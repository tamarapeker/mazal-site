# Database Setup (MySQL / phpMyAdmin)

This folder contains the database setup for the hardware importer catalog using English naming conventions.

## Database and Tables

- Database: `hardware_importer_catalog`
- Tables:
  - `categories`
  - `products`
  - `admins`

## Column Mapping (CSV Spanish -> DB English)

- `CATEGORIA` -> `categories.name` and `categories.slug`
- `CATEGORIA` -> `categories.image_filename` (auto-generated as `NOMBRECATEGORIA.png`)
- `ID` -> `products.product_code`
- `Nombre` -> `products.name`
- `Descripcion` -> `products.description`
- `Medidas` -> `products.sizes` (JSON array of strings)
- `Unidad de medida de venta` -> `products.unit`
- `Cantidad por bulto cerrado` -> `products.quantity_per_bulk`
- `imagen` -> `products.image_filename`

## Local Setup Steps

1. Import one schema in phpMyAdmin:
   - [schema.sql](/C:/Users/MSI/Documents/DesarrolloSoftware/Mazal/mazal-site/database/schema.sql) (creates `hardware_importer_catalog`)
   - [schema_mazal_db.sql](/C:/Users/MSI/Documents/DesarrolloSoftware/Mazal/mazal-site/database/schema_mazal_db.sql) (creates `mazal_db`)
2. If your local DB already exists from previous setup, import:
   - [update_categories_image_filename.sql](/C:/Users/MSI/Documents/DesarrolloSoftware/Mazal/mazal-site/database/scripts/update_categories_image_filename.sql)

   This adds `categories.image_filename` and backfills values like `FERRETERIA.png`.
3. Seed categories and products from CSV (PHP version):

```bash
php database/scripts/seed_from_csv.php --csv="C:\Users\MSI\Documents\DesarrolloSoftware\Mazal\BASE DE DATOS PARA PAGINA WEB FINAL.csv" --database=hardware_importer_catalog --username=root --password=""
```

4. Create or update an admin account:

```bash
php database/scripts/seed_admin.php --full-name="Site Admin" --email="admin@example.com" --password="ChangeMe123!" --database=hardware_importer_catalog --username=root --password-db=""
```

## Node Import Script (Alternative)

If you prefer Node.js, use [import_products.js](/C:/Users/MSI/Documents/DesarrolloSoftware/Mazal/mazal-site/database/scripts/import_products.js):

1. Install dependencies:

```bash
npm install csv-parser mysql2 dotenv bcryptjs
```

2. Run import:

```bash
node database/scripts/import_products.js --csv="C:\Users\MSI\Documents\DesarrolloSoftware\Mazal\BASE DE DATOS PARA PAGINA WEB FINAL.csv" --database=mazal_db --user=root --password=""
```

Optional flags:

- `--dry-run`
- `--truncate-products`
- `--truncate-all`

## Node Admin Import

Create or update one admin user using [import_admin.js](/C:/Users/MSI/Documents/DesarrolloSoftware/Mazal/mazal-site/database/scripts/import_admin.js):

```bash
node database/scripts/import_admin.js --database=mazal_db --user=root --password="" --full-name="Site Admin" --email="admin@example.com" --admin-password="ChangeMe123!" --role=super_admin
```

Optional flags:

- `--host`
- `--port`
- `--rounds=12` (bcrypt rounds, default `12`)

Important:

- If you use `--database=mazal_db`, import `schema_mazal_db.sql` first.

## Useful Script Flags

### `seed_from_csv.php`

- `--dry-run`: validates CSV and prints counts without writing to DB
- `--no-truncate`: keeps existing `categories` and `products` rows
- `--host`, `--port`, `--database`, `--username`, `--password`: DB connection values

### `seed_admin.php`

- `--role=super_admin|editor`
- `--host`, `--port`, `--database`, `--username`, `--password-db`

## Reuse in Production (Donweb)

Yes, you can reuse this approach in production:

1. Import `schema.sql` in production phpMyAdmin.
2. Run the same seed scripts from a machine that can connect to the production DB.
3. Use production DB credentials in script flags.

If direct DB access is limited in your plan, seed locally and then export/import the DB with phpMyAdmin.
