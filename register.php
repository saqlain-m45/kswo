<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $ethnicity = trim($_POST['ethnicity'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $fatherName === '' || $gender === '' || $ethnicity === '' || $dob === '' || $phone === '' || $email === '') {
        $errors[] = 'Please fill all required fields.';
    }

    if (!cnic_valid($cnic)) {
        $errors[] = 'CNIC must be in format 12345-1234567-1.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $db = get_db_connection();
        if (!$db) {
            $errors[] = 'Database connection failed. Please create/import kswo_db first.';
        } else {
            $checkStmt = $db->prepare('SELECT id FROM users WHERE email = ? OR phone = ? OR cnic = ? LIMIT 1');
            $checkStmt->bind_param('sss', $email, $phone, $cnic);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();

            if ($existing) {
                $errors[] = 'User already exists with this email, phone, or CNIC.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $designation = 'Member';
                $stmt = $db->prepare('INSERT INTO users (full_name, father_name, gender, ethnicity, dob, cnic, phone, email, designation, password_hash, role, membership_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "user", "pending")');
                $stmt->bind_param('ssssssssss', $fullName, $fatherName, $gender, $ethnicity, $dob, $cnic, $phone, $email, $designation, $passwordHash);

                if ($stmt->execute()) {
                    $success = 'Registration submitted successfully. Please login after admin verification.';
                } else {
                    $errors[] = 'Unable to register right now. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Register';
$activePage = 'Register';
$navType = 'public';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container form-wrap">
    <section class="card">
        <h2>Member Registration</h2>
        <p>Fill your details for verification.</p>
        <?php if ($errors): ?>
            <div class="notice" style="border-left-color:#c62828;background:#fef2f2;">
                <?= htmlspecialchars(implode(' ', $errors)) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="notice" style="border-left-color:#166534;background:#ecfdf3;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <form id="registerForm" method="post" action="" novalidate>
            <div class="form-grid">
                <div class="form-group"><label for="fullName">Full Name</label><input id="fullName" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required></div>
                <div class="form-group"><label for="fatherName">Father Name</label><input id="fatherName" name="father_name" value="<?= htmlspecialchars($_POST['father_name'] ?? '') ?>" required></div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select</option>
                        <option <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                        <option <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                        <option <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="form-group"><label for="ethnicity">Ethnicity</label><input id="ethnicity" name="ethnicity" value="<?= htmlspecialchars($_POST['ethnicity'] ?? '') ?>" required></div>
                <div class="form-group"><label for="dob">Date of Birth</label><input type="date" id="dob" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required></div>
                <div class="form-group">
                    <label for="cnic">CNIC</label>
                    <input id="cnic" name="cnic" placeholder="12345-1234567-1" value="<?= htmlspecialchars($_POST['cnic'] ?? '') ?>" required>
                    <small class="field-error" id="cnicError"></small>
                </div>
                <div class="form-group"><label for="phone">Phone Number</label><input id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required></div>
                <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small class="password-meter" id="passwordStrength">Strength: -</small>
                    <small class="field-error" id="passwordError"></small>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required>
                    <small class="field-error" id="confirmPasswordError"></small>
                </div>
            </div>
            <div style="margin-top:1rem;display:flex;gap:.6rem;align-items:center;">
                <button class="btn btn-primary" type="submit">Submit Registration</button>
                <span class="badge badge-info">Mobile-friendly & inline validated</span>
            </div>
        </form>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
