<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';

if (is_logged_in()) {
    $user = current_user();
    if (in_array(($user['role'] ?? ''), ['admin', 'super_admin'], true)) {
        header('Location: ' . page_url('admin/dashboard.php'));
    } else {
        header('Location: ' . page_url('user/dashboard.php'));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = get_db_connection();
    if (!$db) {
        $error = 'Database connection failed. Please import database/kswo.sql first.';
    } else {
        $stmt = $db->prepare('SELECT id, full_name, designation, email, phone, role, membership_status, password_hash FROM users WHERE email = ? OR phone = ? LIMIT 1');
        $stmt->bind_param('ss', $identity, $identity);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid credentials. Please try again.';
        } else {
            login_user($user);
            if (in_array($user['role'], ['admin', 'super_admin'], true)) {
                header('Location: ' . page_url('admin/dashboard.php'));
            } else {
                header('Location: ' . page_url('user/dashboard.php'));
            }
            exit;
        }
    }
}

$pageTitle = 'Login';
$activePage = 'Login';
$navType = 'public';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container form-wrap">
    <section class="card">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="notice" style="border-left-color:#c62828;background:#fef2f2;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="post" action="" novalidate>
            <div class="form-group">
                <label for="identity">Email or Phone</label>
                <input id="identity" name="identity" value="<?= htmlspecialchars($_POST['identity'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="loginPassword">Password</label>
                <input type="password" id="loginPassword" name="password" required>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <label style="display:flex;align-items:center;gap:.4rem;"><input type="checkbox" name="remember" style="width:auto;"> Remember Me</label>
                <a href="#" style="color:var(--primary);font-weight:600;">Forgot Password?</a>
            </div>
            <div class="notice">Use clear error handling here after backend auth integration (invalid credentials, locked account, session expired).</div>
            <button class="btn btn-primary" type="submit">Login</button>
        </form>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
