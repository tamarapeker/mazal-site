#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Creates or updates an admin account.
 *
 * Example:
 * php database/scripts/seed_admin.php ^
 *   --full-name="Site Admin" ^
 *   --email="admin@example.com" ^
 *   --password="ChangeMe123!" ^
 *   --database=hardware_importer_catalog ^
 *   --username=root ^
 *   --password-db=""
 */

main();

function main(): void
{
    $options = getopt(
        '',
        [
            'full-name:',
            'email:',
            'password:',
            'role::',
            'host::',
            'port::',
            'database::',
            'username::',
            'password-db::',
            'help',
        ],
    );

    if (isset($options['help'])) {
        printUsage();
        return;
    }

    $fullName = trim((string)($options['full-name'] ?? ''));
    $email = strtolower(trim((string)($options['email'] ?? '')));
    $plainPassword = (string)($options['password'] ?? '');
    $role = trim((string)($options['role'] ?? 'super_admin'));

    if ($fullName === '' || $email === '' || $plainPassword === '') {
        fwrite(STDERR, "Error: --full-name, --email and --password are required.\n\n");
        printUsage();
        exit(1);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email format.');
    }

    $validRoles = ['super_admin', 'editor'];
    if (!in_array($role, $validRoles, true)) {
        throw new InvalidArgumentException('Invalid role. Allowed values: super_admin, editor.');
    }

    $host = (string)($options['host'] ?? getenv('DB_HOST') ?: '127.0.0.1');
    $port = (int)($options['port'] ?? getenv('DB_PORT') ?: 3306);
    $database = (string)($options['database'] ?? getenv('DB_NAME') ?: 'hardware_importer_catalog');
    $username = (string)($options['username'] ?? getenv('DB_USER') ?: 'root');
    $password = (string)($options['password-db'] ?? getenv('DB_PASS') ?: '');

    $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        throw new RuntimeException('Could not generate password hash.');
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);
    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    );

    $sql = <<<SQL
INSERT INTO admins (full_name, email, password_hash, role, is_active)
VALUES (:full_name, :email, :password_hash, :role, 1)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  is_active = 1
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':password_hash' => $passwordHash,
        ':role' => $role,
    ]);

    fwrite(STDOUT, "Admin account created/updated successfully: {$email}\n");
}

function printUsage(): void
{
    $usage = <<<TXT
Usage:
  php database/scripts/seed_admin.php [options]

Required:
  --full-name=NAME          Full name for the admin account
  --email=EMAIL             Admin email
  --password=PASSWORD       Plain password (it is hashed by the script)

Optional:
  --role=ROLE               super_admin (default) or editor
  --host=HOST               Database host (default: 127.0.0.1)
  --port=PORT               Database port (default: 3306)
  --database=NAME           Database name (default: hardware_importer_catalog)
  --username=USER           Database username (default: root)
  --password-db=PASS        Database password (default: empty)
  --help                    Print this help message

TXT;

    fwrite(STDOUT, $usage);
}
