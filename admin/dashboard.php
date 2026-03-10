<?php
include 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KSWO Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --admin-sidebar-bg: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background: var(--admin-sidebar-bg);
            border-right: 1px solid #eee;
        }

        .nav-link {
            border-radius: 12px;
            margin: 5px 15px;
            transition: all 0.3s;
            color: black;
            font-weight: 500;
        }

        .nav-link.active {
            background: var(--primary-gradient);
            color: #fff !important;
            box-shadow: 0 4px 15px rgba(0, 161, 255, 0.3);
        }

        .nav-link:hover:not(.active) {
            background: #eee;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 p-0 sidebar d-none d-md-block sticky-top">
                <div class="p-4 text-center">
                    <img src="../assets/images/logo.png" alt="KSWO Admin" style="height: 60px; width: auto;"
                        class="mb-2">
                    <p class="text-muted small">Control Center</p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link py-3 active" href="dashboard.php"><i class="fas fa-th-large me-2"></i>
                        Dashboard</a>
                    <a class="nav-link py-3" href="members.php"><i class="fas fa-users me-2"></i> Members</a>
                    <a class="nav-link py-3" href="donations.php"><i class="fas fa-hand-holding-heart me-2"></i>
                        Donations</a>
                    <a class="nav-link py-3" href="spending.php"><i class="fas fa-file-invoice-dollar me-2"></i>
                        Spending</a>
                    <a class="nav-link py-3" href="presidents.php"><i class="fas fa-user-tie me-2"></i> Presidents</a>
                    <a class="nav-link py-3" href="events.php"><i class="fas fa-calendar-alt me-2"></i> Events</a>

                    <div class="mt-5 px-3">
                        <a class="btn btn-outline-danger w-100 rounded-pill" href="../logout.php">Logout</a>
                    </div>

                    <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin'): ?>
                    <div class="mt-4 px-3">
                        <div class="small text-muted text-uppercase fw-bold mb-2 ps-2"
                            style="font-size: 0.65rem; letter-spacing: 1px;">System</div>
                        <a class="nav-link py-3 bg-primary bg-opacity-10 text-primary border-0"
                            href="../superadmin/dashboard.php">
                            <i class="fas fa-shield-alt me-2"></i> SuperAdmin
                        </a>
                    </div>
                    <?php
endif; ?>

                </nav>
            </div>

            <!-- Content -->
            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                <div class="mb-5 reveal">
                    <h2 class="fw-bold mb-1">Dashboard <span class="gradient-text">Overview</span></h2>
                    <p class="text-muted">Welcome back! Here's what's happening today.</p>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-3 reveal">
                        <div class="card border-0 shadow-premium p-4 bg-white h-100" style="border-radius: 20px;">
                            <h6 class="text-muted small text-uppercase fw-bold mb-3">Total Members</h6>
                            <?php $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?>
                            <h2 class="display-6 fw-bold mb-0">
                                <?php echo $count; ?>
                            </h2>
                            <i class="fas fa-users position-absolute end-0 bottom-0 m-4 text-light opacity-50 fs-1"></i>
                        </div>
                    </div>
                    <div class="col-md-3 reveal" style="transition-delay: 0.1s;">
                        <div class="card border-0 shadow-premium p-4 bg-white h-100" style="border-radius: 20px;">
                            <h6 class="text-success small text-uppercase fw-bold mb-3">Verified</h6>
                            <?php $count = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'verified'")->fetchColumn(); ?>
                            <h2 class="display-6 fw-bold mb-0 text-success">
                                <?php echo $count; ?>
                            </h2>
                            <i
                                class="fas fa-check-circle position-absolute end-0 bottom-0 m-4 text-success opacity-25 fs-1"></i>
                        </div>
                    </div>
                    <div class="col-md-3 reveal" style="transition-delay: 0.2s;">
                        <div class="card border-0 shadow-premium p-4 bg-white h-100" style="border-radius: 20px;">
                            <h6 class="text-warning small text-uppercase fw-bold mb-3">Pending</h6>
                            <?php $count = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn(); ?>
                            <h2 class="display-6 fw-bold mb-0 text-warning">
                                <?php echo $count; ?>
                            </h2>
                            <i
                                class="fas fa-clock position-absolute end-0 bottom-0 m-4 text-warning opacity-25 fs-1"></i>
                        </div>
                    </div>
                    <div class="col-md-3 reveal" style="transition-delay: 0.3s;">
                        <div class="card border-0 shadow-premium p-4 bg-white h-100 border-start border-primary border-4"
                            style="border-radius: 20px;">
                            <h6 class="text-primary small text-uppercase fw-bold mb-3">Available Funds</h6>
                            <?php
$total_donations = $pdo->query("SELECT SUM(amount) FROM donations WHERE status = 'approved'")->fetchColumn() ?: 0;
$total_spending = $pdo->query("SELECT SUM(amount) FROM spend_records")->fetchColumn() ?: 0;
$funds = $total_donations - $total_spending;
?>
                            <h2 class="display-6 fw-bold mb-0 text-primary">Rs.
                                <?php echo number_format($funds); ?>
                            </h2>
                            <i
                                class="fas fa-wallet position-absolute end-0 bottom-0 m-4 text-primary opacity-25 fs-1"></i>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6 reveal">
                        <div class="card border-0 shadow-premium bg-white h-100"
                            style="border-radius: 24px; overflow: hidden;">
                            <div
                                class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Pending Applications</h5>
                                <a href="members.php" class="btn btn-light btn-sm rounded-pill px-3">View All</a>
                            </div>
                            <div class="table-responsive px-4 pb-4">
                                <table class="table table-hover align-middle">
                                    <tbody>
                                        <?php
$stmt = $pdo->query("SELECT * FROM users WHERE status = 'pending' LIMIT 5");
if ($stmt->rowCount() == 0) {
    echo "<tr><td class='text-center py-4 text-muted'>No pending applications.</td></tr>";
}
while ($u = $stmt->fetch()) {
    echo "<tr>
                                            <td class='py-3 border-0'>
                                                <div class='fw-bold'>{$u['name']}</div>
                                                <div class='small text-muted'>{$u['email']}</div>
                                            </td>
                                            <td class='py-3 border-0 text-end'>
                                                <a href='members.php' class='btn btn-outline-primary btn-sm rounded-pill px-3'>Review</a>
                                            </td>
                                        </tr>";
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 reveal" style="transition-delay: 0.1s;">
                        <div class="card border-0 shadow-premium bg-white h-100"
                            style="border-radius: 24px; overflow: hidden;">
                            <div
                                class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Recent Contributions</h5>
                                <a href="donations.php" class="btn btn-light btn-sm rounded-pill px-3">View All</a>
                            </div>
                            <div class="table-responsive px-4 pb-4">
                                <table class="table table-hover align-middle">
                                    <tbody>
                                        <?php
$stmt = $pdo->query("SELECT d.*, u.name as donor_name FROM donations d LEFT JOIN users u ON d.user_id = u.id ORDER BY d.donation_date DESC LIMIT 5");
if ($stmt->rowCount() == 0) {
    echo "<tr><td class='text-center py-4 text-muted'>No donations recorded yet.</td></tr>";
}
while ($d = $stmt->fetch()) {
    $name = $d['donor_name'] ?: ($d['guest_name'] ?: 'Guest');
    echo "<tr>
                                            <td class='py-3 border-0'>
                                                <div class='fw-bold'>$name</div>
                                                <div class='small text-muted'>{$d['method']}</div>
                                            </td>
                                            <td class='py-3 border-0 text-end'>
                                                <div class='fw-bold text-primary'>Rs. " . number_format($d['amount']) . "</div>
                                                <div class='small text-muted'>" . date('M d', strtotime($d['donation_date'])) . "</div>
                                            </td>
                                        </tr>";
}
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
</body>

</html>