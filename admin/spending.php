<?php
include 'auth_check.php';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $area = $_POST['area'];
            $amount = $_POST['amount'];
            $spend_date = $_POST['spend_date'];

            $attachment_path = '';
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
                $upload_dir = '../uploads/spending/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);
                $file_name = time() . '_' . basename($_FILES['attachment']['name']);
                $target_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
                    $attachment_path = 'uploads/spending/' . $file_name;
                }
            }

            if ($_POST['action'] === 'add') {
                $stmt = $pdo->prepare("INSERT INTO spend_records (title, description, area, amount, spend_date, attachment_path) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $area, $amount, $spend_date, $attachment_path]);
            }
            else {
                $id = $_POST['id'];
                if ($attachment_path) {
                    $stmt = $pdo->prepare("UPDATE spend_records SET title=?, description=?, area=?, amount=?, spend_date=?, attachment_path=? WHERE id=?");
                    $stmt->execute([$title, $description, $area, $amount, $spend_date, $attachment_path, $id]);
                }
                else {
                    $stmt = $pdo->prepare("UPDATE spend_records SET title=?, description=?, area=?, amount=?, spend_date=? WHERE id=?");
                    $stmt->execute([$title, $description, $area, $amount, $spend_date, $id]);
                }
            }
        }
        elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM spend_records WHERE id=?");
            $stmt->execute([$id]);
        }
        header("Location: spending.php?success=1");
        exit;
    }
}

$records = $pdo->query("SELECT * FROM spend_records ORDER BY spend_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Spending - KSWO Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #f8f9fa;
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

        .text-blue-main {
            color: #0056b3;
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
                    <a class="nav-link py-3" href="dashboard.php"><i class="fas fa-th-large me-2"></i> Dashboard</a>
                    <a class="nav-link py-3" href="members.php"><i class="fas fa-users me-2"></i> Members</a>
                    <a class="nav-link py-3" href="donations.php"><i class="fas fa-hand-holding-heart me-2"></i>
                        Donations</a>
                    <a class="nav-link py-3 active" href="spending.php"><i class="fas fa-file-invoice-dollar me-2"></i>
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
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h2 class="fw-bold mb-1">Manage <span class="gradient-text">Spending</span></h2>
                        <p class="text-muted">Track where donation funds are being utilized.</p>
                    </div>
                    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                        <i class="fas fa-plus me-2"></i> Add Record
                    </button>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4 bg-white h-100" style="border-radius: 20px;">
                            <h6 class="text-muted small text-uppercase fw-bold mb-2">Total Donations</h6>
                            <?php $total_donations = $pdo->query("SELECT SUM(amount) FROM donations WHERE status = 'approved'")->fetchColumn() ?: 0; ?>
                            <h3 class="fw-bold mb-1">Rs.
                                <?php echo number_format($total_donations); ?>
                            </h3>
                            <p class="text-muted small mb-0 mt-auto">Total collection</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4 bg-white h-100" style="border-radius: 20px;">
                            <h6 class="text-danger small text-uppercase fw-bold mb-2">Total Spent</h6>
                            <?php $total_spending = $pdo->query("SELECT SUM(amount) FROM spend_records")->fetchColumn() ?: 0; ?>
                            <h3 class="fw-bold mb-1 text-danger">Rs.
                                <?php echo number_format($total_spending); ?>
                            </h3>
                            <p class="text-muted small mb-0 mt-auto">Funds utilized</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4 bg-white h-100 border-start border-primary border-4"
                            style="border-radius: 20px;">
                            <h6 class="text-primary small text-uppercase fw-bold mb-2">Remaining Balance</h6>
                            <h3 class="fw-bold mb-1 text-primary">Rs.
                                <?php echo number_format($total_donations - $total_spending); ?>
                            </h3>
                            <p class="text-muted small mb-0 mt-auto">Available balance</p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-premium bg-white" style="border-radius: 20px;">
                    <div class="table-responsive p-4">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Title</th>
                                    <th class="border-0">Area</th>
                                    <th class="border-0">Amount</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No spending records found.</td>
                                </tr>
                                <?php
else: ?>
                                <?php foreach ($records as $r): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-blue-main">
                                            <?php echo htmlspecialchars($r['title']); ?>
                                        </div>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;">
                                            <?php echo htmlspecialchars($r['description']); ?>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($r['area']); ?>
                                        </span></td>
                                    <td><span class="fw-bold">Rs.
                                            <?php echo number_format($r['amount']); ?>
                                        </span></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($r['spend_date'])); ?>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1 edit-btn"
                                            data-id="<?php echo $r['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($r['title']); ?>"
                                            data-desc="<?php echo htmlspecialchars($r['description']); ?>"
                                            data-area="<?php echo htmlspecialchars($r['area']); ?>"
                                            data-amount="<?php echo $r['amount']; ?>"
                                            data-date="<?php echo $r['spend_date']; ?>">
                                            Edit
                                        </button>
                                        <form method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this record?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
                                        </form>
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

    <!-- Modal for Add/Edit -->
    <div class="modal fade" id="addRecordModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow" style="border-radius: 20px;">
                <form method="POST" enctype="multipart/form-data" id="recordForm">
                    <input type="hidden" name="action" value="add" id="formAction">
                    <input type="hidden" name="id" id="recordId">
                    <div class="modal-header border-0 p-4">
                        <h5 class="modal-title fw-bold" id="modalTitle">Add Spending Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-uppercase text-blue-main">Title /
                                    Purpose</label>
                                <input type="text" name="title" class="form-control" required
                                    style="border-radius: 10px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-blue-main">Area /
                                    Location</label>
                                <input type="text" name="area" class="form-control" required
                                    style="border-radius: 10px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-blue-main">Amount
                                    (Rs)</label>
                                <input type="number" name="amount" class="form-control" required
                                    style="border-radius: 10px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-blue-main">Date</label>
                                <input type="date" name="spend_date" class="form-control" required
                                    style="border-radius: 10px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-blue-main">Supporting
                                    Doc/Image</label>
                                <input type="file" name="attachment" class="form-control" style="border-radius: 10px;">
                            </div>
                            <div class="col-12">
                                <label
                                    class="form-label fw-bold small text-uppercase text-blue-main">Description</label>
                                <textarea name="description" class="form-control" rows="4"
                                    style="border-radius: 10px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-toggle="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom px-4">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = new bootstrap.Modal(document.getElementById('addRecordModal'));
                document.getElementById('modalTitle').innerText = 'Edit Spending Record';
                document.getElementById('formAction').value = 'edit';
                document.getElementById('recordId').value = btn.dataset.id;

                const form = document.getElementById('recordForm');
                form.querySelector('[name="title"]').value = btn.dataset.title;
                form.querySelector('[name="description"]').value = btn.dataset.desc;
                form.querySelector('[name="area"]').value = btn.dataset.area;
                form.querySelector('[name="amount"]').value = btn.dataset.amount;
                form.querySelector('[name="spend_date"]').value = btn.dataset.date;

                modal.show();
            });
        });

        document.getElementById('addRecordModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalTitle').innerText = 'Add Spending Record';
            document.getElementById('formAction').value = 'add';
            document.getElementById('recordId').value = '';
            document.getElementById('recordForm').reset();
        });
    </script>
</body>

</html>