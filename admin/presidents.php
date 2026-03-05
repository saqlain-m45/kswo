<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_access();
$db = get_db_connection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $action = $_POST['action'] ?? 'save';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $stmt = $db->prepare('DELETE FROM presidents WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $success = 'President removed successfully.';
        }
    } else {
        $name = trim($_POST['name'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $photoPath = trim($_POST['existing_photo'] ?? '');

        if ($name === '' || $duration === '' || $description === '') {
            $error = 'Name, duration, and description are required.';
        } else {
            if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
                $uploadDir = __DIR__ . '/../uploads/presidents/';
                $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($extension, $allowed, true)) {
                    $newName = 'president_' . time() . '_' . random_int(1000, 9999) . '.' . $extension;
                    $targetPath = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                        $photoPath = 'uploads/presidents/' . $newName;
                    }
                }
            }

            if (!$error) {
                if ($id > 0) {
                    $stmt = $db->prepare('UPDATE presidents SET name=?, duration=?, description=?, photo_path=?, sort_order=? WHERE id=?');
                    $stmt->bind_param('ssssii', $name, $duration, $description, $photoPath, $sortOrder, $id);
                    $success = $stmt->execute() ? 'President updated successfully.' : 'Failed to update president.';
                } else {
                    $stmt = $db->prepare('INSERT INTO presidents (name, duration, description, photo_path, sort_order) VALUES (?, ?, ?, ?, ?)');
                    $stmt->bind_param('ssssi', $name, $duration, $description, $photoPath, $sortOrder);
                    $success = $stmt->execute() ? 'President added successfully.' : 'Failed to add president.';
                }
            }
        }
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$editRow = null;
if ($editId > 0) {
    $editRow = fetch_one('SELECT id, name, duration, description, photo_path, sort_order FROM presidents WHERE id = ?', 'i', [$editId]);
}

$presidents = fetch_all('SELECT id, name, duration, description, photo_path, sort_order FROM presidents ORDER BY sort_order ASC, id DESC');

$pageTitle = 'Presidents';
$activePage = 'Presidents';
$navType = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container dashboard-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="dashboard-content form-wrap" style="max-width:none;">
    <h2 class="section-title">President Management</h2>
    <section class="card">
        <h3>Add / Edit President</h3>
        <?php if ($error): ?>
            <div class="notice" style="border-left-color:#c62828;background:#fef2f2;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="notice" style="border-left-color:#166534;background:#ecfdf3;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)($editRow['id'] ?? 0) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($editRow['photo_path'] ?? '') ?>">
            <div class="form-grid">
                <div class="form-group"><label>Name</label><input name="name" value="<?= htmlspecialchars($editRow['name'] ?? '') ?>" required></div>
                <div class="form-group"><label>Duration</label><input name="duration" placeholder="2024 - 2025" value="<?= htmlspecialchars($editRow['duration'] ?? '') ?>" required></div>
                <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)($editRow['sort_order'] ?? 0) ?>"></div>
                <div class="form-group"><label>Photo</label><input name="photo" type="file" accept="image/*"></div>
                <div class="form-group" style="grid-column:1/-1;"><label>Description</label><textarea name="description" rows="4"><?= htmlspecialchars($editRow['description'] ?? '') ?></textarea></div>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button class="btn btn-primary" type="submit">Save President</button>
                <a class="btn btn-muted" href="<?= page_url('admin/presidents.php') ?>">Reset</a>
            </div>
        </form>
    </section>

    <section class="card" style="margin-top:1rem;">
        <h3>Existing Records</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Duration</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (!$presidents): ?>
                        <tr><td colspan="4">No president records found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($presidents as $president): ?>
                        <tr>
                            <td><?= htmlspecialchars($president['name']) ?></td>
                            <td><?= htmlspecialchars($president['duration']) ?></td>
                            <td><?= htmlspecialchars($president['description']) ?></td>
                            <td>
                                <a class="btn btn-muted" href="<?= page_url('admin/presidents.php?edit=' . (int)$president['id']) ?>">Edit</a>
                                <form method="post" action="" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?= (int)$president['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="btn" style="background:#fee2e2;color:#991b1b;" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
