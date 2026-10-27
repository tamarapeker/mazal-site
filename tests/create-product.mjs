import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { once } from 'node:events';
import { existsSync } from 'node:fs';
import { mkdtemp, mkdir, readFile, readdir, rm, writeFile } from 'node:fs/promises';
import net from 'node:net';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const php = process.env.PHP_BINARY || (existsSync('C:/xampp/php/php.exe') ? 'C:/xampp/php/php.exe' : 'php');
const temporary = await mkdtemp(path.join(os.tmpdir(), 'mazal-create-product-'));
const images = path.join(temporary, 'images');
const stateFile = path.join(temporary, 'products.json');
const phpString = (value) => "'" + value.replaceAll('\\', '\\\\').replaceAll("'", "\\'") + "'";
const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII=', 'base64');
let server;
let logs = '';

try {
  await mkdir(images);
  await mkdir(path.join(temporary, 'sessions'));
  await writeFile(path.join(images, 'LEGACY.png'), png);
  await writeFile(path.join(temporary, 'blocked'), 'Directory-error test fixture');
  await writeFile(stateFile, JSON.stringify([{id: 1, category_id: 1, product_code: 'EXISTING', name: 'Producto existente', slug: 'producto-existente', description: null, sizes: null, unit: 'unidad', quantity_per_bulk: '10 unidades', image_filename: 'LEGACY.png', is_active: 1}]));
  const routerFile = path.join(temporary, 'router.php');
  await writeFile(routerFile, `<?php
$_SERVER['SCRIPT_NAME'] = str_starts_with($_SERVER['REQUEST_URI'], '/catalogo/') ? '/catalogo/index.php' : '/index.php';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$images = ${phpString(images)};
if (preg_match('#^/assets/images/products/(UPLOAD_[A-F0-9]{32}\\.(?:jpg|png|webp)|LEGACY\\.png)$#', $path, $match)) {
    $file = $images . '/' . $match[1];
    if (is_file($file)) { header('Content-Type: ' . mime_content_type($file)); readfile($file); }
    else { http_response_code(404); }
    return;
}
if (str_starts_with($path, '/assets/')) { return false; }
$router = require ${phpString(path.join(root, 'bootstrap/app.php'))};
// Browser QA must not replace the real localhost application's session cookie.
session_write_close();
header_remove('Set-Cookie');
session_name('mazal_product_test');
session_id(is_string($_COOKIE['mazal_product_test'] ?? null) ? $_COOKIE['mazal_product_test'] : '');
session_start();
set_app_config(array_replace_recursive(config(), ['uploads' => ['product_images_path' => isset($_GET['directory_error']) ? ${phpString(path.join(temporary, 'blocked'))} : $images]]));
$pdo = App\\Core\\Database::connection();
// All writes are connection-local. Only this temporary JSON file persists across requests.
$pdo->exec('CREATE TEMPORARY TABLE categories (id INT PRIMARY KEY, name VARCHAR(120), slug VARCHAR(160), is_active TINYINT)');
$pdo->exec("INSERT INTO categories VALUES (1, 'Ferretería', 'ferreteria', 1), (2, 'Jardinería', 'jardineria', 0)");
$pdo->exec('CREATE TEMPORARY TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_id INT UNSIGNED NOT NULL,
    product_code VARCHAR(40) NOT NULL UNIQUE, name VARCHAR(180) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE, description TEXT NULL, sizes JSON NULL,
    unit VARCHAR(50) NOT NULL DEFAULT "", quantity_per_bulk VARCHAR(100) NULL,
    image_filename VARCHAR(255) NULL, is_active TINYINT NOT NULL DEFAULT 1
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('CREATE TEMPORARY TABLE admins (id INT PRIMARY KEY, full_name VARCHAR(120), email VARCHAR(190), password_hash VARCHAR(255), role VARCHAR(20), is_active TINYINT, last_login_at DATETIME NULL)');
$admin = $pdo->prepare('INSERT INTO admins VALUES (1, ?, ?, ?, ?, 1, NULL)');
$admin->execute(['Admin de prueba', 'test@example.test', password_hash('Local-only-test123!', PASSWORD_BCRYPT), 'editor']);
$insert = $pdo->prepare('INSERT INTO products (id, category_id, product_code, name, slug, description, sizes, unit, quantity_per_bulk, image_filename, is_active) VALUES (:id, :category_id, :product_code, :name, :slug, :description, :sizes, :unit, :quantity_per_bulk, :image_filename, :is_active)');
foreach (json_decode(file_get_contents(${phpString(stateFile)}), true) as $row) { $insert->execute($row); }
if (isset($_GET['database_error'])) { $pdo->exec("ALTER TABLE products ADD CONSTRAINT qa_insert_failure CHECK (product_code <> 'DBFAIL')"); }
if (isset($_GET['database_update_error'])) { $pdo->exec("ALTER TABLE products ADD CONSTRAINT qa_update_failure CHECK (name <> 'FAIL_UPDATE')"); }
if (isset($_GET['database_deactivate_error'])) { $pdo->exec("ALTER TABLE products ADD CONSTRAINT qa_deactivate_failure CHECK (product_code <> 'EXISTING' OR is_active = 1)"); }
if (isset($_GET['database_activate_error'])) { $pdo->exec("ALTER TABLE products ADD CONSTRAINT qa_activate_failure CHECK (product_code <> 'NEW-01' OR is_active = 0)"); }
register_shutdown_function(static function () use ($pdo): void {
    $rows = $pdo->query('SELECT id, category_id, product_code, name, slug, description, sizes, unit, quantity_per_bulk, image_filename, is_active FROM products ORDER BY id')->fetchAll();
    file_put_contents(${phpString(stateFile)}, json_encode($rows, JSON_THROW_ON_ERROR));
});
$router->dispatch($_SERVER['REQUEST_METHOD'], request_path());
`);
  const reservation = net.createServer();
  reservation.listen(0, '127.0.0.1');
  await once(reservation, 'listening');
  const port = reservation.address().port;
  await new Promise((resolve) => reservation.close(resolve));
  const origin = `http://127.0.0.1:${port}`;
  server = spawn(php, ['-d', `session.save_path=${path.join(temporary, 'sessions')}`, '-d', 'upload_max_filesize=6M', '-d', 'post_max_size=8M', '-S', `127.0.0.1:${port}`, '-t', path.join(root, 'public'), routerFile], {cwd: root, windowsHide: true, stdio: ['ignore', 'pipe', 'pipe']});
  server.stdout.on('data', (chunk) => { logs += chunk; });
  server.stderr.on('data', (chunk) => { logs += chunk; });
  server.on('error', (error) => { logs += error.message; });
  let ready = false;
  for (let attempt = 0; attempt < 60; attempt++) {
    try {
      const response = await fetch(origin + '/admin/login');
      await response.text();
      if (response.status === 200) { ready = true; break; }
    } catch {}
    await new Promise((resolve) => setTimeout(resolve, 50));
  }
  assert.ok(ready, 'PHP test server did not start: ' + logs);

  for (const route of ['/admin/products/create', '/admin/products', '/admin/products/1/edit', '/admin/products/1/update', '/admin/products/1/deactivate', '/admin/products/1/activate']) {
    const response = await fetch(origin + route, {method: route.endsWith('/create') || route.endsWith('/edit') ? 'GET' : 'POST', redirect: 'manual'});
    const responseHtml = await response.text();
    assert.equal(response.status, 302, 'Both creation routes must require a session. ' + responseHtml + logs.slice(-2000));
    assert.equal(response.headers.get('location'), '/admin/login');
  }
  const login = await fetch(origin + '/admin/login');
  let cookie = login.headers.get('set-cookie').split(';')[0];
  const loginHtml = await login.text();
  const loginToken = loginHtml.match(/name="_token" value="([^"]+)"/)[1];
  const signedIn = await fetch(origin + '/admin/login', {method: 'POST', redirect: 'manual', headers: {Cookie: cookie}, body: new URLSearchParams({_token: loginToken, email: 'test@example.test', password: 'Local-only-test123!'})});
  assert.equal(signedIn.status, 302);
  cookie = signedIn.headers.get('set-cookie').split(';')[0];
  await signedIn.text();

  const getForm = await fetch(origin + '/admin/products/create', {headers: {Cookie: cookie}});
  const formHtml = await getForm.text();
  assert.equal(getForm.status, 200);
  assert.match(formHtml, /enctype="multipart\/form-data"/);
  for (const field of ['product_code', 'category_id', 'name', 'description', 'unit', 'quantity_per_bulk', 'sizes[]', 'is_active', 'image']) assert.ok(formHtml.includes(`name="${field}"`), 'Missing form field: ' + field);
  const token = formHtml.match(/name="_token" value="([^"]+)"/)[1];
  const values = {product_code: 'NEW-01', category_id: '1', name: 'Manguera de jardín', description: 'Descripción con acentos.\nSegunda línea.', unit: '25 m', quantity_per_bulk: '10 rollos', is_active: '0', sizes: ['10 cm', '20 cm', '', '10 cm']};
  async function submit(overrides = {}, image, query = '', prefix = '', route = '/admin/products') {
    const data = new FormData();
    const fields = {_token: token, ...values, ...overrides};
    for (const [key, value] of Object.entries(fields)) {
      if (Array.isArray(value)) value.forEach((item) => data.append(key + '[]', item));
      else if (value !== undefined) data.append(key, value);
    }
    if (image) data.append(image.field || 'image', new Blob([image.bytes], {type: image.type || 'image/png'}), image.name || 'picture.png');
    const response = await fetch(origin + prefix + route + query, {method: 'POST', redirect: 'manual', headers: {Cookie: cookie}, body: data});
    return {response, html: await response.text()};
  }
  const rows = async () => JSON.parse(await readFile(stateFile, 'utf8'));
  const files = () => readdir(images);
  async function rejected(overrides, image, expected = 422, query = '') {
    const before = await rows();
    const beforeFiles = await files();
    const result = await submit(overrides, image, query);
    assert.equal(result.response.status, expected, result.html.slice(0, 500) + '\n' + logs.slice(-1000));
    assert.match(result.html, /Revisá el formulario/);
    assert.deepEqual(await rows(), before, 'A rejected form must not change the catalog.');
    assert.deepEqual(await files(), beforeFiles, 'A rejected form must not leave an image behind.');
    return result.html;
  }

  const invalid = await rejected({product_code: '', name: '', category_id: '', unit: 'x'.repeat(51), quantity_per_bulk: 'x'.repeat(101), description: 'x'.repeat(5001), is_active: '2'});
  for (const field of ['product_code', 'name', 'category_id', 'unit', 'quantity_per_bulk', 'description', 'is_active']) assert.ok(invalid.includes(`id="error-${field}"`));
  await rejected({_token: 'expired'}, {bytes: png}, 403);
  await rejected({_token: undefined}, undefined, 403);
  await rejected({product_code: "BAD/../CODE", category_id: '999'});
  await rejected({sizes: 'wrong-shape'});
  await rejected({sizes: ['x'.repeat(81)]});
  await rejected({sizes: Array(51).fill('10 cm')});
  await rejected({'sizes[0][nested]': 'bad', sizes: undefined});
  await rejected({'name[nested]': 'bad', name: undefined});
  const escaped = await rejected({product_code: 'EXISTING', name: '<script>alert("x")</script>'}, {bytes: png});
  assert.ok(escaped.includes('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;'));
  assert.ok(!escaped.includes('<script>alert'));
  assert.ok(escaped.includes('Volvé a seleccionar la imagen'));
  await rejected({}, {bytes: '<svg xmlns="http://www.w3.org/2000/svg"></svg>', type: 'image/svg+xml', name: 'fake.png'});
  await rejected({}, {bytes: '<?php echo "bad";', type: 'image/png', name: 'fake.png'});
  await rejected({}, {bytes: png.subarray(0, 8)});
  await rejected({}, {bytes: Buffer.alloc(0)});
  await rejected({}, {bytes: png, field: 'image[]'});
  await rejected({}, {bytes: Buffer.concat([png, Buffer.alloc(5 * 1024 * 1024)])});
  await rejected({}, {bytes: Buffer.alloc(7 * 1024 * 1024)});
  await rejected({}, {bytes: Buffer.alloc(9 * 1024 * 1024)}, 413);
  await rejected({}, {bytes: png}, 422, '?directory_error=1');
  await rejected({product_code: 'DBFAIL'}, {bytes: png}, 422, '?database_error=1');

  const created = await submit({product_code: 'new-01'}, {bytes: png, type: 'text/plain', name: '../../danger.php'});
  assert.equal(created.response.status, 302, created.html + logs.slice(-2000));
  assert.equal(created.response.headers.get('location'), '/admin/products?q=NEW-01');
  const saved = (await rows()).find((row) => row.product_code === 'NEW-01');
  assert.equal(saved.name, values.name);
  assert.equal(saved.description, values.description);
  assert.equal(saved.unit, '25 m');
  assert.equal(saved.is_active, 0);
  assert.deepEqual(JSON.parse(saved.sizes), ['10 cm', '20 cm']);
  assert.match(saved.slug, /^manguera-de-jardin-new-01-[a-f0-9]{8}$/);
  assert.match(saved.image_filename, /^UPLOAD_[A-F0-9]{32}\.png$/);
  assert.deepEqual(await readFile(path.join(images, saved.image_filename)), png);
  const listed = await fetch(origin + created.response.headers.get('location'), {headers: {Cookie: cookie}});
  const listedHtml = await listed.text();
  assert.match(listedHtml, /El producto NEW-01 se creó correctamente/);
  assert.ok(listedHtml.includes('data-product-code="NEW-01"'));
  assert.ok(listedHtml.includes('/assets/images/products/' + saved.image_filename));
  await rejected({product_code: 'NEW-01'}, {bytes: png});

  const noImage = await submit({product_code: 'NO-IMAGE', name: "Codo d'agua", description: '', unit: '', quantity_per_bulk: '', sizes: [], is_active: '1'});
  assert.equal(noImage.response.status, 302);
  const optional = (await rows()).find((row) => row.product_code === 'NO-IMAGE');
  for (const field of ['description', 'sizes', 'quantity_per_bulk', 'image_filename']) assert.equal(optional[field], null);
  assert.equal(optional.unit, '');
  const publicPage = await fetch(origin + '/product/no-image');
  assert.equal(publicPage.status, 200);
  assert.ok(!(await publicPage.text()).includes('Venta mínima'));
  const jpg = await readFile(path.join(root, 'public/assets/images/about-ariel.jpg'));
  assert.equal((await submit({product_code: 'JPEG'}, {bytes: jpg, type: 'image/jpeg', name: 'picture.png'})).response.status, 302);
  assert.match((await rows()).find((row) => row.product_code === 'JPEG').image_filename, /\.jpg$/);
  const webp = Buffer.from('UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA', 'base64');
  assert.equal((await submit({product_code: 'WEBP'}, {bytes: webp, type: 'image/webp', name: 'photo.webp'})).response.status, 302);
  assert.match((await rows()).find((row) => row.product_code === 'WEBP').image_filename, /\.webp$/);

  const subdirectory = await fetch(origin + '/catalogo/admin/products/create', {headers: {Cookie: cookie}});
  assert.ok((await subdirectory.text()).includes('action="/catalogo/admin/products"'));
  const prefixed = await submit({product_code: 'PREFIX'}, undefined, '', '/catalogo');
  assert.equal(prefixed.response.headers.get('location'), '/catalogo/admin/products?q=PREFIX');

  async function change(id, overrides = {}, image, query = '', prefix = '') {
    const current = (await rows()).find((row) => row.id === id);
    const fields = {};
    for (const key of ['product_code', 'name', 'category_id', 'description', 'unit', 'quantity_per_bulk', 'is_active']) fields[key] = current[key] ?? '';
    fields.sizes = current.sizes ? JSON.parse(current.sizes) : [''];
    return submit({...fields, ...overrides}, image, query, prefix, `/admin/products/${id}/update`);
  }
  async function rejectChange(id, overrides = {}, image, expected = 422, query = '') {
    const before = await rows();
    const beforeFiles = await files();
    const result = await change(id, overrides, image, query);
    assert.equal(result.response.status, expected, result.html + logs.slice(-1000));
    assert.deepEqual(await rows(), before, 'A failed edit must preserve all stored fields.');
    assert.deepEqual(await files(), beforeFiles, 'A failed edit must preserve the old image and clean up new uploads.');
    return result.html;
  }
  async function setStatus(id, action, overrides = {}, query = '', prefix = '') {
    const response = await fetch(origin + prefix + `/admin/products/${id}/${action}` + query, {method: 'POST', redirect: 'manual', headers: {Cookie: cookie}, body: new URLSearchParams({_token: token, ...overrides})});
    return {response, html: await response.text()};
  }
  const deactivate = (id, overrides, query, prefix) => setStatus(id, 'deactivate', overrides, query, prefix);
  const activate = (id, overrides, query, prefix) => setStatus(id, 'activate', overrides, query, prefix);
  const id = saved.id;
  const editPage = await fetch(origin + `/admin/products/${id}/edit`, {headers: {Cookie: cookie}});
  const editHtml = await editPage.text();
  assert.equal(editPage.status, 200);
  assert.match(editHtml, /<input[^>]*name="product_code"[^>]*\breadonly\b/);
  assert.match(editHtml, /<select[^>]*name="is_active"[^>]*\bdisabled\b/);
  for (const value of ['NEW-01', 'Manguera de jardín', '25 m', '10 rollos', '10 cm', '20 cm', saved.image_filename]) assert.ok(editHtml.includes(value), 'The edit form must preload ' + value);
  assert.ok(editHtml.includes('value="0" selected'));
  assert.ok(editHtml.includes('name="remove_image"'));
  for (const route of ['/admin/products/999999/edit', '/admin/products/not-an-id/edit']) {
    const response = await fetch(origin + route, {headers: {Cookie: cookie}});
    assert.equal(response.status, 404);
    await response.text();
  }
  const unknownUpdate = await submit({}, undefined, '', '', '/admin/products/999999/update');
  assert.equal(unknownUpdate.response.status, 404);
  assert.equal((await deactivate(999999)).response.status, 404);
  assert.equal((await activate(999999)).response.status, 404);

  const invalidEdit = await rejectChange(id, {name: '', category_id: '999', is_active: 'bad'}, {bytes: png});
  assert.ok(invalidEdit.includes('Volvé a seleccionar la imagen'));
  assert.ok(invalidEdit.includes(saved.image_filename), 'Validation must preserve the stored image in the edit form.');
  await rejectChange(id, {_token: 'expired'}, {bytes: png}, 403);
  await rejectChange(id, {remove_image: 'invalid'});
  await rejectChange(id, {sizes: ['x'.repeat(81)]});
  await rejectChange(id, {}, {bytes: 'not an image', name: 'fake.png'});
  await rejectChange(id, {}, {bytes: png}, 422, '?directory_error=1');
  await rejectChange(id, {name: 'FAIL_UPDATE'}, {bytes: png}, 422, '?database_update_error=1');
  const changed = await change(id, {product_code: 'FORGED-CODE', name: 'Manguera actualizada', description: 'Nueva descripción', category_id: '2', unit: '1 rollo', quantity_per_bulk: '12 rollos', sizes: ['30 cm', '40 cm'], is_active: '1'});
  assert.equal(changed.response.status, 302);
  assert.equal(changed.response.headers.get('location'), '/admin/products?q=NEW-01');
  let updated = (await rows()).find((row) => row.id === id);
  assert.equal(updated.product_code, saved.product_code, 'The server must ignore code changes.');
  assert.equal(updated.slug, saved.slug, 'Editing must preserve the existing slug.');
  assert.equal(updated.name, 'Manguera actualizada');
  assert.equal(updated.description, 'Nueva descripción');
  assert.equal(updated.category_id, 2);
  assert.equal(updated.unit, '1 rollo');
  assert.equal(updated.quantity_per_bulk, '12 rollos');
  assert.equal(updated.is_active, 0, 'Editing must ignore a forged activation value.');
  assert.deepEqual(JSON.parse(updated.sizes), ['30 cm', '40 cm']);
  assert.equal(updated.image_filename, saved.image_filename);
  assert.ok(existsSync(path.join(images, saved.image_filename)), 'Edits without an upload must keep the existing image.');
  const successPage = await fetch(origin + changed.response.headers.get('location'), {headers: {Cookie: cookie}});
  assert.match(await successPage.text(), /El producto NEW-01 se actualizó correctamente/);
  const beforeActivate = await rows();
  const beforeActivateFiles = await files();
  assert.equal((await activate(id, {_token: 'expired'})).response.status, 403);
  assert.deepEqual(await rows(), beforeActivate);
  const activated = await activate(id, {return_query: 'q=NEW-01&sort=name&direction=desc&page=1'});
  assert.equal(activated.response.status, 302);
  assert.equal(activated.response.headers.get('location'), '/admin/products?q=NEW-01&sort=name&direction=desc&page=1');
  assert.deepEqual((await rows()).find((row) => row.id === id), {...updated, is_active: 1}, 'Activating must change only is_active.');
  assert.deepEqual(await files(), beforeActivateFiles);
  assert.equal((await activate(id)).response.status, 302, 'Repeated activation must remain active.');
  const activeEdit = await fetch(origin + `/admin/products/${id}/edit`, {headers: {Cookie: cookie}});
  const activeEditHtml = await activeEdit.text();
  assert.match(activeEditHtml, /<select[^>]*name="is_active"[^>]*\bdisabled\b/);
  assert.ok(activeEditHtml.includes('value="1" selected'));
  assert.equal((await change(id, {is_active: '0'})).response.status, 302);
  assert.equal((await rows()).find((row) => row.id === id).is_active, 1, 'Editing must ignore a forged deactivation value.');
  assert.equal((await change(id, {'is_active[nested]': '0', is_active: undefined})).response.status, 302);
  assert.equal((await rows()).find((row) => row.id === id).is_active, 1, 'Malformed status input must also leave the stored status untouched.');

  assert.equal((await change(id, {category_id: '1'}, {bytes: jpg, type: 'image/jpeg'})).response.status, 302);
  updated = (await rows()).find((row) => row.id === id);
  const replacement = updated.image_filename;
  assert.match(replacement, /\.jpg$/);
  assert.ok(existsSync(path.join(images, replacement)));
  assert.ok(!existsSync(path.join(images, saved.image_filename)), 'Unreferenced generated images must be removed after a successful replacement.');
  // A second product can reference the same stored filename; cleanup must protect it.
  const withShared = await rows();
  const sharedId = Math.max(...withShared.map((row) => row.id)) + 1;
  withShared.push({...updated, id: sharedId, product_code: 'SHARED', name: 'Imagen compartida', slug: 'imagen-compartida'});
  await writeFile(stateFile, JSON.stringify(withShared));
  assert.equal((await change(id, {}, {bytes: png})).response.status, 302);
  assert.ok(existsSync(path.join(images, replacement)), 'Images referenced by another product must be kept.');
  assert.equal((await change(sharedId, {remove_image: '1'})).response.status, 302);
  assert.equal((await rows()).find((row) => row.id === sharedId).image_filename, null);
  assert.ok(!existsSync(path.join(images, replacement)), 'The last reference can remove an uploaded image.');
  assert.equal((await change(1, {remove_image: '1'})).response.status, 302);
  assert.ok(existsSync(path.join(images, 'LEGACY.png')), 'Imported static images must never be deleted by upload cleanup.');

  const beforeSoftDelete = await rows();
  const beforeSoftDeleteFiles = await files();
  const oldRow = beforeSoftDelete.find((row) => row.id === id);
  const forbidden = await deactivate(id, {_token: 'wrong'});
  assert.equal(forbidden.response.status, 403);
  assert.deepEqual(await rows(), beforeSoftDelete);
  for (const route of [`/admin/products/${id}/update`, `/admin/products/${id}/deactivate`, `/admin/products/${id}/activate`]) {
    const response = await fetch(origin + route, {headers: {Cookie: cookie}});
    assert.equal(response.status, 404, 'GET requests must not mutate products.');
    await response.text();
  }
  const deactivated = await deactivate(id, {return_query: 'q=Manguera&status=active&sort=name&direction=desc&page=2&redirect=https%3A%2F%2Fexample.com'});
  assert.equal(deactivated.response.status, 302);
  assert.equal(deactivated.response.headers.get('location'), '/admin/products?q=Manguera&status=active&sort=name&direction=desc&page=2');
  const afterSoftDelete = await rows();
  assert.equal(afterSoftDelete.length, beforeSoftDelete.length, 'Soft delete must not remove the row.');
  assert.deepEqual(afterSoftDelete.find((row) => row.id === id), {...oldRow, is_active: 0}, 'Soft delete must change only is_active.');
  assert.deepEqual(await files(), beforeSoftDeleteFiles, 'Soft delete must not remove images.');
  assert.equal((await deactivate(id)).response.status, 302, 'Repeated deactivation must remain safe.');
  const unpublished = await fetch(origin + '/product/new-01');
  assert.equal(unpublished.status, 404);
  await unpublished.text();
  const inactiveList = await fetch(origin + '/admin/products?q=NEW-01&status=inactive', {headers: {Cookie: cookie}});
  const inactiveHtml = await inactiveList.text();
  assert.ok(inactiveHtml.includes(`href="/admin/products/${id}/edit"`));
  assert.doesNotMatch(inactiveHtml.match(/<button[^>]*aria-label="Activar NEW-01"[^>]*>/)[0], /\bdisabled\b/);
  assert.ok(inactiveHtml.includes(`action="/admin/products/${id}/activate"`));

  assert.equal((await change(id, {is_active: '1'})).response.status, 302);
  assert.equal((await rows()).find((row) => row.id === id).is_active, 0, 'Editing an inactive product must not reactivate it.');
  assert.equal((await activate(id)).response.status, 302);
  const publishedAgain = await fetch(origin + '/product/new-01');
  assert.equal(publishedAgain.status, 200, 'The table activation route must allow reactivation.');
  await publishedAgain.text();
  assert.equal((await change(id, {description: '', unit: '', quantity_per_bulk: '', sizes: [''], remove_image: '1'})).response.status, 302);
  updated = (await rows()).find((row) => row.id === id);
  for (const key of ['description', 'quantity_per_bulk', 'sizes', 'image_filename']) assert.equal(updated[key], null);
  assert.equal(updated.unit, '');
  assert.ok(!existsSync(path.join(images, oldRow.image_filename)), 'Explicit image removal must clean up an unreferenced upload.');

  const beforeFailedDeactivate = await rows();
  const failedDeactivate = await deactivate(1, {}, '?database_deactivate_error=1');
  assert.equal(failedDeactivate.response.status, 302);
  assert.deepEqual(await rows(), beforeFailedDeactivate, 'Failed deactivation must preserve the stored row.');
  const failureList = await fetch(origin + '/admin/products', {headers: {Cookie: cookie}});
  assert.match(await failureList.text(), /No pudimos desactivar el producto/);
  const prefixedEdit = await fetch(origin + `/catalogo/admin/products/${id}/edit`, {headers: {Cookie: cookie}});
  assert.ok((await prefixedEdit.text()).includes(`action="/catalogo/admin/products/${id}/update"`));
  assert.equal((await change(id, {name: 'Edición con prefijo'}, undefined, '', '/catalogo')).response.headers.get('location'), '/catalogo/admin/products?q=NEW-01');
  assert.equal((await deactivate(id, {}, '', '/catalogo')).response.headers.get('location'), '/catalogo/admin/products');
  const beforeFailedActivation = await rows();
  assert.equal((await activate(id, {}, '?database_activate_error=1')).response.status, 302);
  assert.deepEqual(await rows(), beforeFailedActivation);
  const activationFailureList = await fetch(origin + '/admin/products', {headers: {Cookie: cookie}});
  assert.match(await activationFailureList.text(), /No pudimos activar el producto/);
  assert.equal((await activate(id, {}, '', '/catalogo')).response.headers.get('location'), '/catalogo/admin/products');

  assert.ok(!/Fatal error|Warning:|Notice:/.test(logs.replace(/PHP Warning:.*POST Content-Length[^\n]*\n/g, '')), logs.slice(-3000));
  console.log('Product management checks passed: creation, preloaded editing with immutable code/status, table-only activation/deactivation, image lifecycle, rollback, soft delete, auth/CSRF, validation, uploads and Apache subdirectories.');
  if (process.argv.includes('--preview')) {
    console.log(`Isolated preview: ${origin}/admin/products (test@example.test / Local-only-test123!). Test fixtures only.`);
    await new Promise((resolve) => process.once('SIGINT', resolve));
  }
} finally {
  if (server && server.exitCode === null) {
    const ended = once(server, 'exit');
    server.kill();
    await ended;
  }
  // This generated directory must remain strictly inside the OS temporary directory.
  const relative = path.relative(os.tmpdir(), temporary);
  assert.ok(relative && !relative.startsWith('..') && !path.isAbsolute(relative) && path.basename(temporary).startsWith('mazal-create-product-'));
  await rm(temporary, {recursive: true, force: true});
}
