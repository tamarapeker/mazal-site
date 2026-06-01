#!/usr/bin/env node
"use strict";

/**
 * Import products from CSV into MySQL using Node.js.
 *
 * Requirements:
 *   npm install csv-parser mysql2 dotenv
 *
 * Example:
 *   node database/scripts/import_products.js ^
 *     --csv="C:\Users\MSI\Documents\DesarrolloSoftware\Mazal\BASE DE DATOS PARA PAGINA WEB FINAL.csv" ^
 *     --database=mazal_db --user=root --password=""
 */

require("dotenv").config();

const fs = require("fs");
const path = require("path");
const csv = require("csv-parser");
const mysql = require("mysql2/promise");

const HEADER_ALIAS_MAP = {
  categoria: "category",
  category: "category",
  id: "product_code",
  nombre: "name",
  name: "name",
  descripcion: "description",
  description: "description",
  medidas: "measures_raw",
  measures: "measures_raw",
  sizes: "measures_raw",
  unidaddemedidadeventa: "unit",
  salesunit: "unit",
  unit: "unit",
  cantidadporbultocerrado: "quantity_per_bulk",
  quantityperbulk: "quantity_per_bulk",
  imagen: "image_filename",
  image: "image_filename",
};

async function main() {
  const args = parseArgs(process.argv.slice(2));
  const csvPath = resolveCsvPath(args.csv || process.env.CSV_PATH);

  const dbConfig = {
    host: args.host || process.env.DB_HOST || "127.0.0.1",
    port: Number(args.port || process.env.DB_PORT || 3306),
    user: args.user || process.env.DB_USER || "root",
    password: args.password ?? process.env.DB_PASSWORD ?? "",
    database: args.database || process.env.DB_NAME || "mazal_db",
    waitForConnections: true,
    connectionLimit: Number(process.env.DB_CONNECTION_LIMIT || 10),
    queueLimit: 0,
  };

  const shouldTruncate = Boolean(args["truncate-products"] || args["truncate-all"]);
  const dryRun = Boolean(args["dry-run"]);

  const parsedRows = await loadRowsFromCsv(csvPath);
  const { products, skippedRows } = normalizeRows(parsedRows);

  if (dryRun) {
    printDryRunSummary(csvPath, products, skippedRows);
    return;
  }

  const pool = mysql.createPool(dbConfig);
  let connection;

  try {
    connection = await pool.getConnection();
    await ensureRequiredTables(connection);

    await connection.beginTransaction();

    if (shouldTruncate) {
      await connection.query("SET FOREIGN_KEY_CHECKS = 0");
      await connection.query("TRUNCATE TABLE products");
      if (args["truncate-all"]) {
        await connection.query("TRUNCATE TABLE categories");
      }
      await connection.query("SET FOREIGN_KEY_CHECKS = 1");
    }

    const categoryIdBySlug = await ensureCategories(connection, products);
    const { insertedCount, updatedCount } = await upsertProducts(connection, products, categoryIdBySlug);

    await connection.commit();

    const categoriesDetected = new Set(products.map((p) => slugify(p.category)));
    console.log("Import finished successfully.");
    console.log(`CSV path: ${csvPath}`);
    console.log(`Categories detected: ${categoriesDetected.size}`);
    console.log(`Products processed: ${products.length}`);
    console.log(`Rows skipped: ${skippedRows}`);
    console.log(`Rows inserted: ${insertedCount}`);
    console.log(`Rows updated: ${updatedCount}`);
  } catch (error) {
    if (connection) {
      await connection.rollback();
    }
    console.error("Import failed.");
    console.error(error.message || error);
    process.exitCode = 1;
  } finally {
    if (connection) {
      connection.release();
    }
    await pool.end();
  }
}

function parseArgs(argv) {
  const args = {};
  for (const token of argv) {
    if (!token.startsWith("--")) {
      continue;
    }

    const withoutPrefix = token.slice(2);
    const separatorIndex = withoutPrefix.indexOf("=");
    if (separatorIndex === -1) {
      args[withoutPrefix] = true;
      continue;
    }

    const key = withoutPrefix.slice(0, separatorIndex);
    const value = withoutPrefix.slice(separatorIndex + 1);
    args[key] = value;
  }
  return args;
}

function resolveCsvPath(rawPath) {
  if (rawPath) {
    return path.resolve(rawPath);
  }

  const defaultPath = path.resolve(process.cwd(), "data", "productos.csv");
  if (fs.existsSync(defaultPath)) {
    return defaultPath;
  }

  throw new Error(
    'CSV path not found. Pass --csv="C:\\path\\products.csv" or set CSV_PATH in .env',
  );
}

function loadRowsFromCsv(csvPath) {
  return new Promise((resolve, reject) => {
    const rows = [];

    fs.createReadStream(csvPath)
      .pipe(
        csv({
          separator: ";",
          mapHeaders: ({ header }) => normalizeHeader(header),
        }),
      )
      .on("data", (row) => rows.push(row))
      .on("end", () => resolve(rows))
      .on("error", (error) => reject(error));
  });
}

function normalizeHeader(rawHeader) {
  if (rawHeader == null) {
    return null;
  }

  const cleaned = String(rawHeader).replace(/^\uFEFF/, "").trim();
  if (cleaned === "") {
    return null;
  }

  const normalized = cleaned
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^_+|_+$/g, "");

  const compact = normalized.replace(/_/g, "");
  return HEADER_ALIAS_MAP[compact] || null;
}

function normalizeRows(rows) {
  const products = [];
  let skippedRows = 0;

  for (const row of rows) {
    const category = cleanCell(row.category);
    const productCode = cleanCell(row.product_code)?.toUpperCase() || null;
    const name = cleanCell(row.name);

    const isCompletelyEmpty = [category, productCode, name].every((value) => value == null);
    if (isCompletelyEmpty) {
      skippedRows += 1;
      continue;
    }

    if (!category || !productCode || !name) {
      skippedRows += 1;
      continue;
    }

    const sizes = parseSizes(cleanCell(row.measures_raw));
    const unit = cleanCell(row.unit) || "unit";
    const quantityPerBulk = cleanCell(row.quantity_per_bulk);
    const description = cleanCell(row.description);
    const imageFilename = cleanCell(row.image_filename);
    const slug = `${slugify(name || productCode)}-${productCode.toLowerCase()}`;

    products.push({
      category,
      productCode,
      name,
      slug,
      description,
      sizes,
      unit,
      quantityPerBulk,
      imageFilename,
    });
  }

  return { products, skippedRows };
}

function cleanCell(value) {
  if (value == null) {
    return null;
  }

  const cleaned = String(value).replace(/\u00A0/g, " ").trim();
  return cleaned === "" ? null : cleaned;
}

function parseSizes(measuresRaw) {
  if (!measuresRaw) {
    return null;
  }

  const pieces = measuresRaw.split(";");
  const values = [];

  for (let index = 0; index < pieces.length; index += 1) {
    let current = cleanCell(pieces[index]);
    if (!current) {
      continue;
    }

    const next = cleanCell(pieces[index + 1]);
    if (
      next &&
      /^\d+$/.test(current) &&
      /^\d+\s*[a-zA-Z]/.test(next)
    ) {
      current = `${current};${next}`;
      index += 1;
    }

    values.push(current);
  }

  if (values.length === 0) {
    return null;
  }

  return JSON.stringify(values);
}

function slugify(value) {
  return String(value)
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

async function ensureRequiredTables(connection) {
  const requiredTables = ["categories", "products"];

  for (const tableName of requiredTables) {
    const [rows] = await connection.query("SHOW TABLES LIKE ?", [tableName]);
    if (!Array.isArray(rows) || rows.length === 0) {
      throw new Error(
        `Required table "${tableName}" not found in current database. Import database/schema.sql first.`,
      );
    }
  }
}

async function ensureCategories(connection, products) {
  const categoryIdBySlug = new Map();
  const categoriesInOrder = [];

  for (const product of products) {
    const slug = slugify(product.category);
    if (!categoryIdBySlug.has(slug)) {
      categoriesInOrder.push({
        name: toDisplayCase(product.category),
        slug,
        imageFilename: categoryImageFilename(product.category),
      });
      categoryIdBySlug.set(slug, null);
    }
  }

  const sql = `
    INSERT INTO categories (name, slug, image_filename, display_order, is_active)
    VALUES (?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE
      id = LAST_INSERT_ID(id),
      name = VALUES(name),
      slug = VALUES(slug),
      image_filename = VALUES(image_filename),
      is_active = 1
  `;

  for (let index = 0; index < categoriesInOrder.length; index += 1) {
    const category = categoriesInOrder[index];
    const displayOrder = index + 1;
    const [result] = await connection.query(sql, [
      category.name,
      category.slug,
      category.imageFilename,
      displayOrder,
    ]);
    categoryIdBySlug.set(category.slug, Number(result.insertId));
  }

  return categoryIdBySlug;
}

async function upsertProducts(connection, products, categoryIdBySlug) {
  let insertedCount = 0;
  let updatedCount = 0;

  const sql = `
    INSERT INTO products (
      category_id,
      product_code,
      name,
      slug,
      description,
      sizes,
      unit,
      quantity_per_bulk,
      image_filename,
      is_active
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE
      category_id = VALUES(category_id),
      name = VALUES(name),
      slug = VALUES(slug),
      description = VALUES(description),
      sizes = VALUES(sizes),
      unit = VALUES(unit),
      quantity_per_bulk = VALUES(quantity_per_bulk),
      image_filename = VALUES(image_filename),
      is_active = 1
  `;

  for (const product of products) {
    const categorySlug = slugify(product.category);
    const categoryId = categoryIdBySlug.get(categorySlug);
    if (!categoryId) {
      throw new Error(`Category ID not found for slug "${categorySlug}"`);
    }

    const values = [
      categoryId,
      product.productCode,
      product.name,
      product.slug,
      product.description,
      product.sizes,
      product.unit,
      product.quantityPerBulk,
      product.imageFilename,
    ];

    const [result] = await connection.query(sql, values);

    if (result.affectedRows === 1) {
      insertedCount += 1;
    } else {
      updatedCount += 1;
    }
  }

  return { insertedCount, updatedCount };
}

function toDisplayCase(value) {
  const words = String(value).trim().split(/\s+/);
  return words
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(" ");
}

function categoryImageFilename(categoryName) {
  const slug = slugify(categoryName).replace(/-/g, "");
  if (!slug) {
    return "CATEGORY.png";
  }
  return `${slug.toUpperCase()}.png`;
}

function printDryRunSummary(csvPath, products, skippedRows) {
  const categorySlugs = new Set(products.map((product) => slugify(product.category)));
  console.log("Dry run summary");
  console.log("---------------");
  console.log(`CSV path: ${csvPath}`);
  console.log(`Categories detected: ${categorySlugs.size}`);
  console.log(`Products detected: ${products.length}`);
  console.log(`Rows skipped: ${skippedRows}`);
}

main().catch((error) => {
  console.error("Fatal error.");
  console.error(error.message || error);
  process.exit(1);
});
