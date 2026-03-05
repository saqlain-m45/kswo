<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('user');
$user = current_user();

$paymentAccountsByMethod = [
    'Easypaisa' => [],
    'JazzCash' => [],
    'Bank' => [],
];
$hasPaymentAccountsTable = fetch_one("SHOW TABLES LIKE 'payment_accounts'") !== null;
if ($hasPaymentAccountsTable) {
    $paymentAccounts = fetch_all('SELECT method, account_title, account_holder, account_number, icon_path, branch_info FROM payment_accounts WHERE is_active = 1 ORDER BY method ASC, sort_order ASC, id DESC');
    foreach ($paymentAccounts as $account) {
        $methodKey = $account['method'] ?? '';
        if (isset($paymentAccountsByMethod[$methodKey])) {
            $paymentAccountsByMethod[$methodKey][] = $account;
        }
    }
}

$error = '';
$success = '';
$receipt = null;
$activeStep = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $isMonthly = isset($_POST['is_monthly']) ? 1 : 0;
    $receiptPath = null;

    if ($amount <= 0) {
        $error = 'Please select a valid donation amount.';
    } elseif (!in_array($paymentMethod, ['Easypaisa', 'JazzCash', 'Bank'], true)) {
        $error = 'Please select a payment method.';
    } else {
        $upload = upload_receipt_file('payment_receipt');
        if ($upload['error']) {
            $error = $upload['error'];
        } else {
            $receiptPath = $upload['path'];
        }

        if ($error !== '') {
            // Validation failed above, stop before DB insert.
        } else {
        $db = get_db_connection();
        if (!$db) {
            $error = 'Database connection failed.';
        } else {
            $transactionId = generate_transaction_id();
            $status = 'paid';
            $stmt = $db->prepare('INSERT INTO donations (user_id, amount, payment_method, payment_status, is_monthly, transaction_id, receipt_path, donated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            $userId = (int)$user['id'];
            $stmt->bind_param('idssiss', $userId, $amount, $paymentMethod, $status, $isMonthly, $transactionId, $receiptPath);

            if ($stmt->execute()) {
                $success = 'Donation completed successfully.';
                $activeStep = 3;
                $receipt = [
                    'amount' => $amount,
                    'method' => $paymentMethod,
                    'status' => $status,
                    'transaction_id' => $transactionId,
                    'receipt_path' => $receiptPath,
                ];
            } else {
                $error = 'Unable to process donation right now. Please try again.';
            }
        }
        }
    }
}

$pageTitle = 'Donate';
$activePage = 'Donate';
$navType = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content form-wrap" style="max-width:none;">
    <section class="card">
        <h2>Donate Monthly</h2>
        <p class="notice">Secure Payment • Verified Organization • Receipt Included</p>
        <?php if ($error): ?>
            <div class="notice" style="border-left-color:#c62828;background:#fef2f2;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="notice" style="border-left-color:#166534;background:#ecfdf3;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="" id="donationForm" enctype="multipart/form-data">

        <div class="stepper">
            <div class="step <?= $activeStep === 1 ? 'active' : '' ?>" data-step="1">1. Amount</div>
            <div class="step <?= $activeStep === 2 ? 'active' : '' ?>" data-step="2">2. Method</div>
            <div class="step <?= $activeStep === 3 ? 'active' : '' ?>" data-step="3">3. Confirmation</div>
        </div>

        <div class="step-pane <?= $activeStep === 1 ? 'active' : '' ?>" id="stepPane1">
            <div class="form-group">
                <label for="donationAmount">Choose Amount (PKR)</label>
                <select id="donationAmount" name="amount">
                    <option value="">Select amount</option>
                    <option value="1000">1,000</option>
                    <option value="2000">2,000</option>
                    <option value="5000">5,000</option>
                </select>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="is_monthly" style="width:auto;"> Enable monthly subscription</label>
            </div>
            <button class="btn btn-primary step-next" type="button" data-current="1">Next</button>
        </div>

        <div class="step-pane <?= $activeStep === 2 ? 'active' : '' ?>" id="stepPane2">
            <p>Select payment method:</p>
            <label style="display:block;margin:.5rem 0;"><input type="radio" name="payment_method" value="Easypaisa" style="width:auto;"> Easypaisa</label>
            <label style="display:block;margin:.5rem 0;"><input type="radio" name="payment_method" value="JazzCash" style="width:auto;"> JazzCash</label>
            <label style="display:block;margin:.5rem 0;"><input type="radio" name="payment_method" value="Bank" style="width:auto;"> Bank Account</label>

            <div class="form-group" style="margin-top:.75rem;">
                <label for="paymentReceipt">Attach Payment Receipt (jpg, png, pdf)</label>
                <input id="paymentReceipt" type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>

            <div class="method-accounts-wrap" id="methodAccountsWrap" style="display:none;">
                <h4 style="margin:.75rem 0 .4rem;">Available Accounts</h4>

                <?php foreach (['Easypaisa', 'JazzCash', 'Bank'] as $method): ?>
                    <div class="method-accounts" data-method="<?= $method ?>" style="display:none;">
                        <?php if (!$hasPaymentAccountsTable): ?>
                            <p class="notice">Payment accounts are not configured yet.</p>
                        <?php elseif (empty($paymentAccountsByMethod[$method])): ?>
                            <p class="notice">No <?= htmlspecialchars($method) ?> accounts available right now.</p>
                        <?php else: ?>
                            <?php foreach ($paymentAccountsByMethod[$method] as $account): ?>
                                <article class="card" style="margin-bottom:.6rem;box-shadow:none;border:1px solid #e5e7eb;">
                                    <?php if (!empty($account['icon_path'])): ?>
                                        <img class="payment-method-icon" src="<?= page_url($account['icon_path']) ?>" alt="<?= htmlspecialchars($method) ?> icon">
                                    <?php endif; ?>
                                    <p><strong>Title:</strong> <?= htmlspecialchars($account['account_title']) ?></p>
                                    <p><strong>Number:</strong> <?= htmlspecialchars($account['account_number']) ?></p>
                                    <p><strong>Holder:</strong> <?= htmlspecialchars($account['account_holder'] ?: '-') ?></p>
                                    <p><strong>Details:</strong> <?= htmlspecialchars($account['branch_info'] ?: '-') ?></p>
                                    <button
                                        class="btn btn-muted copy-account-btn"
                                        type="button"
                                        data-copy="<?= htmlspecialchars($account['account_number']) ?>"
                                        data-default="Copy Number"
                                        style="margin-top:.25rem;"
                                    >Copy Number</button>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display:flex;gap:.5rem;">
                <button class="btn btn-muted step-prev" type="button" data-current="2">Back</button>
                <button class="btn btn-primary" id="confirmDonation" type="button">Confirm Payment</button>
            </div>
        </div>

        <div class="step-pane <?= $activeStep === 3 ? 'active' : '' ?>" id="stepPane3">
            <h3>Donation Receipt</h3>
            <p><strong>Amount:</strong> <span id="receiptAmount"><?= $receipt ? 'PKR ' . number_format((float)$receipt['amount'], 0) : '-' ?></span></p>
            <p><strong>Method:</strong> <span id="receiptMethod"><?= htmlspecialchars($receipt['method'] ?? '-') ?></span></p>
            <p><strong>Status:</strong> <span id="receiptStatus" class="badge badge-success"><?= htmlspecialchars(isset($receipt['status']) ? ucfirst($receipt['status']) : '-') ?></span></p>
            <p><strong>Transaction ID:</strong> <?= htmlspecialchars($receipt['transaction_id'] ?? ('KSWO-' . date('Ymd') . '-001')) ?></p>
            <?php if (!empty($receipt['receipt_path'])): ?>
                <p><strong>Receipt File:</strong> <a href="<?= page_url($receipt['receipt_path']) ?>" target="_blank" rel="noopener">View Attached Receipt</a></p>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">Save Donation</button>
            <button class="btn btn-secondary" type="button" onclick="window.print()">Print Receipt</button>
        </div>
        </form>
    </section>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
