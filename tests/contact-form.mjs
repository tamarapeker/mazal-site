import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { once } from 'node:events';
import { existsSync } from 'node:fs';
import { mkdtemp, mkdir, rm, writeFile } from 'node:fs/promises';
import net from 'node:net';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const php = process.env.PHP_BINARY || (existsSync('C:/xampp/php/php.exe') ? 'C:/xampp/php/php.exe' : 'php');
const temporary = await mkdtemp(path.join(os.tmpdir(), 'mazal-contact-test-'));
const messages = [];
let rejectDelivery = false;
let authenticatedConnections = 0;
const smtp = net.createServer((socket) => {
  socket.setEncoding('utf8');
  socket.write('220 localhost test SMTP\r\n');
  let buffer = '';
  let data = null;
  let authentication = 0;
  socket.on('data', (chunk) => {
    buffer += chunk;
    let end;
    while ((end = buffer.indexOf('\r\n')) !== -1) {
      const line = buffer.slice(0, end);
      buffer = buffer.slice(end + 2);
      if (data !== null) {
        if (line === '.') {
          messages.push(data.join('\r\n'));
          data = null;
          socket.write('250 Accepted\r\n');
        } else data.push(line.replace(/^\.\./, '.'));
      } else if (authentication === 1) {
        assert.equal(Buffer.from(line, 'base64').toString(), 'test-user');
        authentication = 2;
        socket.write('334 UGFzc3dvcmQ6\r\n');
      } else if (authentication === 2) {
        assert.equal(Buffer.from(line, 'base64').toString(), 'test-password');
        authentication = 0;
        authenticatedConnections++;
        socket.write('235 Authenticated\r\n');
      } else if (/^EHLO /i.test(line)) socket.write('250-localhost\r\n250 AUTH LOGIN\r\n');
      else if (line === 'AUTH LOGIN') {
        authentication = 1;
        socket.write('334 VXNlcm5hbWU6\r\n');
      } else if (/^RCPT TO:/i.test(line) && rejectDelivery) socket.write('550 Delivery rejected for test\r\n');
      else if (line === 'DATA') {
        data = [];
        socket.write('354 End with a dot\r\n');
      } else if (line === 'QUIT') socket.end('221 Bye\r\n');
      else socket.write('250 OK\r\n');
    }
  });
});

let server;
let logs = '';
try {
  smtp.listen(0, '127.0.0.1');
  await once(smtp, 'listening');
  const portReservation = net.createServer();
  portReservation.listen(0, '127.0.0.1');
  await once(portReservation, 'listening');
  const port = portReservation.address().port;
  await new Promise((resolve) => portReservation.close(resolve));
  const origin = `http://127.0.0.1:${port}`;
  const phpString = (value) => "'" + value.replaceAll('\\', '\\\\').replaceAll("'", "\\'") + "'";
  const routerFile = path.join(temporary, 'router.php');
  const sessionDirectory = path.join(temporary, 'sessions');
  await mkdir(sessionDirectory);
  await writeFile(routerFile, `<?php
$_SERVER['SCRIPT_NAME'] = str_starts_with($_SERVER['REQUEST_URI'], '/catalogo/') ? '/catalogo/index.php' : '/index.php';
$router = require ${phpString(path.join(root, 'bootstrap/app.php'))};
set_app_config(array_replace_recursive(config(), ['mail' => [
    'host' => isset($_GET['unconfigured']) ? '' : '127.0.0.1',
    'port' => ${smtp.address().port},
    'username' => 'test-user', 'password' => 'test-password', 'encryption' => 'none',
    'from_address' => 'info@mazalimportaciones.com.ar', 'from_name' => 'Mazal Importaciones',
]]));
$router->dispatch($_SERVER['REQUEST_METHOD'], request_path());
`);
  server = spawn(php, ['-d', `session.save_path=${sessionDirectory}`, '-S', `127.0.0.1:${port}`, routerFile], {
    cwd: root, windowsHide: true, stdio: ['ignore', 'pipe', 'pipe'],
  });
  server.stdout.on('data', (chunk) => { logs += chunk; });
  server.stderr.on('data', (chunk) => { logs += chunk; });
  server.on('error', (error) => { logs += error.message; });
  let ready = false;
  for (let attempt = 0; attempt < 60; attempt++) {
    try {
      const response = await fetch(origin + '/contact');
      await response.text();
      if (response.status === 200) { ready = true; break; }
    } catch {}
    await new Promise((resolve) => setTimeout(resolve, 50));
  }
  assert.ok(ready, 'PHP server did not start: ' + logs);

  async function session(prefix = '') {
    const route = prefix + '/contact';
    const response = await fetch(origin + route);
    assert.equal(response.status, 200);
    const html = await response.text();
    assert.ok(html.includes(`action="${route}"`));
    assert.ok(html.includes('mailto:info@mazalimportaciones.com.ar'));
    for (const field of ['full_name', 'phone', 'email']) {
      assert.match(html.match(new RegExp('<input[^>]*name="' + field + '"[^>]*>'))[0], /\brequired\b/);
    }
    for (const field of ['province', 'store_type']) {
      assert.doesNotMatch(html.match(new RegExp('<input[^>]*name="' + field + '"[^>]*>'))[0], /\brequired\b/);
    }
    const cookie = response.headers.get('set-cookie').split(';')[0];
    const token = html.match(/name="_token" value="([^"]+)"/)[1];
    return { route, cookie, token };
  }
  const values = {
    full_name: 'María Pérez', phone: '+54 9 11 1234 5678',
    email: 'visitor@example.com', province: 'Córdoba', store_type: 'distribuidor',
    message: 'Quisiera conocer los precios mayoristas.\n¿Me pueden enviar el catálogo?',
  };
  async function submit(client, overrides = {}, query = '') {
    const response = await fetch(origin + client.route + query, {
      method: 'POST', redirect: 'manual', headers: { Cookie: client.cookie },
      body: new URLSearchParams({ _token: client.token, ...values, ...overrides }),
    });
    assert.equal(response.status, 302);
    assert.equal(response.headers.get('location'), client.route);
    const page = await fetch(origin + client.route, { headers: { Cookie: client.cookie } });
    assert.equal(page.status, 200);
    return page.text();
  }

  const invalid = await session();
  assert.match(await submit(invalid, { _token: '' }), /sesión del formulario venció/);
  assert.match(await submit(invalid, { '_token[]': 'invalid', _token: '' }), /sesión del formulario venció/);
  assert.match(await submit(invalid, { email: 'invalid', full_name: '<script>alert(1)</script>' }), /email válido/);
  assert.match(await submit(invalid, { email: 'invalid', full_name: '<script>alert(1)</script>' }), /&lt;script&gt;/);
  assert.match(await submit(invalid, { email: 'visitor@example.com\r\nBcc: other@example.com' }), /valor válido|email válido/);
  assert.match(await submit(invalid, { store_type: 'on' }), /tipo de punto de venta válido/);
  for (const [field, label] of [['full_name', 'Nombre y apellido'], ['phone', 'Teléfono'], ['email', 'Email']]) {
    assert.ok((await submit(invalid, { [field]: '   ' })).includes('Completá ' + label + '.'));
  }
  assert.match(await submit(invalid, { full_name: 'A' }), /entre 2 y 150 caracteres/);
  assert.match(await submit(invalid, { full_name: 'x'.repeat(151) }), /entre 2 y 150 caracteres/);
  for (const phone of ['abcdefghi', '123', '1'.repeat(16), '54+11 1234 5678']) {
    assert.match(await submit(invalid, { phone }), /teléfono válido/);
  }
  assert.match(await submit(invalid, { 'phone[]': 'invalid', phone: '' }), /Teléfono/);
  assert.match(await submit(invalid, { message: 'x'.repeat(2001) }), /hasta 2000 caracteres/);
  const escapedMessage = await submit(invalid, { email: 'invalid', message: '</textarea><script>alert(1)</script>' });
  assert.ok(escapedMessage.includes('&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;'));
  assert.doesNotMatch(escapedMessage, /<\/textarea><script>/);
  assert.equal(messages.length, 0);
  assert.match(await submit(invalid, { website: 'spam.example' }), /consulta fue enviada/);
  assert.equal(messages.length, 0);

  for (const [type, label] of [['ferreteria', 'Ferretería al público'], ['distribuidor', 'Distribuidor'], ['mayorista', 'Mayorista']]) {
    const client = await session();
    assert.match(await submit(client, { store_type: type }), /consulta fue enviada/);
    const message = messages.at(-1);
    assert.match(message, /^To: info@mazalimportaciones\.com\.ar$/m);
    assert.match(message, /Reply-To: .*<visitor@example\.com>/);
    assert.match(message, /From: Mazal Importaciones <info@mazalimportaciones\.com\.ar>/);
    const body = Buffer.from(message.split('\r\n\r\n').slice(1).join('\r\n\r\n'), 'base64').toString('utf8').replaceAll('\r\n', '\n');
    for (const field of ['full_name', 'phone', 'email', 'province', 'message']) assert.ok(body.includes(values[field]));
    assert.ok(body.includes(label));
    const count = messages.length;
    assert.match(await submit(client), /Esperá un minuto/);
    assert.equal(messages.length, count);
  }

  const failure = await session();
  rejectDelivery = true;
  const failurePage = await submit(failure);
  assert.match(failurePage, /No pudimos enviar tu consulta/);
  assert.ok(failurePage.includes(`value="${values.full_name}"`));
  assert.ok(failurePage.includes(values.message));
  assert.doesNotMatch(failurePage, /consulta fue enviada|Delivery rejected for test/);
  assert.equal(messages.length, 3);
  rejectDelivery = false;
  assert.match(await submit(failure), /consulta fue enviada/);
  const refreshed = await fetch(origin + failure.route, { headers: { Cookie: failure.cookie } });
  assert.doesNotMatch(await refreshed.text(), /consulta fue enviada/);
  assert.equal(messages.length, 4);

  assert.match(await submit(await session(), {}, '?unconfigured=1'), /No pudimos enviar tu consulta/);
  assert.equal(messages.length, 4);
  assert.match(await submit(await session('/catalogo'), { province: '', store_type: '', message: '' }), /consulta fue enviada/);
  assert.equal(messages.length, 5);
  const emptyMessageBody = Buffer.from(messages.at(-1).split('\r\n\r\n').slice(1).join('\r\n\r\n'), 'base64').toString('utf8');
  assert.ok(emptyMessageBody.includes('Sin comentarios adicionales.'));
  assert.ok(emptyMessageBody.includes('Provincia: No especificada'));
  assert.ok(emptyMessageBody.includes('Tipo de punto de venta: No especificado'));
  assert.ok(authenticatedConnections >= 6, 'Expected authenticated SMTP, including rejected delivery');
  assert.doesNotMatch(logs, /PHP (?:Fatal error|Warning|Parse error|Deprecated)/);
  console.log('Contact integration checks passed: validation, optional multiline message, safe field recovery, CSRF, spam trap, SMTP auth, all fields and store types, recipient, Reply-To, cooldown, retry, missing config and subdirectory routes.');
} finally {
  if (server && server.exitCode === null && !server.killed) {
    const exited = once(server, 'exit');
    server.kill();
    await exited;
  }
  await new Promise((resolve) => smtp.close(resolve));
  assert.equal(path.dirname(temporary), path.resolve(os.tmpdir()));
  assert.ok(path.basename(temporary).startsWith('mazal-contact-test-'));
  await rm(temporary, { recursive: true, force: true });
}
