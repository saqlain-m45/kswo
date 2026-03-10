<?php
ob_start();
include 'includes/header.php';

if (!isLoggedIn()) {
    redirect('join.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect('join.php');
}
?>

<section class="py-5 mt-5">
    <div class="container mt-5">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-md-4 col-lg-3">
                <?php include 'includes/user_sidebar.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Action Cards & Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-12 reveal">
                        <div class="card border-0 shadow-premium p-4 h-100"
                            style="background: var(--primary-gradient); border-radius: 24px;">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="text-white fw-bold mb-3 text-white">Impact the Future</h4>
                                    <p class="text-white-50 mb-0 text-white opacity-75">Your contributions directly
                                        support the educational welfare of
                                        Khattak students.</p>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <a href="donate.php" class="btn btn-white py-3 px-5 fw-bold text-primary shadow-sm"
                                        style="border-radius: 12px; background: #fff;">Donate Now</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 reveal">
                        <div class="card border-0 shadow-sm p-4 bg-white h-100" style="border-radius: 20px;">
                            <h6 class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-2 text-blue-main">
                                Total Donations</h6>
                            <?php
$total_all = $pdo->query("SELECT SUM(amount) FROM donations WHERE status = 'approved'")->fetchColumn() ?: 0;
?>
                            <h3 class="fw-bold text-dark mb-1">Rs.
                                <?php echo number_format($total_all); ?>
                            </h3>
                            <p class="text-muted small mb-0 mt-auto">Total collection</p>
                        </div>
                    </div>

                    <div class="col-md-4 reveal">
                        <div class="card border-0 shadow-sm p-4 bg-white h-100" style="border-radius: 20px;">
                            <h6 class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-2 text-danger">Spent
                                Donation</h6>
                            <?php
$total_spent = $pdo->query("SELECT SUM(amount) FROM spend_records")->fetchColumn() ?: 0;
?>
                            <h3 class="fw-bold text-danger mb-1">Rs.
                                <?php echo number_format($total_spent); ?>
                            </h3>
                            <p class="text-muted small mb-0 mt-auto">Funds utilized</p>
                        </div>
                    </div>

                    <div class="col-md-4 reveal">
                        <div class="card border-0 shadow-sm p-4 bg-white h-100 border-start border-primary border-4"
                            style="border-radius: 20px;">
                            <h6 class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-2 text-primary">
                                Remaining</h6>
                            <h3 class="fw-bold text-primary mb-1">Rs.
                                <?php echo number_format($total_all - $total_spent); ?>
                            </h3>
                            <p class="text-muted small mb-0 mt-auto">Available balance</p>
                        </div>
                    </div>
                </div>

                <!-- Spending History (For Verified Members) -->
                <?php if ($user['status'] == 'verified'): ?>
                <div class="card border-0 shadow-premium bg-white reveal mb-4"
                    style="border-radius: 24px; overflow: hidden;">
                    <div
                        class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold text-blue-main">Donation Utilization</h4>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">Spend History</span>
                    </div>
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-3 border-0">Purpose</th>
                                    <th class="py-3 px-3 border-0">Area</th>
                                    <th class="py-3 px-3 border-0">Amount</th>
                                    <th class="py-3 px-3 border-0 text-end">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
    $stmt = $pdo->query("SELECT * FROM spend_records ORDER BY spend_date DESC LIMIT 10");
    $spending = $stmt->fetchAll();

    if (empty($spending)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No spending records available
                                        yet.</td>
                                </tr>
                                <?php
    else: ?>
                                <?php foreach ($spending as $s): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($s['title']); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($s['description']); ?>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($s['area']); ?>
                                        </span></td>
                                    <td><span class="fw-bold text-dark">Rs.
                                            <?php echo number_format($s['amount']); ?>
                                        </span></td>
                                    <td class="text-end text-muted small">
                                        <?php echo date('M d, Y', strtotime($s['spend_date'])); ?>
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
                <?php
endif; ?>

                <!-- History Section -->
                <div class="card border-0 shadow-premium bg-white reveal"
                    style="border-radius: 24px; overflow: hidden;">
                    <div
                        class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold">Recent Activities</h4>
                        <span class="badge bg-light text-dark px-3 py-2 border">Donation History</span>
                    </div>
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-3 border-0">Reference</th>
                                    <th class="py-3 px-3 border-0">Amount</th>
                                    <th class="py-3 px-3 border-0">Method</th>
                                    <th class="py-3 px-3 border-0">Date</th>
                                    <th class="py-3 px-3 border-0 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
$stmt = $pdo->prepare("SELECT * FROM donations WHERE user_id = ? ORDER BY donation_date DESC");
$stmt->execute([$user_id]);
$donations = $stmt->fetchAll();

if (empty($donations)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">You haven't made any donations
                                        yet.</td>
                                </tr>
                                <?php
else: ?>
                                <?php foreach ($donations as $d): ?>
                                <tr>
                                    <td><span class="small text-muted fw-bold">#
                                            <?php echo htmlspecialchars($d['reference_number'] ?: 'TBA'); ?>
                                        </span></td>
                                    <td><span class="fw-bold text-dark">Rs.
                                            <?php echo number_format($d['amount']); ?>
                                        </span></td>
                                    <td><span class="badge bg-light text-muted px-2 py-1 border">
                                            <?php echo $d['method']; ?>
                                        </span></td>
                                    <td><span class="small text-muted">
                                            <?php echo date('M d, Y', strtotime($d['donation_date'])); ?>
                                        </span></td>
                                    <td class="text-end">
                                        <span class="badge rounded-pill <?php
        echo $d['status'] == 'approved' ? 'bg-success-subtle text-success' : ($d['status'] == 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
?> px-3 py-2">
                                            <?php echo ucfirst($d['status']); ?>
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

    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1) !important;
        color: #ffc107 !important;
    }

    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1) !important;
        color: #dc3545 !important;
    }

    .letter-spacing-1 {
        letter-spacing: 1px;
    }
</style>

<?php
include 'includes/footer.php';
ob_end_flush();
?>