# Mazal Site Bootstrap

This repository now includes the base PHP bootstrap for:

- Public pages routing
- PDO database connection via `.env`
- Basic admin login/logout with session + CSRF
- Placeholder pages for home, categories, product detail, about, and contact

## 1. Environment

1. Copy `.env.example` to `.env`
2. Update DB values:
   - `DB_NAME=mazal_db`
   - `DB_USER`
   - `DB_PASSWORD`

## 2. Database

Import one SQL schema in phpMyAdmin:

- `database/schema_mazal_db.sql` (for `mazal_db`)
- or `database/schema.sql` (for `hardware_importer_catalog`)

Seed data with the script you already used.

## 3. Run Locally

With XAMPP/Apache, open:

- `http://localhost/mazal-site/public/`

Or run PHP built-in server from the project root:

```bash
"C:\xampp\php\php.exe" -S localhost:8080 -t public public/index.php
```

Then open:

- `http://localhost:8080/`

Before running, build Tailwind CSS:

```bash
npm install
npm run build:css
```

For live style changes while developing:

```bash
npm run watch:css
```

Routes available now:

- `/`
- `/categories`
- `/category/{slug}`
- `/product/{code}`
- `/how-to-buy`
- `/como-comprar`
- `/about`
- `/contact`
- `/admin/login`
- `/admin`

## 4. Next Step

Implement product CRUD in admin:

- List products
- Create product
- Edit product
- Soft delete / activate
