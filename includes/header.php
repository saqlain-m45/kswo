<?php
require_once __DIR__ . '/config.php';

$pageTitle = $pageTitle ?? APP_NAME;
$activePage = $activePage ?? '';
$navType = $navType ?? 'public';

$publicNav = [
    'Home' => page_url('index.php'),
    'About' => page_url('about.php'),
    'Public Donate' => page_url('public_donate.php'),
    'Presidents' => page_url('presidents.php'),
    'Transparency' => page_url('transparency.php'),
];

if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? '';
    $designation = strtolower(trim((string)($_SESSION['user']['designation'] ?? '')));
    $hasFullAccess = in_array($role, ['admin', 'super_admin'], true) || ($designation !== '' && strpos($designation, 'president') !== false);
    $isAdminContext = $hasFullAccess;
    $publicNav['Dashboard'] = $isAdminContext ? page_url('admin/dashboard.php') : page_url('user/dashboard.php');
    $publicNav['Logout'] = page_url('logout.php');
} else {
    $publicNav['Register'] = page_url('register.php');
    $publicNav['Login'] = page_url('login.php');
}

$userNav = [
    'Dashboard' => page_url('user/dashboard.php'),
    'Donate' => page_url('user/donate.php'),
    'Profile' => page_url('user/profile.php'),
    'Logout' => page_url('logout.php'),
];

$adminNav = [
    'Dashboard' => page_url('admin/dashboard.php'),
    'Members' => page_url('admin/members.php'),
    'Donations' => page_url('admin/donations.php'),
    'Presidents' => page_url('admin/presidents.php'),
    'Settings' => page_url('admin/settings.php'),
];

$role = $_SESSION['user']['role'] ?? '';
$designation = strtolower(trim((string)($_SESSION['user']['designation'] ?? '')));
$hasFullAccess = in_array($role, ['admin', 'super_admin'], true) || ($designation !== '' && strpos($designation, 'president') !== false);

if ($hasFullAccess) {
    $adminNav['Payment Accounts'] = page_url('admin/payment_accounts.php');
}

$menu = $navType === 'admin' ? $adminNav : ($navType === 'user' ? $userNav : $publicNav);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= page_url('assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a href="<?= page_url('index.php') ?>" class="brand">
            <span class="brand-badge">K</span>
            <span>KSWO</span>
        </a>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">☰</button>
        <nav class="main-nav" id="mainNav">
            <?php foreach ($menu as $label => $url): ?>
                <a href="<?= $url ?>" class="<?= strtolower($activePage) === strtolower($label) ? 'active' : '' ?>"><?= htmlspecialchars($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
<main class="page-main">
