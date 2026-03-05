<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('user');
$sessionUser = current_user();
$db = get_db_connection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $fullName = trim($_POST['full_name'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $dob = trim($_POST['dob'] ?? '');

    if ($fullName === '' || $fatherName === '' || $phone === '' || $email === '' || $cnic === '' || $dob === '') {
        $error = 'All profile fields are required.';
    } elseif (!cnic_valid($cnic)) {
        $error = 'CNIC must be in format 12345-1234567-1.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $checkStmt = $db->prepare('SELECT id FROM users WHERE (email = ? OR phone = ? OR cnic = ?) AND id != ? LIMIT 1');
        $userId = (int)$sessionUser['id'];
        $checkStmt->bind_param('sssi', $email, $phone, $cnic, $userId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->fetch_assoc()) {
            $error = 'Email, phone, or CNIC already belongs to another user.';
        } else {
            $stmt = $db->prepare('UPDATE users SET full_name=?, father_name=?, phone=?, email=?, cnic=?, dob=? WHERE id=?');
            $stmt->bind_param('ssssssi', $fullName, $fatherName, $phone, $email, $cnic, $dob, $userId);
            if ($stmt->execute()) {
                refresh_session_user($userId);
                $success = 'Profile updated successfully.';
            } else {
                $error = 'Could not update profile at this time.';
            }
        }
    }
}

$user = fetch_one(
    'SELECT full_name, father_name, phone, email, cnic, dob
     FROM users WHERE id = ?',
    'i',
    [(int)$sessionUser['id']]
);

$pageTitle = 'Profile';
$activePage = 'Profile';
$navType = 'user';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content form-wrap" style="max-width:none;">
    <section class="card">
        <h2>My Profile</h2>
        <?php if ($error): ?>
            <div class="notice" style="border-left-color:#c62828;background:#fef2f2;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="notice" style="border-left-color:#166534;background:#ecfdf3;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-grid">
                <div class="form-group"><label>Full Name</label><input name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"></div>
                <div class="form-group"><label>Father Name</label><input name="father_name" value="<?= htmlspecialchars($user['father_name'] ?? '') ?>"></div>
                <div class="form-group"><label>Phone</label><input name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"></div>
                <div class="form-group"><label>CNIC</label><input name="cnic" value="<?= htmlspecialchars($user['cnic'] ?? '') ?>"></div>
                <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" value="<?= htmlspecialchars($user['dob'] ?? '') ?>"></div>
            </div>
            <div style="margin-top:1rem;">
                <button class="btn btn-primary" type="submit">Update Profile</button>
            </div>
        </form>
    </section>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
