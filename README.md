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

Install the PHP dependencies with `composer install` (PHP 8.0+ with OpenSSL).

### Contact emails

The contact form sends the submitted fields, including an optional multiline message of up to 2000 characters,
to `info@mazalimportaciones.com.ar` using PHPMailer over SMTP.
The visitor's email is set as `Reply-To`, so replies go directly to the person who submitted the form.

The SMTP connection supplied for Mazal uses `c1950410.ferozo.com`, port `465`, and SSL.
Configure these values in the server's `.env`, filling in the mailbox password and confirming the SMTP username:

```env
MAIL_HOST=c1950410.ferozo.com
MAIL_PORT=465
MAIL_USERNAME=info@mazalimportaciones.com.ar
MAIL_PASSWORD=YOUR_SMTP_PASSWORD
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@mazalimportaciones.com.ar
MAIL_FROM_NAME="Mazal Importaciones"
```

IMAP and POP3 settings are not used to send contact emails.
`MAIL_FROM_ADDRESS` must be a sender authorized by that provider.
For a local SMTP capture server only, use `MAIL_ENCRYPTION=none` and leave the username and password empty.
Keep credentials in `.env`, which is excluded from Git.

Full name, phone, and email are required; province, store type, and message are optional.
Validation runs in the browser and on the server: names need at least two characters, phone numbers need
7–15 digits (with an optional leading +, spaces, dots, hyphens, and parentheses), and emails must have a valid format.
The form also verifies CSRF tokens and includes a hidden spam trap.
Successful submissions redirect to the form to avoid resending on refresh, with a one-minute cooldown per session.
If SMTP is missing or fails, it displays an error and preserves the entered fields. Technical errors go to the PHP error log.
To verify delivery after deployment, submit the form and confirm receipt in the destination inbox.

Run `node tests/contact-form.mjs` to test the full form flow against a local SMTP capture server
without sending external emails. Set `PHP_BINARY` if PHP is not on `PATH` or at `C:/xampp/php/php.exe`.

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
