<?php
ob_start();
session_start();
require_once __DIR__ . '/../includes/db.php';

$error = "";

if (isset($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        redirect('dashboard.php');
    }
    else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - KSWO</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #00a1ff 0%, #00ff8f 100%);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
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
            width: 500px;
            height: 500px;
            background: var(--primary-gradient);
            filter: blur(150px);
            opacity: 0.1;
            top: -200px;
            right: -100px;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: #007bff;
            filter: blur(150px);
            opacity: 0.1;
            bottom: -150px;
            left: -100px;
            border-radius: 50%;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }

        .gradient-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 20px;
            background: #f8f9fa;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .form-control:focus {
            background: #fff;
            border-color: #00a1ff;
            box-shadow: none;
        }

        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            color: #fff;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(0, 161, 255, 0.2);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 161, 255, 0.3);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="login-card p-4 p-md-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold gradient-text mb-0">KSWO Admin</h2>
            <p class="text-muted small">Restricted Management Access</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-4 px-4 py-3 mb-4 shadow-sm small text-center">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $error; ?>
        </div>
        <?php
endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-4">
                <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1 ms-2">Username</label>
                <div class="position-relative">
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                    <i class="fas fa-user position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>
            </div>
            <div class="mb-5">
                <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1 ms-2">Password</label>
                <div class="position-relative">
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    <i class="fas fa-lock position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>
            </div>
            <button type="submit" name="login" class="btn btn-login w-100 mb-4">
                Verify & Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
            <div class="text-center">
                <a href="../index.php" class="text-decoration-none text-muted small hover-primary">
                    <i class="fas fa-home me-1"></i> Return to Homepage
                </a>
            </div>
        </form>
    </div>

    <style>
        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .hover-primary:hover {
            color: #00a1ff !important;
        }
    </style>
</body>

</html>
<?php ob_end_flush(); ?>