# Deploy to InfinityFree

This project can be deployed on InfinityFree with PHP + MySQL.

## Recommended folder structure

Upload the project in two parts so the application code stays outside `public_html`.

Example remote structure:

```text
/htdocs/
  /mazal-site/
    app/
    bootstrap/
    config/
    public/
    resources/
    routes/
    storage/
    .env
  /public_html/
    index.php
    .htaccess
    /assets/
```

## What to upload

### To `/htdocs/mazal-site/`

Upload these folders:

- `app`
- `bootstrap`
- `config`
- `public`
- `routes`
- `storage`
- `vendor` (generated locally with `composer install --no-dev --optimize-autoloader`)

Upload these files:

- `.env`
- `composer.json`
- `composer.lock`

You do **not** need to upload:

- `node_modules`
- `database`
- `resources`
- `tmp-*`
- `.git`

### To `/htdocs/public_html/`

Upload the **contents** of local `public/`:

- `index.php`
- `.htaccess`
- `assets/`

Do not upload the `public` folder itself inside `public_html`.

## Build CSS before uploading

Run locally:

```powershell
npm.cmd run build:css
```

Then upload the generated file:

- `public/assets/css/app.css`

## Database

1. In InfinityFree, create a MySQL database.
2. Open InfinityFree phpMyAdmin.
3. Import:
   - `database/schema_mazal_db.sql`
4. If needed, also import:
   - `database/scripts/update_categories_image_filename.sql`
5. Import your local DB data or seed it before exporting/importing.

## Production `.env`

Set your InfinityFree values in `.env`:

```env
APP_NAME="Mazal Catalog"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-DOMAIN.infinityfreeapp.com
APP_TIMEZONE=Europe/Madrid

DB_HOST=YOUR_DB_HOST
DB_PORT=3306
DB_NAME=YOUR_DB_NAME
DB_USER=YOUR_DB_USER
DB_PASSWORD=YOUR_DB_PASSWORD

SESSION_NAME=mazal_session

MAIL_HOST=c1950410.ferozo.com
MAIL_PORT=465
MAIL_USERNAME=info@mazalimportaciones.com.ar
MAIL_PASSWORD=YOUR_SMTP_PASSWORD
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@mazalimportaciones.com.ar
MAIL_FROM_NAME="Mazal Importaciones"
```

Use the database host shown by InfinityFree. It is usually **not** `127.0.0.1`.

The contact form uses authenticated SMTP to send to `info@mazalimportaciones.com.ar`.
InfinityFree restricts PHP's native `mail()` function, so configure an external SMTP provider
as described in [their email documentation](https://forum.infinityfree.com/t/emails-from-my-website-arent-working/49242).
Mazal's supplied SMTP connection is `c1950410.ferozo.com:465` with `MAIL_ENCRYPTION=ssl`.
Fill in the mailbox password and confirm the SMTP username with the email provider.
Upload `vendor/` alongside `app/`, outside `public_html`.
After uploading, submit a contact request and check the destination inbox. Without valid SMTP settings,
the page will report that the request could not be sent.

## Notes

- `public/index.php` now supports app code living outside `public_html`.
- If your app is uploaded to a folder with a different name than `mazal-site`, set:

```env
APP_BASE_PATH=/home/volXX_XX/infinityfree.com/htdocs/YOUR_APP_FOLDER
```

Only use `APP_BASE_PATH` if auto-detection does not find the app folder.
