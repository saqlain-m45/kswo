<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_access();

$stats = fetch_one(
    'SELECT
        (SELECT COUNT(*) FROM users) AS total_members,
        (SELECT COUNT(*) FROM users WHERE membership_status = "verified") AS verified_members,
        (SELECT COUNT(*) FROM users WHERE membership_status = "pending") AS pending_members,
        (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = "paid") AS total_donations,
        (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = "paid" AND YEAR(donated_at) = YEAR(CURDATE()) AND MONTH(donated_at) = MONTH(CURDATE())) AS month_donations'
);

$pageTitle = 'Admin Dashboard';
$activePage = 'Dashboard';
$navType = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content">
    <h2 class="section-title">Admin Dashboard</h2>
    <section class="grid grid-3">
        <article class="card"><p>Total Members</p><p class="stat"><?= number_format((float)($stats['total_members'] ?? 0), 0) ?></p></article>
        <article class="card"><p>Verified Members</p><p class="stat"><?= number_format((float)($stats['verified_members'] ?? 0), 0) ?></p></article>
        <article class="card"><p>Pending Members</p><p class="stat"><?= number_format((float)($stats['pending_members'] ?? 0), 0) ?></p></article>
        <article class="card"><p>Total Donations</p><p class="stat">PKR <?= number_format((float)($stats['total_donations'] ?? 0), 0) ?></p></article>
        <article class="card"><p>This Month</p><p class="stat">PKR <?= number_format((float)($stats['month_donations'] ?? 0), 0) ?></p></article>
        <article class="card"><p>Conversion Rate</p><p class="stat">78%</p></article>
    </section>
    <section class="card" style="margin-top:1rem;">
        <h3>Monthly Donations Graph</h3>
        <canvas id="donationChart" height="100"></canvas>
    </section>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
