<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('user');
$user = current_user();

$donations = fetch_all(
    'SELECT amount, payment_method, payment_status, donated_at
     FROM donations
     WHERE user_id = ?
     ORDER BY donated_at DESC',
    'i',
    [(int)$user['id']]
);

$notifications = fetch_all(
    'SELECT message, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5',
    'i',
    [(int)$user['id']]
);

$statusClass = ($user['membership_status'] ?? 'pending') === 'verified' ? 'badge-success' : (($user['membership_status'] ?? 'pending') === 'rejected' ? 'badge-danger' : 'badge-warning');

$pageTitle = 'User Dashboard';
$activePage = 'Dashboard';
$navType = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content">
    <h2 class="section-title">User Dashboard</h2>
    <section class="grid grid-3">
        <article class="card">
            <h3>Profile Summary</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <a class="btn btn-muted" href="<?= page_url('user/profile.php') ?>">Edit Profile</a>
        </article>
        <article class="card">
            <h3>Membership Status</h3>
            <p><span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($user['membership_status'])) ?></span></p>
            <p>Verification team will review your profile shortly.</p>
        </article>
        <article class="card">
            <h3>Quick Action</h3>
            <p>Support monthly student welfare in one click.</p>
            <a class="btn btn-primary" href="<?= page_url('user/donate.php') ?>">Quick Donate</a>
        </article>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h3>Donation History</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                <tbody>
                    <?php if (!$donations): ?>
                        <tr><td colspan="4">No donations yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($donations as $donation): ?>
                        <?php $badgeClass = $donation['payment_status'] === 'paid' ? 'badge-success' : ($donation['payment_status'] === 'pending' ? 'badge-warning' : 'badge-danger'); ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($donation['donated_at']))) ?></td>
                            <td>PKR <?= number_format((float)$donation['amount'], 0) ?></td>
                            <td><?= htmlspecialchars($donation['payment_method']) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($donation['payment_status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h3>Notifications</h3>
        <?php if (!$notifications): ?>
            <p class="notice">No notifications yet.</p>
        <?php endif; ?>
        <?php foreach ($notifications as $notification): ?>
            <p class="notice"><?= htmlspecialchars($notification['message']) ?> (<?= htmlspecialchars(date('Y-m-d', strtotime($notification['created_at']))) ?>)</p>
        <?php endforeach; ?>
    </section>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
