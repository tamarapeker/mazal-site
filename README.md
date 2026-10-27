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
- `/admin/products`
- `/admin/products/create`
- `/admin/products/{id}/edit`

## 4. Backoffice product list

Sign in at `/admin/login`, then choose **Productos** or open `/admin/products`.
The list reads the existing MySQL catalog, including inactive products and categories, with:

- Search by product code or name.
- Category and product status filters, which can be combined with search.
- Ascending/descending sorting by code, name or category by clicking the column headers.
- Server-side pagination of 25 products, preserving the selected filters and sort order.
- Existing image thumbnails, minimum sale (stored in `unit`), quantity per bulk, status and a public detail link for published products.
- **Editar** and a status action on each row: **Desactivar** for active products, **Activar** for inactive ones.

Filters use GET parameters, so the current selection can be bookmarked. Sorting and applying filters start
at page one; pagination keeps the selection. No database migration is needed for this screen.
Admin styles live in `public/assets/css/admin.css` and do not require a Tailwind rebuild.

Run `C:/xampp/php/php.exe tests/admin-products.php` (or `php tests/admin-products.php`) to verify the listing.
The test requires the configured MySQL connection and creates connection-local temporary tables;
the real catalog is not modified.

### Create a product

Choose **Nuevo producto** above the table, or open `/admin/products/create`.
Code, name, category and status are required. Codes are normalized to uppercase and must be unique
(up to 40 letters, numbers, hyphens or underscores). Description, minimum sale, quantity per bulk,
measures and image are optional. The slug is generated automatically.

Measures use one input per value, with **Agregar medida** and **Quitar** controls (up to 50 values of
80 characters). Blank values are ignored and exact duplicates are removed; the list is stored in the
existing `sizes` JSON column. Minimum sale remains stored in `unit` and is only shown in the backoffice.
An active product is public when its category is also active; an inactive product is kept in the backoffice.
The initial status is chosen when creating the product. Subsequent status changes happen from the table.

The form preserves entered text and measures after validation errors. Browsers require the image to be
selected again after a server error. Successful saves redirect to the table with a confirmation and a
search for the new code. Both creation routes require an admin session; POST also verifies CSRF.

### Edit and deactivate products

**Editar** opens `/admin/products/{id}/edit` with the existing database values, measures, status and
image loaded into the same form used for creation. The code is read-only and the server always keeps
the stored code, even if a modified request submits another value. The slug also stays unchanged.
The status select displays the current state and is disabled in Editar, with a hint to use the table's
status action. Editing ignores any submitted `is_active` value and never updates that column.
Saving posts to `/admin/products/{id}/update` and returns to the list with a confirmation.

Leaving the image field empty keeps the current image. Selecting a new image replaces it;
**Quitar la imagen actual al guardar** removes the image association. Changes are saved in a database
transaction. A failed save preserves the old image and removes any newly uploaded file. After a successful
replacement/removal, unreferenced `UPLOAD_*` files are cleaned up; imported static assets and images
still referenced by other products are preserved.

The table's status button asks for confirmation inside the page, showing the product code and name.
**Desactivar** posts to `/admin/products/{id}/deactivate`; **Activar** posts to `/admin/products/{id}/activate`.
This is a soft delete: only `is_active` changes to `0`; the record, code, fields and image are retained.
The product stops appearing in the public catalog. The list preserves its filters, sorting and page,
and replaces the action with **Activar** for inactive products. Activation sets `is_active` to `1`;
the product becomes public when its category is active. Each action requests a specific state,
so repeated submissions cannot accidentally toggle it back. All mutation routes require an admin session and a valid CSRF token; GET
requests never update or deactivate products.

### Uploaded product images

The form supports an optional JPG, PNG or WebP image, with a preview and a remove button before saving.
PHP checks the actual MIME type, image header/dimensions and size (up to 5 MiB and 40 megapixels).
SVG and other file types are rejected. The browser-supplied filename is never used as the stored filename.
`move_uploaded_file()` saves the image as `UPLOAD_<random uppercase hex>.jpg|png|webp` in
`public/assets/images/products/`; only that filename is stored in `products.image_filename`.
On split hosting layouts, `public/index.php` defines the actual served public directory so the image
is written under the deployed `public_html/assets/images/products/` directory.
If database insertion fails after moving the image, the new file is removed.

The host needs PHP Fileinfo and write permission for the product image directory. Set
`upload_max_filesize` to at least `5M` and `post_max_size` to a larger value, such as `8M`, to allow
multipart overhead. Upload the image directory's `.htaccess` on Apache; it denies script execution requests.
Uploaded `UPLOAD_*` files are ignored by Git. Include them in backups and preserve them when deploying
static assets. See the [PHP upload documentation](https://www.php.net/manual/en/features.file-upload.post-method.php).

Run `node tests/create-product.mjs` for HTTP integration checks covering creation, preloaded editing,
immutable codes/slugs and edit status, table-only activation/deactivation, validation, session/CSRF, multipart uploads,
JSON measures, size/type rejection, image lifecycle and rollback after storage/database errors.
Like the listing test, this uses connection-local temporary MySQL tables; uploaded test files and
fixture state live in an isolated OS temporary directory. No real catalog records are changed.
`node tests/create-product.mjs --preview` keeps the isolated test server running for browser QA.
