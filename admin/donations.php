<?php
include 'auth_check.php';

if (isset($_POST['action'])) {
    $id = (int)$_POST['donation_id'];
    $status = $_POST['action'] == 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE donations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

$filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$query = "SELECT d.*, u.name as donor_name, u.email as donor_email FROM donations d LEFT JOIN users u ON d.user_id = u.id";
if ($filter != 'all') {
    $query .= " WHERE d.status = '$filter'";
}
$query .= " ORDER BY d.donation_date DESC";
$stmt = $pdo->query($query);
$donations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Management - KSWO Admin</title>
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

        .table-card {
            border-radius: 24px;
            border: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .badge-pending {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .badge-approved {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .badge-rejected {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .screenshot-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .screenshot-thumb:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body class="bg-light">

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 p-0 sidebar d-none d-md-block sticky-top">
                <div class="p-4 text-center">
                    <h4 class="fw-bold gradient-text">KSWO Admin</h4>
                    <p class="text-muted small">Control Center</p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link py-3" href="dashboard.php"><i class="fas fa-th-large me-2"></i> Dashboard</a>
                    <a class="nav-link py-3" href="members.php"><i class="fas fa-users me-2"></i> Members</a>
                    <a class="nav-link py-3 active" href="donations.php"><i class="fas fa-hand-holding-heart me-2"></i>
                        Donations</a>
                    <a class="nav-link py-3" href="spending.php"><i class="fas fa-file-invoice-dollar me-2"></i>
                        Spending</a>
                    <a class="nav-link py-3" href="presidents.php"><i class="fas fa-user-tie me-2"></i> Presidents</a>
                    <a class="nav-link py-3" href="events.php"><i class="fas fa-calendar-alt me-2"></i> Events</a>
                    <div class="mt-5 px-3">
                        <a class="btn btn-outline-danger w-100 rounded-pill" href="../logout.php">Logout</a>
                    </div>
                </nav>
            </div>

            <!-- Content -->
            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-5 reveal">
                    <div>
                        <h2 class="fw-bold mb-1">Donation <span class="gradient-text">Management</span></h2>
                        <p class="text-muted">Review and verify community contributions.</p>
                    </div>
                    <div class="btn-group shadow-sm rounded-pill p-1 bg-white">
                        <a href="?status=all"
                            class="btn rounded-pill <?php echo $filter == 'all' ? 'btn-primary-custom' : 'btn-light'; ?> btn-sm px-3">All</a>
                        <a href="?status=pending"
                            class="btn rounded-pill <?php echo $filter == 'pending' ? 'btn-primary-custom' : 'btn-light'; ?> btn-sm px-3">Pending</a>
                        <a href="?status=approved"
                            class="btn rounded-pill <?php echo $filter == 'approved' ? 'btn-primary-custom' : 'btn-light'; ?> btn-sm px-3">Approved</a>
                    </div>
                </div>

                <div class="card table-card bg-white reveal">
                    <div class="table-responsive p-4">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-3 py-3 rounded-start">Donor Info</th>
                                    <th class="border-0 px-3 py-3">Amount</th>
                                    <th class="border-0 px-3 py-3">Method / Ref</th>
                                    <th class="border-0 px-3 py-3">Receipt</th>
                                    <th class="border-0 px-3 py-3 text-center">Status</th>
                                    <th class="border-0 px-3 py-3 text-end rounded-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $d): ?>
                                <tr>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3"
                                                style="width: 40px; height: 40px;">
                                                <i
                                                    class="fas <?php echo $d['user_id'] ? 'fa-user text-primary' : 'fa-user-secret text-secondary'; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($d['donor_name'] ?: $d['guest_name']); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?php echo htmlspecialchars($d['donor_email'] ?: $d['guest_email']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <div class="fw-bold text-primary">Rs.
                                            <?php echo number_format($d['amount']); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo date('M d, Y', strtotime($d['donation_date'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-light text-dark border mb-1 px-2">
                                            <?php echo $d['method']; ?>
                                        </span>
                                        <div class="small text-muted font-monospace">
                                            <?php echo htmlspecialchars($d['reference_number'] ?: 'N/A'); ?>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <?php if ($d['screenshot']): ?>
                                        <a href="../<?php echo $d['screenshot']; ?>" target="_blank">
                                            <img src="../<?php echo $d['screenshot']; ?>"
                                                class="screenshot-thumb shadow-sm" alt="Receipt">
                                        </a>
                                        <?php
    else: ?>
                                        <span class="text-muted small">No receipt</span>
                                        <?php
    endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge rounded-pill <?php echo 'badge-' . $d['status']; ?> px-3 py-2">
                                            <?php echo ucfirst($d['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-3 text-end">
                                        <?php if ($d['status'] == 'pending'): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle"
                                                data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2"
                                                style="border-radius: 12px;">
                                                <li>
                                                    <form method="POST">
                                                        <input type="hidden" name="donation_id"
                                                            value="<?php echo $d['id']; ?>">
                                                        <button type="submit" name="action" value="approve"
                                                            class="dropdown-item text-success rounded-3 py-2">
                                                            <i class="fas fa-check-circle me-2"></i> Approve & Verify
                                                        </button>
                                                        <button type="submit" name="action" value="reject"
                                                            class="dropdown-item text-danger rounded-3 py-2">
                                                            <i class="fas fa-times-circle me-2"></i> Reject Donation
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                        <?php
    endif; ?>
                                    </td>
                                </tr>
                                <?php
endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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