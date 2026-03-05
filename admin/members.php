<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_access();
$db = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $fullName = trim($_POST['full_name'] ?? '');
        $fatherName = trim($_POST['father_name'] ?? 'System');
        $gender = trim($_POST['gender'] ?? 'Other');
        $ethnicity = trim($_POST['ethnicity'] ?? 'Khattak');
        $dob = trim($_POST['dob'] ?? '2000-01-01');
        $cnic = trim($_POST['cnic'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $designation = trim($_POST['designation'] ?? 'Member');
        $password = $_POST['password'] ?? '';
        $requestedRole = trim($_POST['role'] ?? 'user');
        $roleToCreate = is_super_admin_user() && in_array($requestedRole, ['user', 'admin'], true) ? $requestedRole : 'user';

        if ($fullName !== '' && $cnic !== '' && $phone !== '' && $email !== '' && strlen($password) >= 8 && cnic_valid($cnic) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $checkStmt = $db->prepare('SELECT id FROM users WHERE email = ? OR phone = ? OR cnic = ? LIMIT 1');
            $checkStmt->bind_param('sss', $email, $phone, $cnic);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->fetch_assoc();

            if (!$exists) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $db->prepare('INSERT INTO users (full_name, father_name, gender, ethnicity, dob, cnic, phone, email, designation, password_hash, role, membership_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "verified")');
                $insertStmt->bind_param('sssssssssss', $fullName, $fatherName, $gender, $ethnicity, $dob, $cnic, $phone, $email, $designation, $passwordHash, $roleToCreate);
                $insertStmt->execute();
            }
        }
    }

    $memberId = (int)($_POST['member_id'] ?? 0);

    if ($memberId > 0 && in_array($action, ['approve', 'reject'], true)) {
        $newStatus = $action === 'approve' ? 'verified' : 'rejected';
        $stmt = $db->prepare('UPDATE users SET membership_status = ? WHERE id = ?');
        $stmt->bind_param('si', $newStatus, $memberId);
        $stmt->execute();
    }
}

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT id, full_name, designation, cnic, phone, membership_status FROM users WHERE role = "user"';
$types = '';
$params = [];

if (in_array($statusFilter, ['verified', 'pending', 'rejected'], true)) {
    $sql .= ' AND membership_status = ?';
    $types .= 's';
    $params[] = $statusFilter;
}

if ($search !== '') {
    $sql .= ' AND (full_name LIKE ? OR cnic LIKE ?)';
    $types .= 'ss';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$sql .= ' ORDER BY id DESC';
$members = fetch_all($sql, $types, $params);

$pageTitle = 'Members';
$activePage = 'Members';
$navType = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content">
    <section class="card" style="margin-bottom:1rem;">
        <h3>Create User / Office Holder</h3>
        <form method="post" action="" class="form-grid">
            <input type="hidden" name="action" value="create_user">
            <div class="form-group"><label>Full Name</label><input name="full_name" required></div>
            <div class="form-group"><label>Designation</label><input name="designation" placeholder="Deputy President" required></div>
            <div class="form-group"><label>CNIC</label><input name="cnic" placeholder="12345-1234567-1" required></div>
            <div class="form-group"><label>Phone</label><input name="phone" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" minlength="8" required></div>
            <?php if (is_super_admin_user()): ?>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            <?php endif; ?>
            <div class="form-group"><label>Gender</label><select name="gender"><option value="Male">Male</option><option value="Female">Female</option><option value="Other" selected>Other</option></select></div>
            <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" value="2000-01-01"></div>
            <div class="form-group"><label>Father Name</label><input name="father_name" value="System"></div>
            <div class="form-group"><label>Ethnicity</label><input name="ethnicity" value="Khattak"></div>
            <div style="grid-column:1/-1;"><button class="btn btn-primary" type="submit">Create User</button></div>
        </form>
    </section>

    <h2 class="section-title">Member Verification</h2>
    <form class="filters" method="get" action="">
        <select name="status">
            <option value="">All Status</option>
            <option value="verified" <?= $statusFilter === 'verified' ? 'selected' : '' ?>>Verified</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or CNIC">
        <button class="btn btn-primary" type="submit">Filter</button>
    </form>

    <div class="table-wrap card">
        <table>
            <thead><tr><th>Name</th><th>Designation</th><th>CNIC</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (!$members): ?>
                    <tr><td colspan="6">No members found.</td></tr>
                <?php endif; ?>
                <?php foreach ($members as $member): ?>
                    <?php
                        $badgeClass = $member['membership_status'] === 'verified' ? 'badge-success' : ($member['membership_status'] === 'pending' ? 'badge-warning' : 'badge-danger');
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($member['full_name']) ?></td>
                        <td><?= htmlspecialchars($member['designation'] ?? 'Member') ?></td>
                        <td><?= htmlspecialchars($member['cnic']) ?></td>
                        <td><?= htmlspecialchars($member['phone']) ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($member['membership_status'])) ?></span></td>
                        <td>
                            <button class="btn btn-muted member-detail-btn" data-name="<?= htmlspecialchars($member['full_name']) ?>" data-cnic="<?= htmlspecialchars($member['cnic']) ?>" data-phone="<?= htmlspecialchars($member['phone']) ?>" data-status="<?= ucfirst(htmlspecialchars($member['membership_status'])) ?>">View</button>
                            <form method="post" action="" style="display:inline-block;">
                                <input type="hidden" name="member_id" value="<?= (int)$member['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button class="btn btn-secondary" type="submit">Approve</button>
                            </form>
                            <form method="post" action="" style="display:inline-block;">
                                <input type="hidden" name="member_id" value="<?= (int)$member['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button class="btn" style="background:#fee2e2;color:#991b1b;" type="submit">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </section>
</div>

<div class="modal" id="memberModal">
    <div class="modal-content">
        <h3>Member Details</h3>
        <p><strong>Name:</strong> <span id="modalName"></span></p>
        <p><strong>CNIC:</strong> <span id="modalCnic"></span></p>
        <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <div style="display:flex;gap:.5rem;">
            <button class="btn btn-secondary">Approve</button>
            <button class="btn" style="background:#fee2e2;color:#991b1b;">Reject</button>
            <button class="btn btn-muted close-modal">Close</button>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
