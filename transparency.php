<?php include 'includes/header.php'; ?>

<section class="py-5 mt-5">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-end mb-5 reveal">
            <div>
                <h1 class="display-4 fw-bold"> <span class="gradient-text"></span></h1>
                <p class="lead text-muted mb-0"></p>
            </div>
            <div>
                <a href="donate.php" class="btn btn-primary-custom px-4 py-3 rounded-pill shadow-premium fw-bold">
                    <i class="fas fa-heart me-2"></i> Join as a Donor
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5 reveal">
            <div class="col-md-4">
                <div class="card p-4 border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <h6 class="text-muted small text-uppercase mb-2 letter-spacing-1 fw-bold">Total Donation</h6>
                    <?php
$total_donations = $pdo->query("SELECT SUM(amount) FROM donations WHERE status = 'approved'")->fetchColumn() ?: 0;
?>
                    <h3 class="fw-bold text-primary mb-1">Rs.
                        <?php echo number_format($total_donations); ?>
                    </h3>
                    <p class="small text-muted mb-0">Total contributions received</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <h6 class="text-muted small text-uppercase mb-2 letter-spacing-1 fw-bold">Spend Donation</h6>
                    <?php
$total_spending = $pdo->query("SELECT SUM(amount) FROM spend_records")->fetchColumn() ?: 0;
?>
                    <h3 class="fw-bold text-danger mb-1">Rs.
                        <?php echo number_format($total_spending); ?>
                    </h3>
                    <p class="small text-muted mb-0">Funds utilized for welfare</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 border-0 shadow-sm h-100 border-start border-primary border-4"
                    style="border-radius: 20px;">
                    <h6 class="text-muted small text-uppercase mb-2 letter-spacing-1 fw-bold">Remaining Donation</h6>
                    <h3 class="fw-bold text-primary mb-1">Rs.
                        <?php echo number_format($total_donations - $total_spending); ?>
                    </h3>
                    <p class="small text-muted mb-0">Available balance for projects</p>
                </div>
            </div>
        </div>

        <div class="row reveal">
            <div class="col-12">
                <div class="card border-0 shadow-premium bg-white" style="border-radius: 24px; overflow: hidden;">
                    <div
                        class="card-header border-0 py-4 px-5 bg-transparent d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold">Recent Contributions</h4>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">Verified Only</span>
                    </div>
                    <div class="table-responsive px-5 pb-5">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-4 border-0">Donor Name</th>
                                    <th class="py-3 px-4 border-0">Amount</th>
                                    <th class="py-3 px-4 border-0">Date</th>
                                    <th class="py-3 px-4 border-0 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
$stmt = $pdo->query("SELECT d.*, u.name as donor_name FROM donations d LEFT JOIN users u ON d.user_id = u.id WHERE d.status = 'approved' ORDER BY d.donation_date DESC LIMIT 10");
$donations = $stmt->fetchAll();

if (empty($donations)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No public donations found.
                                        Become the first to contribute!</td>
                                </tr>
                                <?php
else: ?>
                                <?php foreach ($donations as $d): ?>
                                <tr>
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3"
                                                style="width: 35px; height: 35px;">
                                                <i class="fas fa-user text-muted small"></i>
                                            </div>
                                            <span class="fw-bold text-dark">
                                                <?php echo htmlspecialchars($d['donor_name'] ?: ($d['guest_name'] ?: 'Anonymous Donor')); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-primary fw-bold">Rs.
                                        <?php echo number_format($d['amount']); ?>
                                    </td>
                                    <td class="py-3 px-4 text-muted small">
                                        <?php echo date('M d, Y', strtotime($d['donation_date'])); ?>
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        <span
                                            class="badge rounded-pill bg-success-subtle text-success px-3 py-2 fw-medium">
                                            <i class="fas fa-check-circle me-1"></i> Verified
                                        </span>
                                    </td>
                                </tr>
                                <?php
    endforeach; ?>
                                <?php
endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const reveals = document.querySelectorAll('.reveal');
        const revealOnScroll = () => {
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) {
                    el.classList.add('active');
                }
            });
        };
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll();
    });
</script>

<style>
    .bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.1) !important;
        color: #198754 !important;
    }

    .bg-primary-subtle {
        background-color: rgba(0, 161, 255, 0.1) !important;
    }

    .letter-spacing-1 {
        letter-spacing: 1px;
    }
</style>

<?php include 'includes/footer.php'; ?>