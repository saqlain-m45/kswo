<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();
require_once 'includes/db.php';
$error = "";
$success = "";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Registration Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $active_tab = 'register';
    try {
        $name = sanitize($_POST['name']);
        $father_name = sanitize($_POST['father_name']);
        $gender = sanitize($_POST['gender']);
        $ethnicity = sanitize($_POST['ethnicity'] ?? '');
        $dob = sanitize($_POST['dob'] ?? '');
        if (empty($dob))
            $dob = null;
        $cnic = sanitize($_POST['cnic']);
        $phone = sanitize($_POST['phone']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($name) || empty($cnic) || empty($email) || empty($password)) {
            $error = "Please fill in all required fields.";
        }
        elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        }
        else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR cnic = ?");
            $stmt->execute([$email, $cnic]);
            if ($stmt->rowCount() > 0) {
                $error = "Email or CNIC already registered.";
            }
            else {
                $profile_pic_path = null;
                $cnic_pic_path = null;
                $student_card_pic_path = null;

                $upload_dir = 'uploads/profiles/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                // Handle Profile Pic
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                    $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                        $new_filename = uniqid('profile_', true) . '.' . $ext;
                        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $new_filename)) {
                            $profile_pic_path = $upload_dir . $new_filename;
                        }
                    }
                }

                // Handle CNIC Pic
                if (isset($_FILES['cnic_pic']) && $_FILES['cnic_pic']['error'] == 0) {
                    $ext = strtolower(pathinfo($_FILES['cnic_pic']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                        $new_filename = uniqid('cnic_', true) . '.' . $ext;
                        if (move_uploaded_file($_FILES['cnic_pic']['tmp_name'], $upload_dir . $new_filename)) {
                            $cnic_pic_path = $upload_dir . $new_filename;
                        }
                    }
                }

                // Handle Student Card Pic
                if (isset($_FILES['student_card_pic']) && $_FILES['student_card_pic']['error'] == 0) {
                    $ext = strtolower(pathinfo($_FILES['student_card_pic']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                        $new_filename = uniqid('card_', true) . '.' . $ext;
                        if (move_uploaded_file($_FILES['student_card_pic']['tmp_name'], $upload_dir . $new_filename)) {
                            $student_card_pic_path = $upload_dir . $new_filename;
                        }
                    }
                }

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, father_name, gender, ethnicity, dob, cnic, phone, email, password, profile_pic, cnic_pic, student_card_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $father_name, $gender, $ethnicity, $dob, $cnic, $phone, $email, $hashed_password, $profile_pic_path, $cnic_pic_path, $student_card_pic_path])) {
                    $success = "Registration successful! You can now login.";
                    $active_tab = 'login';
                }
                else {
                    $error = "Failed to create account. Please try again.";
                }
            }
        }
    }
    catch (Exception $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

// Login Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $active_tab = 'login';
    try {
        $identifier = sanitize($_POST['identifier']);
        $password = $_POST['password'];

        if (empty($identifier) || empty($password)) {
            $error = "Please fill in all fields.";
        }
        else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_status'] = $user['status'];
                $_SESSION['user_pic'] = $user['profile_pic'];
                redirect('dashboard.php');
            }
            else {
                $error = "Invalid email/phone or password.";
            }
        }
    }
    catch (Exception $e) {
        $error = "Login Error: " . $e->getMessage();
    }
}

$page_title = $active_tab == 'login' ? "Welcome Back" : "Join KSWO";
$page_subtitle = "Access your member portal or become a part of our growing community today.";

include 'includes/header.php';

?>

<section class="py-5" style="background: #fcfcfd;">
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <!-- Premium Tab Switcher -->
                <div class="text-center mb-5 reveal">
                    <div class="join-tabs-wrapper p-2 bg-white shadow-premium d-inline-flex"
                        style="border-radius: 30px; border: 1px solid rgba(0,0,0,0.05);">
                        <button
                            class="btn <?php echo $active_tab == 'login' ? 'btn-primary-custom shadow-premium' : 'btn-light text-muted'; ?> px-5 py-3 border-0"
                            onclick="switchTab('login')"
                            style="border-radius: 24px; font-weight: 700; transition: all 0.4s ease;">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                        <button
                            class="btn <?php echo $active_tab == 'register' ? 'btn-primary-custom shadow-premium' : 'btn-light text-muted'; ?> px-5 py-3 border-0"
                            onclick="switchTab('register')"
                            style="border-radius: 24px; font-weight: 700; transition: all 0.4s ease;">
                            <i class="fas fa-user-plus me-2"></i> Register
                        </button>
                    </div>
                </div>

                <div class="card border-0 shadow-premium bg-white reveal overflow-visible" style="border-radius: 35px;">
                    <div class="card-body p-4 p-md-5">

                        <!-- Login Form -->
                        <div id="login-form" style="display: <?php echo $active_tab == 'login' ? 'block' : 'none'; ?>;">
                            <div class="text-center mb-5">
                                <h2 class="fw-bold mb-2">Welcome <span class="gradient-text">Back</span></h2>
                                <p class="text-secondary small">Secure your future by accessing your member dashboard.
                                </p>
                            </div>

                            <?php if ($active_tab == 'login' && $error): ?>
                            <div
                                class="alert alert-danger border-0 rounded-4 p-3 mb-4 bg-danger bg-opacity-10 text-danger small text-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                            <?php
endif; ?>

                            <?php if ($active_tab == 'login' && $success): ?>
                            <div
                                class="alert alert-success border-0 rounded-4 p-3 mb-4 bg-success bg-opacity-10 text-success small text-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>
                            <?php
endif; ?>

                            <form action="join.php?tab=login" method="POST">
                                <div class="mb-4">
                                    <label
                                        class="form-label small fw-bold text-uppercase text-muted letter-spacing-1 ms-2 mb-2">Email
                                        or Phone</label>
                                    <div class="input-group-custom">
                                        <i class="fas fa-envelope icon"></i>
                                        <input type="text" name="identifier" class="form-control-premium"
                                            placeholder="Enter your identity" required>
                                    </div>
                                </div>
                                <div class="mb-5">
                                    <label
                                        class="form-label small fw-bold text-uppercase text-muted letter-spacing-1 ms-2 mb-2">Password</label>
                                    <div class="input-group-custom">
                                        <i class="fas fa-lock icon"></i>
                                        <input type="password" name="password" class="form-control-premium"
                                            placeholder="••••••••" required>
                                    </div>
                                </div>
                                <button type="submit" name="login"
                                    class="btn btn-primary-custom w-100 py-3 shadow-premium fw-bold rounded-4 mb-3">
                                    SIGN IN NOW <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                                <div class="text-center">
                                    <a href="#" class="text-decoration-none text-muted small hover-primary">Forgot your
                                        password?</a>
                                </div>
                            </form>
                        </div>

                        <!-- Register Form -->
                        <div id="register-form"
                            style="display: <?php echo $active_tab == 'register' ? 'block' : 'none'; ?>;">
                            <div class="text-center mb-5">
                                <h2 class="fw-bold mb-2">Join <span class="gradient-text">KSWO</span></h2>
                                <p class="text-secondary small">Start your journey today as a valued student
                                    organization member.</p>
                            </div>

                            <?php if ($active_tab == 'register' && $error): ?>
                            <div
                                class="alert alert-danger border-0 rounded-4 p-3 mb-4 bg-danger bg-opacity-10 text-danger small text-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                            <?php
endif; ?>

                            <form action="join.php?tab=register" method="POST" enctype="multipart/form-data">
                                <div class="row g-4">
                                    <div class="col-12 text-center mb-2">
                                        <div class="profile-upload-wrapper position-relative d-inline-block">
                                            <div id="profile-preview"
                                                class="rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 140px; height: 140px; border: 3px dashed var(--accent-color); background: #f8fafc; overflow: hidden; position: relative;">
                                                <div id="preview-placeholder" class="text-center">
                                                    <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                                                    <div class="small text-muted" style="font-size: 0.7rem;">TAP TO
                                                        UPLOAD</div>
                                                </div>
                                                <img id="preview-img" class="w-100 h-100 position-absolute"
                                                    style="display: none; object-fit: cover;">
                                            </div>
                                            <label for="profile_pic"
                                                class="btn btn-primary btn-sm rounded-circle position-absolute"
                                                style="bottom: 5px; right: 5px; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border: 3px solid #fff;">
                                                <i class="fas fa-plus"></i>
                                            </label>
                                            <input type="file" name="profile_pic" id="profile_pic" class="d-none"
                                                accept="image/*" onchange="previewImage(this)">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">Full
                                            Name</label>
                                        <input type="text" name="name" class="form-control-premium"
                                            placeholder="John Doe" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">Father's
                                            Name</label>
                                        <input type="text" name="father_name" class="form-control-premium"
                                            placeholder="Father Name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">Gender</label>
                                        <select name="gender" class="form-select form-control-premium" required>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">CNIC
                                            Number</label>
                                        <input type="text" name="cnic" class="form-control-premium"
                                            placeholder="00000-0000000-0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">CNIC
                                            Front Picture</label>
                                        <input type="file" name="cnic_pic" class="form-control-premium bg-white p-2"
                                            accept="image/*" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label
                                            class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">Student
                                            Card OR Last Semester Challan</label>
                                        <input type="file" name="student_card_pic"
                                            class="form-control-premium bg-white p-2" accept="image/*" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">Phone
                                            Number</label>
                                        <input type="text" name="phone" class="form-control-premium"
                                            placeholder="+92 3XX XXXXXXX" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">Email
                                            Address</label>
                                        <input type="email" name="email" class="form-control-premium"
                                            placeholder="name@example.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">Password</label>
                                        <input type="password" name="password" class="form-control-premium"
                                            placeholder="Create Password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="form-label small fw-bold text-uppercase text-muted ms-1 mb-2">Confirm</label>
                                        <input type="password" name="confirm_password" class="form-control-premium"
                                            placeholder="Repeat Password" required>
                                    </div>
                                </div>
                                <button type="submit" name="register"
                                    class="btn btn-primary-custom w-100 py-3 fw-bold rounded-4 shadow-premium mt-5">
                                    CREATE ACCOUNT <i class="fas fa-user-check ms-2"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .letter-spacing-1 {
        letter-spacing: 1px;
    }

    .hover-primary:hover {
        color: var(--accent-color) !important;
    }

    .input-group-custom {
        position: relative;
    }

    .input-group-custom .icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        z-index: 10;
        transition: color 0.3s ease;
    }

    .form-control-premium {
        display: block;
        width: 100%;
        padding: 15px 20px 15px 50px;
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-main);
        background-color: #f8fafc;
        border: 2px solid transparent;
        border-radius: 16px;
        transition: all 0.3s ease;
    }

    #register-form .form-control-premium {
        padding-left: 20px;
    }

    .form-control-premium:focus {
        background-color: #fff;
        border-color: var(--accent-color);
        box-shadow: 0 10px 25px rgba(0, 161, 255, 0.1);
        outline: none;
    }

    .input-group-custom input:focus+.icon {
        color: var(--accent-color);
    }

    select.form-control-premium {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23495057' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1.25rem center;
        background-size: 16px 12px;
    }
</style>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('preview-img').style.display = 'block';
                document.getElementById('preview-placeholder').style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function switchTab(tab) {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const buttons = document.querySelectorAll('.join-tabs-wrapper button');

        if (tab === 'login') {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            buttons[0].classList.remove('btn-light', 'text-muted');
            buttons[1].classList.add('btn-light', 'text-muted');
            buttons[1].classList.remove('btn-primary-custom', 'shadow-premium');
        } else {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            buttons[1].classList.add('btn-primary-custom', 'shadow-premium');
            buttons[1].classList.remove('btn-light', 'text-muted');
            buttons[0].classList.add('btn-light', 'text-muted');
            buttons[0].classList.remove('btn-primary-custom', 'shadow-premium');
        }

        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);
    }
</script>

<?php
include 'includes/footer.php';
ob_end_flush();
?>