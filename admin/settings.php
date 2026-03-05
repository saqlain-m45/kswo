<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_access();
$db = get_db_connection();

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $settingsInput = [
        'session_timeout_minutes' => trim($_POST['session_timeout_minutes'] ?? '30'),
        'payment_trust_badge' => trim($_POST['payment_trust_badge'] ?? 'Yes'),
        'notification_email' => trim($_POST['notification_email'] ?? ''),
        'default_currency' => trim($_POST['default_currency'] ?? 'PKR'),
    ];

    $stmt = $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($settingsInput as $key => $value) {
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
    }

    $success = 'Settings updated successfully.';
}

$rows = fetch_all('SELECT setting_key, setting_value FROM settings');
$settings = [];
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$pageTitle = 'Settings';
$activePage = 'Settings';
$navType = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content form-wrap" style="max-width:none;">
    <section class="card">
        <h2>Admin Settings</h2>
        <?php if ($success): ?>
            <div class="notice" style="border-left-color:#166534;background:#ecfdf3;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="form-grid">
                <div class="form-group"><label>Session Timeout (minutes)</label><input type="number" name="session_timeout_minutes" value="<?= htmlspecialchars($settings['session_timeout_minutes'] ?? '30') ?>"></div>
                <div class="form-group"><label>Enable Payment Trust Badge</label><select name="payment_trust_badge"><option <?= ($settings['payment_trust_badge'] ?? 'Yes') === 'Yes' ? 'selected' : '' ?>>Yes</option><option <?= ($settings['payment_trust_badge'] ?? 'Yes') === 'No' ? 'selected' : '' ?>>No</option></select></div>
                <div class="form-group"><label>Notification Email</label><input type="email" name="notification_email" value="<?= htmlspecialchars($settings['notification_email'] ?? 'admin@kswo.org') ?>"></div>
                <div class="form-group"><label>Default Currency</label><input name="default_currency" value="<?= htmlspecialchars($settings['default_currency'] ?? 'PKR') ?>"></div>
            </div>
            <button class="btn btn-primary" type="submit">Save Settings</button>
        </form>
    </section>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
