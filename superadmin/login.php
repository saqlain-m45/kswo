<?php
ob_start();
session_start();
require_once __DIR__ . '/../includes/db.php';

$error = "";

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin') {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND role = 'superadmin'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        header("Location: dashboard.php");
        exit();
    }
    else {
        $error = "Invalid SuperAdmin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin Login - KSWO</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --super-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: var(--super-gradient);
            filter: blur(180px);
            opacity: 0.15;
            top: -300px;
            right: -100px;
            border-radius: 50%;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border-radius: 35px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            color: #fff;
        }

        .gradient-text-super {
            background: var(--super-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-control {
            border-radius: 15px;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            transition: all 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #2575fc;
            box-shadow: none;
            color: #fff;
        }

        .btn-super {
            background: var(--super-gradient);
            border: none;
            border-radius: 15px;
            padding: 16px;
            font-weight: 700;
            color: #fff;
            transition: all 0.3s;
            box-shadow: 0 10px 25px rgba(37, 117, 252, 0.3);
        }

        .btn-super:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(37, 117, 252, 0.4);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="login-card p-4 p-md-5">
        <div class="text-center mb-5">
            <div class="rounded-circle bg-white bg-opacity-10 d-inline-flex p-3 mb-3">
                <i class="fas fa-shield-alt fa-2x gradient-text-super"></i>
            </div>
            <h2 class="fw-bold mb-0">Super<span class="gradient-text-super">Admin</span></h2>
            <p class="text-white-50 small">System Authority Portal</p>
        </div>

        <?php if ($error): ?>
        <div
            class="alert alert-danger border-0 rounded-4 px-4 py-3 mb-4 bg-danger bg-opacity-10 text-danger small text-center">
            <i class="fas fa-lock me-2"></i>
            <?php echo $error; ?>
        </div>
        <?php
endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-4">
                <label
                    class="form-label small fw-bold text-uppercase text-white-50 letter-spacing-1 ms-2">Identity</label>
                <div class="position-relative">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
            </div>
            <div class="mb-5">
                <label class="form-label small fw-bold text-uppercase text-white-50 letter-spacing-1 ms-2">Security
                    Key</label>
                <div class="position-relative">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>
            <button type="submit" name="login" class="btn btn-super w-100 mb-4">
                AUTHENTICATE <i class="fas fa-fingerprint ms-2"></i>
            </button>
            <div class="text-center">
                <a href="../index.php" class="text-decoration-none text-white-50 small hover-white">
                    <i class="fas fa-external-link-alt me-1"></i> Back to KSWO Public
                </a>
            </div>
        </form>
    </div>

    <style>
        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .hover-white:hover {
            color: #fff !important;
        }
    </style>
</body>

</html>
<?php ob_end_flush(); ?>