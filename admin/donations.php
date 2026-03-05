<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_access();

$dateFilter = trim($_GET['date'] ?? '');
$memberFilter = trim($_GET['member'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$hasPublicDonationsTable = fetch_one("SHOW TABLES LIKE 'public_donations'") !== null;

if ($hasPublicDonationsTable) {
    $memberConditions = ['1=1'];
    $publicConditions = ['1=1'];
    $types = '';
    $params = [];

    if ($dateFilter !== '') {
        $memberConditions[] = 'DATE(d.donated_at) = ?';
        $publicConditions[] = 'DATE(pd.donated_at) = ?';
        $types .= 'ss';
        $params[] = $dateFilter;
        $params[] = $dateFilter;
    }

    if ($memberFilter !== '') {
        $memberConditions[] = 'u.full_name LIKE ?';
        $publicConditions[] = 'pd.donor_name LIKE ?';
        $types .= 'ss';
        $params[] = '%' . $memberFilter . '%';
        $params[] = '%' . $memberFilter . '%';
    }

    if (in_array($statusFilter, ['paid', 'pending', 'failed'], true)) {
        $memberConditions[] = 'd.payment_status = ?';
        $publicConditions[] = 'pd.payment_status = ?';
        $types .= 'ss';
        $params[] = $statusFilter;
        $params[] = $statusFilter;
    }

    $sql = 'SELECT donor_name, donor_type, amount, payment_status, payment_method, receipt_path, donated_at
            FROM (
                SELECT
                    u.full_name AS donor_name,
                    "Member" AS donor_type,
                    d.amount,
                    d.payment_status,
                    d.payment_method,
                    d.receipt_path,
                    d.donated_at
                FROM donations d
                INNER JOIN users u ON u.id = d.user_id
                WHERE ' . implode(' AND ', $memberConditions) . '

                UNION ALL

                SELECT
                    pd.donor_name AS donor_name,
                    "Public" AS donor_type,
                    pd.amount,
                    pd.payment_status,
                    pd.payment_method,
                    pd.receipt_path,
                    pd.donated_at
                FROM public_donations pd
                WHERE ' . implode(' AND ', $publicConditions) . '
            ) combined
            ORDER BY donated_at DESC';

    $donations = fetch_all($sql, $types, $params);
} else {
    $sql = 'SELECT u.full_name AS donor_name,
                   "Member" AS donor_type,
                   d.amount,
                   d.payment_status,
                   d.payment_method,
                     d.receipt_path,
                   d.donated_at
            FROM donations d
            INNER JOIN users u ON u.id = d.user_id
            WHERE 1=1';
    $types = '';
    $params = [];

    if ($dateFilter !== '') {
        $sql .= ' AND DATE(d.donated_at) = ?';
        $types .= 's';
        $params[] = $dateFilter;
    }

    if ($memberFilter !== '') {
        $sql .= ' AND u.full_name LIKE ?';
        $types .= 's';
        $params[] = '%' . $memberFilter . '%';
    }

    if (in_array($statusFilter, ['paid', 'pending', 'failed'], true)) {
        $sql .= ' AND d.payment_status = ?';
        $types .= 's';
        $params[] = $statusFilter;
    }

    $sql .= ' ORDER BY d.donated_at DESC';
    $donations = fetch_all($sql, $types, $params);
}

$pageTitle = 'Donations';
$activePage = 'Donations';
$navType = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content">
    <h2 class="section-title">Donation Management</h2>
    <form class="filters" method="get" action="">
        <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
        <input type="text" name="member" value="<?= htmlspecialchars($memberFilter) ?>" placeholder="Filter by donor name">
        <select name="status">
            <option value="">All Payment Status</option>
            <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Paid</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
        </select>
        <button class="btn btn-muted" type="submit">Apply</button>
        <button class="btn btn-primary" id="exportCsv">Export CSV</button>
    </form>

    <div class="card table-wrap">
        <table id="adminDonationTable">
            <thead><tr><th>Donor</th><th>Type</th><th>Amount</th><th>Date</th><th>Status</th><th>Method</th><th>Receipt</th></tr></thead>
            <tbody>
                <?php if (!$donations): ?>
                    <tr><td colspan="7">No donations found.</td></tr>
                <?php endif; ?>
                <?php foreach ($donations as $donation): ?>
                    <?php $badgeClass = $donation['payment_status'] === 'paid' ? 'badge-success' : ($donation['payment_status'] === 'pending' ? 'badge-warning' : 'badge-danger'); ?>
                    <tr>
                        <td><?= htmlspecialchars($donation['donor_name']) ?></td>
                        <td><span class="badge <?= $donation['donor_type'] === 'Public' ? 'badge-info' : 'badge-success' ?>"><?= htmlspecialchars($donation['donor_type']) ?></span></td>
                        <td>PKR <?= number_format((float)$donation['amount'], 0) ?></td>
                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($donation['donated_at']))) ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($donation['payment_status'])) ?></span></td>
                        <td><?= htmlspecialchars($donation['payment_method']) ?></td>
                        <td>
                            <?php if (!empty($donation['receipt_path'])): ?>
                                <a href="<?= page_url($donation['receipt_path']) ?>" target="_blank" rel="noopener">View Receipt</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$hasPublicDonationsTable): ?>
        <p class="notice">Public donation table is not available yet. Import the latest database.sql to include public donations here.</p>
    <?php endif; ?>
    <p class="notice">Excel export can be enabled server-side by generating `.xlsx` from MySQL data (e.g., PhpSpreadsheet).</p>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
