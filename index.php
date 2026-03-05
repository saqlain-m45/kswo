<?php
require_once __DIR__ . '/includes/functions.php';

$stats = fetch_one(
    'SELECT
        (SELECT COUNT(*) FROM users) AS total_members,
        (
            (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = "paid" AND YEAR(donated_at) = YEAR(CURDATE()) AND MONTH(donated_at) = MONTH(CURDATE()))
            +
            (SELECT COALESCE(SUM(amount), 0) FROM public_donations WHERE payment_status = "paid" AND YEAR(donated_at) = YEAR(CURDATE()) AND MONTH(donated_at) = MONTH(CURDATE()))
        ) AS monthly_donations,
        (SELECT COUNT(*) FROM users WHERE membership_status = "verified") AS supported_students'
);

$pageTitle = 'Home';
$activePage = 'Home';
$navType = 'public';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container home-layout">
    <section class="hero home-hero home-section">
        <span class="hero-kicker">Khattak Student Welfare Organization</span>
        <h1>Professional Student Welfare Platform Built on Trust</h1>
        <p>Support deserving students through a transparent donation system. Members can donate from their dashboard, and the general public can contribute directly from a dedicated public donation form.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= page_url('register.php') ?>">Become a Member</a>
            <a class="btn btn-secondary" href="<?= page_url('user/donate.php') ?>">Member Donation</a>
            <a class="btn btn-muted" href="<?= page_url('public_donate.php') ?>">Public Donation</a>
        </div>
    </section>

    <h2 class="section-title">Quick Stats</h2>
    <section class="grid grid-3 home-section">
        <article class="card hover-card"><p>Total Members</p><p class="stat"><?= number_format((float)($stats['total_members'] ?? 0)) ?></p></article>
        <article class="card hover-card"><p>Monthly Donations</p><p class="stat">PKR <?= number_format((float)($stats['monthly_donations'] ?? 0)) ?></p></article>
        <article class="card hover-card"><p>Verified Students</p><p class="stat"><?= number_format((float)($stats['supported_students'] ?? 0)) ?></p></article>
    </section>

    <section class="card about-preview home-section">
        <h2>About KSWO</h2>
        <p>KSWO supports talented students facing financial hardship through a verified, accountable, and community-driven model. Our process prioritizes dignity for beneficiaries and confidence for donors.</p>
        <a class="btn btn-muted" href="<?= page_url('about.php') ?>">Learn More</a>
    </section>

    <h2 class="section-title">Our Core Services</h2>
    <section class="grid grid-3 home-section">
        <article class="card hover-card service-card">
            <h3>Member Support</h3>
            <p>Verified members contribute monthly and help sustain ongoing student support programs.</p>
        </article>
        <article class="card hover-card service-card">
            <h3>Public Contribution</h3>
            <p>Non-members can donate quickly through the dedicated public donation route.</p>
        </article>
        <article class="card hover-card service-card">
            <h3>Full Transparency</h3>
            <p>Donation activity and totals are visible in the public transparency dashboard.</p>
        </article>
    </section>

    <section class="grid grid-2 home-section">
        <article class="card hover-card donation-card">
            <h3>Donation for Members</h3>
            <p>Use your member account for monthly contribution with receipt and transaction tracking.</p>
            <a class="btn btn-primary" href="<?= page_url('user/donate.php') ?>">Go to Member Donation</a>
        </article>
        <article class="card hover-card donation-card">
            <h3>Donation for Public</h3>
            <p>Support the cause without account login using the public donation form.</p>
            <a class="btn btn-secondary" href="<?= page_url('public_donate.php') ?>">Go to Public Donation</a>
        </article>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
