<?php
include 'auth_check.php';

$success = "";
$error = "";

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Get image path to delete file
    $stmt = $pdo->prepare("SELECT image_path FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    if ($event && $event['image_path']) {
        @unlink("../" . $event['image_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Event record deleted.";
}

// Handle Addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $title = sanitize($_POST['title']);
    $event_date = sanitize($_POST['event_date']);
    $desc = sanitize($_POST['description']);

    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
        if ($_FILES['image']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9]/", "_", $title) . '.' . $ext;
                $target = "../assets/images/events/" . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $image_path = "assets/images/events/" . $filename;
                }
                else {
                    $error = "Critical: Could not move file. Check folder permissions.";
                }
            }
            else {
                $error = "Invalid file type. Please use JPG, PNG or WEBP.";
            }
        }
        else {
            $error = "Upload failed with error code: " . $_FILES['image']['error'];
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("INSERT INTO events (title, event_date, description, image_path) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $event_date, $desc, $image_path])) {
            $success = "Event created successfully!";
        }
        else {
            $error = "Database error: Could not save record.";
        }
    }
}

$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - KSWO Admin</title>
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

        .form-card {
            border-radius: 24px;
            border: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .table-card {
            border-radius: 24px;
            border: 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
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
                    <a class="nav-link py-3" href="members.php"><i class="fas fa-users me-2"></i> Members</a>
                    <a class="nav-link py-3" href="donations.php"><i class="fas fa-hand-holding-heart me-2"></i> Donations</a>
                    <a class="nav-link py-3" href="spending.php"><i class="fas fa-file-invoice-dollar me-2"></i> Spending</a>
                    <a class="nav-link py-3" href="presidents.php"><i class="fas fa-user-tie me-2"></i> Presidents</a>
                    <a class="nav-link py-3 active" href="events.php"><i class="fas fa-calendar-alt me-2"></i> Events</a>
                    <div class="mt-5 px-3">
                        <a class="btn btn-outline-danger w-100 rounded-pill" href="../logout.php">Logout</a>
                    </div>
                </nav>
            </div>

            <!-- Content -->
            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-5 reveal">
                    <div>
                        <h2 class="fw-bold mb-1">Event <span class="gradient-text">Management</span></h2>
                        <p class="text-muted">Manage organization events and activities.</p>
                    </div>
                    <div>
                        <button class="btn btn-primary-custom rounded-pill px-4 shadow-sm" data-bs-toggle="collapse"
                            data-bs-target="#addEventForm">
                            <i class="fas fa-plus me-2"></i> Add Event
                        </button>
                    </div>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success border-0 rounded-4 px-4 py-3 mb-4 shadow-sm reveal">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                </div>
                <?php
endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger border-0 rounded-4 px-4 py-3 mb-4 shadow-sm reveal">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
                <?php
endif; ?>

                <!-- Add Form -->
                <div class="collapse <?php echo $error ? 'show' : ''; ?> mb-5" id="addEventForm">
                    <div class="card form-card bg-white p-4 p-md-5 reveal">
                        <h5 class="mb-4 fw-bold">Enter Event Details</h5>
                        <form method="POST" enctype="multipart/form-data" class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label small text-muted text-uppercase fw-bold letter-spacing-1">Event Title</label>
                                <input type="text" name="title" class="form-control bg-light border-0 py-3" required style="border-radius: 12px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted text-uppercase fw-bold letter-spacing-1">Event Date</label>
                                <input type="date" name="event_date" class="form-control bg-light border-0 py-3" required style="border-radius: 12px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted text-uppercase fw-bold letter-spacing-1">Event Image</label>
                                <input type="file" name="image" class="form-control bg-light border-0 py-3" accept="image/*" style="border-radius: 12px;">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted text-uppercase fw-bold letter-spacing-1">Description</label>
                                <textarea name="description" class="form-control bg-light border-0 py-3" rows="3" required style="border-radius: 12px;"></textarea>
                            </div>
                            <div class="col-12 mt-4 text-end">
                                <button type="button" class="btn btn-light rounded-pill px-4 me-2" data-bs-toggle="collapse" data-bs-target="#addEventForm">Cancel</button>
                                <button type="submit" name="add_event" class="btn btn-primary-custom rounded-pill px-4 shadow-sm">Save Event</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List -->
                <div class="card table-card bg-white reveal">
                    <div class="table-responsive p-4">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-3 py-3 rounded-start">Event</th>
                                    <th class="border-0 px-3 py-3">Date</th>
                                    <th class="border-0 px-3 py-3">Description</th>
                                    <th class="border-0 px-3 py-3 text-end rounded-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $e): ?>
                                <tr>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center me-3 overflow-hidden"
                                                style="width: 50px; height: 50px;">
                                                <?php if ($e['image_path']): ?>
                                                <img src="../<?php echo htmlspecialchars($e['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                <?php
    else: ?>
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                                <?php
    endif; ?>
                                            </div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($e['title']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-light text-primary border px-3 py-2 rounded-pill">
                                            <?php echo date('M d, Y', strtotime($e['event_date'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-3">
                                        <div class="small text-muted text-truncate" style="max-width: 300px;">
                                            <?php echo htmlspecialchars($e['description']); ?>
                                        </div>
                                    </td>
                                    <td class="px-3 text-end">
                                        <a href="?delete=<?php echo $e['id']; ?>" class="btn btn-light btn-sm rounded-circle text-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
