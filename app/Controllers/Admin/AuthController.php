<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use PDO;

final class AuthController
{
    public function showLogin(): void
    {
        if (is_admin_authenticated()) {
            redirect('/admin');
        }

        View::render('admin/login', [
            'title' => 'Admin Login',
            'error' => pull_flash('auth_error'),
        ], 'admin');
    }

    public function login(): void
    {
        if (!verify_csrf($_POST['_token'] ?? null)) {
            flash('auth_error', 'Invalid form token. Please try again.');
            redirect('/admin/login');
        }

        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            flash('auth_error', 'Email and password are required.');
            redirect('/admin/login');
        }

        $stmt = Database::connection()->prepare(
            'SELECT id, full_name, email, password_hash, role, is_active
             FROM admins
             WHERE email = :email
             LIMIT 1',
        );
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || (int)$admin['is_active'] !== 1 || !password_verify($password, (string)$admin['password_hash'])) {
            flash('auth_error', 'Invalid credentials.');
            redirect('/admin/login');
        }

        session_regenerate_id(true);
        $_SESSION['admin_user'] = [
            'id' => (int)$admin['id'],
            'full_name' => (string)$admin['full_name'],
            'email' => (string)$admin['email'],
            'role' => (string)$admin['role'],
        ];

        $updateStmt = Database::connection()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id');
        $updateStmt->execute(['id' => $admin['id']]);

        redirect('/admin');
    }

    public function dashboard(): void
    {
        require_admin_auth();

        $stats = [
            'categories' => 0,
            'products' => 0,
            'active_products' => 0,
        ];

        $pdo = Database::connection();
        $stats['categories'] = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
        $stats['products'] = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
        $stats['active_products'] = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();

        View::render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'admin' => admin_user(),
            'stats' => $stats,
        ], 'admin');
    }

    public function logout(): void
    {
        if (!verify_csrf($_POST['_token'] ?? null)) {
            flash('auth_error', 'Invalid logout request.');
            redirect('/admin/login');
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();

        redirect('/admin/login');
    }
}
