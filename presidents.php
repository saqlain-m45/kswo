<?php
require_once __DIR__ . '/includes/functions.php';

$presidents = fetch_all('SELECT name, duration, description, photo_path FROM presidents ORDER BY sort_order ASC, id DESC');

$pageTitle = 'Past Presidents';
$activePage = 'Presidents';
$navType = 'public';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h2 class="section-title">Past Presidents</h2>
    <section class="timeline">
        <?php foreach ($presidents as $president): ?>
            <article class="card">
                <h3><?= htmlspecialchars($president['name']) ?></h3>
                <p><span class="badge badge-info"><?= htmlspecialchars($president['duration']) ?></span></p>
                <p><?= htmlspecialchars($president['description']) ?></p>
                <p class="notice">Photo placeholder available for optional profile image upload.</p>
            </article>
        <?php endforeach; ?>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
