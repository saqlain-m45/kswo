<?php
include 'auth_check.php';

if (isset($_POST['action'])) {
    $id = (int)$_POST['user_id'];
    $status = $_POST['action'] == 'approve' ? 'verified' : 'rejected';
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

$filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$query = "SELECT * FROM users";
if ($filter != 'all') {
    $query .= " WHERE status = '$filter'";
}
$query .= " ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Management - KSWO Admin</title>
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

        .badge-verified {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .badge-rejected {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
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
                    <a class="nav-link py-3 active" href="members.php"><i class="fas fa-users me-2"></i> Members</a>
                    <a class="nav-link py-3" href="donations.php"><i class="fas fa-hand-holding-heart me-2"></i>
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
                        <h2 class="fw-bold mb-1">Member <span class="gradient-text">Verifications</span></h2>
                        <p class="text-muted">Manage organization membership and access.</p>
                    </div>
                    <div class="btn-group shadow-sm rounded-pill p-1 bg-white">
                        <a href="?status=all"
                            class="btn rounded-pill <?php echo $filter == 'all' ? 'btn-primary-custom' : 'btn-light'; ?> btn-sm px-3">All</a>
                        <a href="?status=pending"
                            class="btn rounded-pill <?php echo $filter == 'pending' ? 'btn-primary-custom' : 'btn-light'; ?> btn-sm px-3">Pending</a>
                        <a href="?status=verified"
                            class="btn rounded-pill <?php echo $filter == 'verified' ? 'btn-primary-custom' : 'btn-light'; ?> btn-sm px-3">Verified</a>
                    </div>
                </div>

                <div class="card table-card bg-white reveal">
                    <div class="table-responsive p-4">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-3 py-3 rounded-start">Member Info</th>
                                    <th class="border-0 px-3 py-3">Identity / Contact</th>
                                    <th class="border-0 px-3 py-3 text-center">Status</th>
                                    <th class="border-0 px-3 py-3 text-end rounded-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm text-white"
                                                style="width: 45px; height: 45px; background: <?php echo $u['status'] == 'verified' ? 'var(--primary-gradient)' : '#ccc'; ?>; font-weight: 700;">
                                                <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($u['name']); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?php echo htmlspecialchars($u['email']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <div class="text-dark fw-medium"><i class="far fa-id-card me-2 text-muted"></i>
                                            <?php echo htmlspecialchars($u['cnic']); ?>
                                        </div>
                                        <div class="small text-muted"><i class="fas fa-phone me-2"></i>
                                            <?php echo htmlspecialchars($u['phone']); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge rounded-pill <?php echo 'badge-' . $u['status']; ?> px-3 py-2 mb-2 d-inline-block">
                                            <?php echo ucfirst($u['status']); ?>
                                        </span>
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <?php if (!empty($u['cnic_pic'])): ?>
                                            <button class="btn btn-outline-primary btn-sm rounded-pill"
                                                style="font-size: 0.7rem;" data-bs-toggle="modal"
                                                data-bs-target="#docModal"
                                                onclick="showDocument('../<?php echo htmlspecialchars($u['cnic_pic']); ?>', 'CNIC Front')">
                                                <i class="fas fa-id-card me-1"></i> View CNIC
                                            </button>
                                            <?php
    endif; ?>
                                            <?php if (!empty($u['student_card_pic'])): ?>
                                            <button class="btn btn-outline-info btn-sm rounded-pill"
                                                style="font-size: 0.7rem;" data-bs-toggle="modal"
                                                data-bs-target="#docModal"
                                                onclick="showDocument('../<?php echo htmlspecialchars($u['student_card_pic']); ?>', 'Student ID / Challan')">
                                                <i class="fas fa-graduation-cap me-1"></i> View ID Card
                                            </button>
                                            <?php
    endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-3 text-end align-top">
                                        <?php if ($u['status'] == 'pending'): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle"
                                                data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2"
                                                style="border-radius: 12px;">
                                                <li>
                                                    <form method="POST">
                                                        <input type="hidden" name="user_id"
                                                            value="<?php echo $u['id']; ?>">
                                                        <button type="submit" name="action" value="approve"
                                                            class="dropdown-item text-success rounded-3 py-2 font-weight-600">
                                                            <i class="fas fa-check-circle me-2"></i> Verify Member
                                                        </button>
                                                        <button type="submit" name="action" value="reject"
                                                            class="dropdown-item text-danger rounded-3 py-2 font-weight-600">
                                                            <i class="fas fa-times-circle me-2"></i> Reject Account
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

    <!-- Document Viewer Modal -->
    <div class="modal fade" id="docModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-premium"
                style="border-radius: 20px; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="docModalTitle">Document Viewer</h5>
                    <button type="button" class="btn-close bg-light rounded-circle p-2" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img id="docModalImage" src="" alt="Document" class="img-fluid rounded-3 shadow-sm"
                        style="max-height: 70vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDocument(imageSrc, title) {
            document.getElementById('docModalImage').src = imageSrc;
            document.getElementById('docModalTitle').innerText = title;
        }

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