<?php
require_once __DIR__ . '/includes/functions.php';

$donations = fetch_all(
    'SELECT name, amount, date, month, year, donor_type
     FROM (
            SELECT
                u.full_name AS name,
                d.amount,
                DATE(d.donated_at) AS date,
                DATE_FORMAT(d.donated_at, "%m") AS month,
                YEAR(d.donated_at) AS year,
                "Member" AS donor_type,
                d.donated_at AS sort_date
            FROM donations d
            INNER JOIN users u ON u.id = d.user_id
            WHERE d.payment_status = "paid"

            UNION ALL

            SELECT
                pd.donor_name AS name,
                pd.amount,
                DATE(pd.donated_at) AS date,
                DATE_FORMAT(pd.donated_at, "%m") AS month,
                YEAR(pd.donated_at) AS year,
                "Public" AS donor_type,
                pd.donated_at AS sort_date
            FROM public_donations pd
            WHERE pd.payment_status = "paid"
        ) combined
     ORDER BY sort_date DESC'
);

$monthlyTotals = fetch_one(
    'SELECT
        (
            (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = "paid" AND YEAR(donated_at) = YEAR(CURDATE()) AND MONTH(donated_at) = MONTH(CURDATE()))
            +
            (SELECT COALESCE(SUM(amount), 0) FROM public_donations WHERE payment_status = "paid" AND YEAR(donated_at) = YEAR(CURDATE()) AND MONTH(donated_at) = MONTH(CURDATE()))
        ) AS monthly_total,
        (
            (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = "paid" AND YEAR(donated_at) = YEAR(CURDATE()))
            +
            (SELECT COALESCE(SUM(amount), 0) FROM public_donations WHERE payment_status = "paid" AND YEAR(donated_at) = YEAR(CURDATE()))
        ) AS yearly_total,
        (
            (SELECT COUNT(DISTINCT user_id) FROM donations WHERE payment_status = "paid")
            +
            (SELECT COUNT(DISTINCT CONCAT(COALESCE(email, ""), "|", donor_name)) FROM public_donations WHERE payment_status = "paid")
        ) AS active_donors'
);

$pageTitle = 'Transparency';
$activePage = 'Transparency';
$navType = 'public';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <section class="card about-intro-card" style="margin-bottom:1rem;">
        <h2>Public Transparency Dashboard <span class="badge badge-success">Transparency Verified</span></h2>
        <p>Every eligible donation is listed for public visibility with donor type, amount, and date so the community can review financial activity clearly.</p>
    </section>

    <section class="grid grid-3" style="margin-bottom:1rem;">
        <article class="card hover-card"><p>Monthly Total</p><p class="stat">PKR <?= number_format((float)($monthlyTotals['monthly_total'] ?? 0), 0) ?></p></article>
        <article class="card hover-card"><p>Yearly Total</p><p class="stat">PKR <?= number_format((float)($monthlyTotals['yearly_total'] ?? 0), 0) ?></p></article>
        <article class="card hover-card"><p>Active Donors</p><p class="stat"><?= number_format((float)($monthlyTotals['active_donors'] ?? 0), 0) ?></p></article>
    </section>

    <div class="card" style="margin-bottom:1rem;">
        <div class="filters">
            <input type="text" id="transparencySearch" placeholder="Search donor name">
            <select id="monthFilter">
                <option value="">All Months</option>
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="12">December</option>
            </select>
            <select id="yearFilter">
                <option value="">All Years</option>
                <option value="2026">2026</option>
                <option value="2025">2025</option>
            </select>
        </div>

        <div class="table-wrap">
            <table id="transparencyTable">
                <thead>
                <tr><th>Donor Name</th><th>Type</th><th>Amount</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php foreach ($donations as $donation): ?>
                    <tr data-month="<?= $donation['month'] ?>" data-year="<?= $donation['year'] ?>">
                        <td><?= htmlspecialchars($donation['name']) ?></td>
                        <td><span class="badge <?= $donation['donor_type'] === 'Public' ? 'badge-info' : 'badge-success' ?>"><?= htmlspecialchars($donation['donor_type']) ?></span></td>
                        <td>PKR <?= number_format($donation['amount']) ?></td>
                        <td><?= htmlspecialchars($donation['date']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <article class="card hover-card">
        <h3>Donation Trends</h3>
        <canvas id="donationChart" height="100"></canvas>
    </article>

    <section class="grid grid-2" style="margin-top:1rem;">
        <article class="card hover-card">
            <h3>Governance Note</h3>
            <p>Transaction records are visible for audit confidence and for responsible community oversight.</p>
        </article>
        <article class="card hover-card">
            <h3>Data Clarity</h3>
            <p>Use donor search and month/year filters to quickly track contribution activity.</p>
        </article>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
