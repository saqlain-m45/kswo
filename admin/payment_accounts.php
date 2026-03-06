<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_super_admin();
$db = get_db_connection();

$error = '';
$success = '';
$hasTable = fetch_one("SHOW TABLES LIKE 'payment_accounts'") !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db && $hasTable) {
    $action = $_POST['action'] ?? 'save';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $stmt = $db->prepare('DELETE FROM payment_accounts WHERE id = ?');
        $stmt->bind_param('i', $id);
        $success = $stmt->execute() ? 'Payment account deleted successfully.' : 'Unable to delete payment account.';
    } else {
        $method = trim($_POST['method'] ?? '');
        $accountTitle = trim($_POST['account_title'] ?? '');
        $accountHolder = trim($_POST['account_holder'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $existingIcon = trim($_POST['existing_icon_path'] ?? '');
        $branchInfo = trim($_POST['branch_info'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $iconPath = $existingIcon;

        if (!in_array($method, ['Easypaisa', 'JazzCash', 'Bank'], true)) {
            $error = 'Please select a valid payment method.';
        } elseif ($accountTitle === '' || $accountNumber === '') {
            $error = 'Account title and account number are required.';
        } else {
            $upload = upload_payment_icon('icon_file');
            if ($upload['error']) {
                $error = $upload['error'];
            } elseif ($upload['path']) {
                $iconPath = $upload['path'];
            }
        }

        if ($error === '') {
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE payment_accounts SET method=?, account_title=?, account_holder=?, account_number=?, icon_path=?, branch_info=?, is_active=?, sort_order=? WHERE id=?');
                $stmt->bind_param('ssssssiii', $method, $accountTitle, $accountHolder, $accountNumber, $iconPath, $branchInfo, $isActive, $sortOrder, $id);
                $success = $stmt->execute() ? 'Payment account updated successfully.' : 'Unable to update payment account.';
            } else {
                $stmt = $db->prepare('INSERT INTO payment_accounts (method, account_title, account_holder, account_number, icon_path, branch_info, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssii', $method, $accountTitle, $accountHolder, $accountNumber, $iconPath, $branchInfo, $isActive, $sortOrder);
                $success = $stmt->execute() ? 'Payment account added successfully.' : 'Unable to add payment account.';
            }
        }
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$editRow = null;
if ($hasTable && $editId > 0) {
    $editRow = fetch_one('SELECT id, method, account_title, account_holder, account_number, icon_path, branch_info, is_active, sort_order FROM payment_accounts WHERE id = ?', 'i', [$editId]);
}

$accounts = $hasTable
    ? fetch_all('SELECT id, method, account_title, account_holder, account_number, icon_path, branch_info, is_active, sort_order FROM payment_accounts ORDER BY method ASC, sort_order ASC, id DESC')
    : [];

$pageTitle = 'Payment Accounts';
$activePage = 'Payment Accounts';
$navType = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell payment-accounts-page">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content form-wrap" style="max-width:none;">
    <section class="card payment-form-card">
        <h2>Payment Accounts (Full Access)</h2>

        <?php if (!$hasTable): ?>
            <div class="notice" style="border-left-color:#c62828;background:#fef2f2;">`payment_accounts` table is missing. Import the latest database.sql.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="notice" style="border-left-color:#c62828;background:#fef2f2;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="notice" style="border-left-color:#166534;background:#ecfdf3;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($hasTable): ?>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)($editRow['id'] ?? 0) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="existing_icon_path" value="<?= htmlspecialchars($editRow['icon_path'] ?? '') ?>">
            <div class="form-grid payment-form-grid">
                <div class="form-group">
                    <label>Method</label>
                    <select name="method" required>
                        <option value="">Select method</option>
                        <option value="Easypaisa" <?= (($editRow['method'] ?? '') === 'Easypaisa') ? 'selected' : '' ?>>Easypaisa</option>
                        <option value="JazzCash" <?= (($editRow['method'] ?? '') === 'JazzCash') ? 'selected' : '' ?>>JazzCash</option>
                        <option value="Bank" <?= (($editRow['method'] ?? '') === 'Bank') ? 'selected' : '' ?>>Bank</option>
                    </select>
                </div>
                <div class="form-group"><label>Account Title</label><input name="account_title" required value="<?= htmlspecialchars($editRow['account_title'] ?? '') ?>"></div>
                <div class="form-group"><label>Account Holder</label><input name="account_holder" value="<?= htmlspecialchars($editRow['account_holder'] ?? '') ?>"></div>
                <div class="form-group"><label>Account Number</label><input name="account_number" required value="<?= htmlspecialchars($editRow['account_number'] ?? '') ?>"></div>
                <div class="form-group"><label>Method Icon</label><input type="file" name="icon_file" accept=".png,.jpg,.jpeg,.webp,.svg"></div>
                <div class="form-group"><label>Branch / Details</label><input name="branch_info" value="<?= htmlspecialchars($editRow['branch_info'] ?? '') ?>"></div>
                <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)($editRow['sort_order'] ?? 0) ?>"></div>
                <div class="form-group"><label><input type="checkbox" name="is_active" style="width:auto;" <?= ((int)($editRow['is_active'] ?? 1) === 1) ? 'checked' : '' ?>> Active</label></div>
            </div>
            <?php if (!empty($editRow['icon_path'])): ?>
                <p style="margin:.5rem 0;"><strong>Current Icon:</strong> <img src="<?= page_url($editRow['icon_path']) ?>" alt="Method Icon" style="width:28px;height:28px;object-fit:contain;vertical-align:middle;"></p>
            <?php endif; ?>
            <div class="payment-form-actions">
                <button class="btn btn-primary" type="submit">Save Account</button>
                <a class="btn btn-muted" href="<?= page_url('admin/payment_accounts.php') ?>">Reset</a>
            </div>
        </form>
        <?php endif; ?>
    </section>

    <section class="card payment-table-card" style="margin-top:1rem;">
        <h3>Configured Accounts</h3>
        <div class="table-wrap">
            <table class="payment-accounts-table">
                <thead><tr><th>Icon</th><th>Method</th><th>Title</th><th>Number</th><th>Holder</th><th>Details</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$accounts): ?>
                    <tr><td colspan="8">No payment accounts configured yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td>
                            <?php if (!empty($account['icon_path'])): ?>
                                <img src="<?= page_url($account['icon_path']) ?>" alt="<?= htmlspecialchars($account['method']) ?> icon" style="width:28px;height:28px;object-fit:contain;">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($account['method']) ?></td>
                        <td><?= htmlspecialchars($account['account_title']) ?></td>
                        <td><?= htmlspecialchars($account['account_number']) ?></td>
                        <td><?= htmlspecialchars($account['account_holder'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($account['branch_info'] ?: '-') ?></td>
                        <td><span class="badge <?= (int)$account['is_active'] === 1 ? 'badge-success' : 'badge-warning' ?>"><?= (int)$account['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                        <td>
                            <div class="payment-table-actions">
                            <a class="btn btn-muted" href="<?= page_url('admin/payment_accounts.php?edit=' . (int)$account['id']) ?>">Edit</a>
                            <form method="post" action="" style="display:inline-block;">
                                <input type="hidden" name="id" value="<?= (int)$account['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="btn" style="background:#fee2e2;color:#991b1b;" type="submit" onclick="return confirm('Do you really want to delete this payment account?');">Delete</button>
                            </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
