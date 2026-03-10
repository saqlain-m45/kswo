<?php
include 'auth_check.php';

$success = "";
$error = "";

// Handle Admin Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_admin'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    }
    else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                $success = "Administrator created successfully.";
            }
        }
        catch (PDOException $e) {
            $error = "Username or Email already exists.";
        }
    }
}

// Handle Admin Deletion
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    if ($did != $_SESSION['admin_id']) { // Can't delete yourself
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$did]);
        $success = "Administrator removed.";
    }
}

$stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin Dashboard - KSWO</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --super-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            --dark-bg: #0f172a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--dark-bg);
            color: #fff;
            min-height: 100vh;
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.03);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            min-height: 100vh;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.6);
            border-radius: 12px;
            margin: 5px 15px;
            padding: 12px 20px;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .nav-link.active {
            background: var(--super-gradient);
            box-shadow: 0 10px 20px rgba(37, 117, 252, 0.2);
        }

        .card-custom {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            backdrop-filter: blur(10px);
        }

        .gradient-text {
            background: var(--super-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .table {
            color: #fff;
        }

        .table thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }

        .table tbody td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 12px;
            padding: 12px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #2575fc;
            color: #fff;
            box-shadow: none;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 p-0 sidebar d-none d-md-block sticky-top">
                <div class="p-4 text-center">
                    <h4 class="fw-bold mb-0">KSWO <span class="gradient-text">Super</span></h4>
                    <p class="text-white-50 small mt-1">System Master Control</p>
                </div>
                <nav class="nav flex-column mt-4">
                    <a class="nav-link active" href="dashboard.php"><i class="fas fa-user-shield me-2"></i> Admins</a>
                    <a class="nav-link" href="../admin/dashboard.php"><i class="fas fa-chart-line me-2"></i> Main
                        Admin</a>
                    <a class="nav-link" href="../index.php"><i class="fas fa-home me-2"></i> Website</a>
                    <div class="mt-5 px-3">
                        <a class="btn btn-outline-danger w-100 rounded-pill" href="../logout.php">Logout</a>
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h2 class="fw-bold mb-1">Administrator <span class="gradient-text">Management</span></h2>
                        <p class="text-white-50">Authorized control of system operators and roles.</p>
                    </div>
                    <button class="btn btn-primary bg-gradient rounded-pill px-4 shadow-sm"
                        style="background: var(--super-gradient); border: 0;" data-bs-toggle="modal"
                        data-bs-target="#createAdminModal">
                        <i class="fas fa-plus me-2"></i> New Admin
                    </button>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success rounded-4 p-3 mb-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                </div>
                <?php
endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger rounded-4 p-3 mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                </div>
                <?php
endif; ?>

                <div class="card card-custom p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Admin User</th>
                                    <th class="px-4 py-3">Privileges</th>
                                    <th class="px-4 py-3">Joined Date</th>
                                    <th class="px-4 py-3 text-end">Security</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $a): ?>
                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center me-3"
                                                style="width: 45px; height: 45px;">
                                                <i class="fas fa-user-tie text-white-50"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($a['username']); ?>
                                                </div>
                                                <div class="small text-white-50">
                                                    <?php echo htmlspecialchars($a['email']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4">
                                        <span
                                            class="badge rounded-pill <?php echo $a['role'] == 'superadmin' ? 'bg-primary' : 'bg-secondary'; ?> px-3">
                                            <?php echo strtoupper($a['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 text-white-50 small">
                                        <?php echo date('M d, Y', strtotime($a['created_at'])); ?>
                                    </td>
                                    <td class="px-4 text-end">
                                        <?php if ($a['id'] != $_SESSION['admin_id']): ?>
                                        <a href="?delete=<?php echo $a['id']; ?>"
                                            class="btn btn-sm btn-outline-danger rounded-circle"
                                            onclick="return confirm('Revoke this admin access?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        <?php
    else: ?>
                                        <span class="badge bg-white bg-opacity-10 text-white-50">Current Session</span>
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

    <!-- Create Admin Modal -->
    <div class="modal fade" id="createAdminModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content card-custom border-0" style="background: #1e293b;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Create New Administrator</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Temporary Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small text-white-50">User Role</label>
                            <select name="role" class="form-select form-control">
                                <option value="admin">Standard Admin</option>
                                <option value="superadmin">Super Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_admin" class="btn btn-primary rounded-pill px-4"
                            style="background: var(--super-gradient); border: 0;">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>