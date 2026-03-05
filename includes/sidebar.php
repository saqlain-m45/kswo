<?php
require_once __DIR__ . '/config.php';

$activePage = $activePage ?? '';
$navType = $navType ?? 'user';

$userItems = [
    ['label' => 'Dashboard', 'url' => page_url('user/dashboard.php')],
    ['label' => 'Donate', 'url' => page_url('user/donate.php')],
    ['label' => 'Profile', 'url' => page_url('user/profile.php')],
    ['label' => 'Logout', 'url' => page_url('logout.php')],
];

$adminItems = [
    ['label' => 'Dashboard', 'url' => page_url('admin/dashboard.php')],
    ['label' => 'Members', 'url' => page_url('admin/members.php')],
    ['label' => 'Donations', 'url' => page_url('admin/donations.php')],
    ['label' => 'Presidents', 'url' => page_url('admin/presidents.php')],
    ['label' => 'Settings', 'url' => page_url('admin/settings.php')],
    ['label' => 'Logout', 'url' => page_url('logout.php')],
];

if (($_SESSION['user']['role'] ?? '') === 'super_admin') {
    array_splice($adminItems, 5, 0, [['label' => 'Payment Accounts', 'url' => page_url('admin/payment_accounts.php')]]);
}

$items = $navType === 'admin' ? $adminItems : $userItems;
?>
<aside class="dashboard-sidebar card">
    <h3><?= $navType === 'admin' ? 'Admin Panel' : 'Member Panel' ?></h3>
    <nav class="sidebar-nav">
        <?php foreach ($items as $item): ?>
            <a href="<?= $item['url'] ?>" class="<?= strtolower($activePage) === strtolower($item['label']) ? 'active' : '' ?>"><?= htmlspecialchars($item['label']) ?></a>
        <?php endforeach; ?>
    </nav>
</aside>
