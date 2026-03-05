<?php
require_once __DIR__ . '/includes/functions.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donorName = trim($_POST['donor_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $receiptPath = null;

    if ($donorName === '' || strlen($donorName) < 3) {
        $error = 'Please enter a valid donor name.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($amount <= 0) {
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
            $stmt = $db->prepare('INSERT INTO public_donations (donor_name, email, phone, amount, payment_method, payment_status, transaction_id, receipt_path, message, donated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            if (!$stmt) {
                $error = 'Public donation module is not initialized. Please import the latest database.sql.';
            } else {
                $stmt->bind_param('sssdsssss', $donorName, $email, $phone, $amount, $paymentMethod, $status, $transactionId, $receiptPath, $message);

                if ($stmt->execute()) {
                    $success = 'Thank you for your public donation. Your support has been received successfully.';
                    $receipt = [
                        'donor_name' => $donorName,
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
}

$pageTitle = 'Public Donation';
$activePage = 'Public Donate';
$navType = 'public';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container form-wrap">
    <section class="card">
        <h2>Public Donation</h2>
        <p class="notice">Donate without member login • Secure channels • Receipt included</p>

        <?php if ($error): ?>
            <div class="notice" style="border-left-color:#c62828;background:#fef2f2;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="notice" style="border-left-color:#166534;background:#ecfdf3;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="" class="form-grid" enctype="multipart/form-data">
            <div class="form-group">
                <label for="donorName">Full Name</label>
                <input id="donorName" type="text" name="donor_name" required value="<?= htmlspecialchars($_POST['donor_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email (optional)</label>
                <input id="email" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone (optional)</label>
                <input id="phone" type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="amount">Donation Amount (PKR)</label>
                <select id="amount" name="amount" required>
                    <option value="">Select amount</option>
                    <option value="1000" <?= (($_POST['amount'] ?? '') === '1000') ? 'selected' : '' ?>>1,000</option>
                    <option value="2000" <?= (($_POST['amount'] ?? '') === '2000') ? 'selected' : '' ?>>2,000</option>
                    <option value="5000" <?= (($_POST['amount'] ?? '') === '5000') ? 'selected' : '' ?>>5,000</option>
                    <option value="10000" <?= (($_POST['amount'] ?? '') === '10000') ? 'selected' : '' ?>>10,000</option>
                </select>
            </div>

            <div class="form-group" style="grid-column:1/-1;">
                <label>Payment Method</label>
                <label style="display:block;margin:.35rem 0;"><input type="radio" name="payment_method" value="Easypaisa" style="width:auto;" <?= (($_POST['payment_method'] ?? '') === 'Easypaisa') ? 'checked' : '' ?>> Easypaisa</label>
                <label style="display:block;margin:.35rem 0;"><input type="radio" name="payment_method" value="JazzCash" style="width:auto;" <?= (($_POST['payment_method'] ?? '') === 'JazzCash') ? 'checked' : '' ?>> JazzCash</label>
                <label style="display:block;margin:.35rem 0;"><input type="radio" name="payment_method" value="Bank" style="width:auto;" <?= (($_POST['payment_method'] ?? '') === 'Bank') ? 'checked' : '' ?>> Bank Account</label>

                <div class="form-group" style="margin-top:.75rem;">
                    <label for="paymentReceiptPublic">Attach Payment Receipt (jpg, png, pdf)</label>
                    <input id="paymentReceiptPublic" type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.pdf" required>
                </div>

                <div class="method-accounts-wrap" id="methodAccountsWrapPublic" style="display:none;">
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
            </div>

            <div class="form-group" style="grid-column:1/-1;">
                <label for="message">Message (optional)</label>
                <textarea id="message" name="message" rows="3" placeholder="Any note for KSWO..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <div style="grid-column:1/-1;display:flex;gap:.6rem;flex-wrap:wrap;">
                <button class="btn btn-primary" type="submit">Submit Public Donation</button>
                <a class="btn btn-muted" href="<?= page_url('transparency.php') ?>">View Transparency</a>
            </div>
        </form>

        <?php if ($receipt): ?>
            <hr style="margin:1.25rem 0;border:none;border-top:1px solid #e2e8f0;">
            <h3>Donation Receipt</h3>
            <p><strong>Donor:</strong> <?= htmlspecialchars($receipt['donor_name']) ?></p>
            <p><strong>Amount:</strong> PKR <?= number_format((float)$receipt['amount'], 0) ?></p>
            <p><strong>Method:</strong> <?= htmlspecialchars($receipt['method']) ?></p>
            <p><strong>Status:</strong> <span class="badge badge-success"><?= ucfirst(htmlspecialchars($receipt['status'])) ?></span></p>
            <p><strong>Transaction ID:</strong> <?= htmlspecialchars($receipt['transaction_id']) ?></p>
            <?php if (!empty($receipt['receipt_path'])): ?>
                <p><strong>Receipt File:</strong> <a href="<?= page_url($receipt['receipt_path']) ?>" target="_blank" rel="noopener">View Attached Receipt</a></p>
            <?php endif; ?>
            <button class="btn btn-secondary" type="button" onclick="window.print()">Print Receipt</button>
        <?php endif; ?>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
