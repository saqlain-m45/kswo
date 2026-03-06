<?php
require_once __DIR__ . '/db.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . page_url('login.php'));
        exit;
    }
}

function require_role(string $role): void
{
    require_login();
    $user = current_user();
    if (($user['role'] ?? '') !== $role) {
        header('Location: ' . page_url('index.php'));
        exit;
    }
}

function is_admin_user(): bool
{
    $role = current_user()['role'] ?? '';
    return in_array($role, ['admin', 'super_admin'], true);
}

function is_super_admin_user(): bool
{
    return (current_user()['role'] ?? '') === 'super_admin';
}

function is_president_user(): bool
{
    $designation = strtolower(trim((string)(current_user()['designation'] ?? '')));
    return $designation !== '' && strpos($designation, 'president') !== false;
}

function has_full_access_user(): bool
{
    return is_admin_user() || is_president_user();
}

function require_admin_access(): void
{
    require_login();
    if (!has_full_access_user()) {
        header('Location: ' . page_url('index.php'));
        exit;
    }
}

function require_super_admin(): void
{
    require_login();
    if (!has_full_access_user()) {
        header('Location: ' . page_url('admin/dashboard.php'));
        exit;
    }
}

function login_user(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'designation' => $user['designation'] ?? 'Member',
        'email' => $user['email'],
        'phone' => $user['phone'],
        'role' => $user['role'],
        'membership_status' => $user['membership_status'],
    ];
}

function refresh_session_user(int $userId): void
{
    $db = get_db_connection();
    if (!$db) {
        return;
    }
    $stmt = $db->prepare('SELECT id, full_name, designation, email, phone, role, membership_status FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user) {
        $_SESSION['user'] = $user;
    }
}
