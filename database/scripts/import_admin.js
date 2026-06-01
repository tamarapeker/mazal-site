#!/usr/bin/env node
"use strict";

/**
 * Import or update an admin user in MySQL.
 *
 * Requirements:
 *   npm install mysql2 dotenv bcryptjs
 *
 * Example:
 *   node database/scripts/import_admin.js ^
 *     --database=mazal_db ^
 *     --user=root ^
 *     --password="" ^
 *     --full-name="Site Admin" ^
 *     --email="admin@example.com" ^
 *     --admin-password="ChangeMe123!" ^
 *     --role=super_admin
 */

require("dotenv").config();

const mysql = require("mysql2/promise");
const bcrypt = require("bcryptjs");

const ALLOWED_ROLES = new Set(["super_admin", "editor"]);

async function main() {
  const args = parseArgs(process.argv.slice(2));

  const fullName = cleanText(args["full-name"] || process.env.ADMIN_FULL_NAME);
  const email = cleanEmail(args.email || process.env.ADMIN_EMAIL);
  const plainPassword = args["admin-password"] || process.env.ADMIN_PASSWORD;
  const role = (args.role || process.env.ADMIN_ROLE || "super_admin").trim();
  const saltRounds = Number(args.rounds || process.env.BCRYPT_ROUNDS || 12);

  if (!fullName) {
    throw new Error('Missing required value: --full-name or ADMIN_FULL_NAME');
  }
  if (!email) {
    throw new Error("Missing or invalid email: --email or ADMIN_EMAIL");
  }
  if (!plainPassword) {
    throw new Error(
      "Missing required value: --admin-password or ADMIN_PASSWORD",
    );
  }
  if (!ALLOWED_ROLES.has(role)) {
    throw new Error('Invalid role. Use "super_admin" or "editor".');
  }
  if (!Number.isInteger(saltRounds) || saltRounds < 8 || saltRounds > 15) {
    throw new Error("Invalid rounds. Use an integer between 8 and 15.");
  }

  const dbConfig = {
    host: args.host || process.env.DB_HOST || "127.0.0.1",
    port: Number(args.port || process.env.DB_PORT || 3306),
    user: args.user || process.env.DB_USER || "root",
    password: args.password ?? process.env.DB_PASSWORD ?? "",
    database: args.database || process.env.DB_NAME || "mazal_db",
    waitForConnections: true,
    connectionLimit: 5,
    queueLimit: 0,
  };

  const passwordHash = await bcrypt.hash(String(plainPassword), saltRounds);

  const pool = mysql.createPool(dbConfig);
  let connection;

  try {
    connection = await pool.getConnection();
    await ensureAdminsTable(connection);

    const sql = `
      INSERT INTO admins (
        full_name,
        email,
        password_hash,
        role,
        is_active
      ) VALUES (?, ?, ?, ?, 1)
      ON DUPLICATE KEY UPDATE
        full_name = VALUES(full_name),
        password_hash = VALUES(password_hash),
        role = VALUES(role),
        is_active = 1
    `;

    const [result] = await connection.query(sql, [
      fullName,
      email,
      passwordHash,
      role,
    ]);

    const created = result.affectedRows === 1;
    console.log(
      created
        ? `Admin created successfully: ${email}`
        : `Admin updated successfully: ${email}`,
    );
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

function cleanText(value) {
  if (value == null) {
    return null;
  }
  const cleaned = String(value).trim();
  return cleaned === "" ? null : cleaned;
}

function cleanEmail(value) {
  const cleaned = cleanText(value);
  if (!cleaned) {
    return null;
  }
  const normalized = cleaned.toLowerCase();
  const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized);
  return isValid ? normalized : null;
}

async function ensureAdminsTable(connection) {
  const [rows] = await connection.query("SHOW TABLES LIKE 'admins'");
  if (!Array.isArray(rows) || rows.length === 0) {
    throw new Error(
      'Required table "admins" not found in current database. Import schema first.',
    );
  }
}

main().catch((error) => {
  console.error("Admin import failed.");
  console.error(error.message || error);
  process.exit(1);
});
