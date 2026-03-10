<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

// Determine Page Title for Hero (if not already set)
$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($page_title) || empty($page_title)) {
    $page_title = "Welcome";
    switch ($current_page) {
        case 'about.php':
            $page_title = "About <span class='gradient-text'>Us</span>";
            break;
        case 'presidents.php':
            $page_title = "Past <span class='gradient-text'>Presidents</span>";
            break;
        case 'transparency.php':
            $page_title = "Transparency <span class='gradient-text'>Dashboard</span>";
            break;
        case 'donate.php':
            $page_title = "Make a <span class='gradient-text'>Difference</span>";
            break;
        case 'join.php':
            $page_title = "Join the <span class='gradient-text'>Community</span>";
            break;
        case 'dashboard.php':
            $page_title = "User <span class='gradient-text'>Dashboard</span>";
            break;
        case 'events.php':
            $page_title = "Our <span class='gradient-text'>Events</span>";
            break;
    }
}

if (!isset($page_subtitle) || empty($page_subtitle)) {
    $page_subtitle = "Khattak Student Welfare Organization";
    switch ($current_page) {
        case 'about.php':
            $page_subtitle = "Learning our history, mission, and vision.";
            break;
        case 'presidents.php':
            $page_subtitle = "Honoring the leaders who shaped our legacy.";
            break;
        case 'transparency.php':
            $page_subtitle = "Open records of every contribution made.";
            break;
        case 'donate.php':
            $page_subtitle = "Your contribution empowers student welfare.";
            break;
        case 'join.php':
            $page_subtitle = "Become a member or access your account.";
            break;
        case 'dashboard.php':
            $page_subtitle = "Manage your profile and contributions.";
            break;
        case 'events.php':
            $page_subtitle = "Stay updated with our latest activities and workshops.";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KSWO - Khattak Student Welfare Organization</title>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        header {
            border-bottom: none !important;
        }

        header:not(.scrolled) {
            background: transparent !important;
            box-shadow: none !important;
        }

        header:not(.scrolled) .nav-link,
        header:not(.scrolled) .navbar-brand {
            color: #fff !important;
        }

        header.scrolled {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body>

    <header id="main-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center position-relative" href="index.php"
                    style="min-width: 120px; height: 60px;">
                    <div class="logo-container shadow-premium" style="
                        width: 100px; 
                        height: 100px; 
                        
                        border-radius: 50%; 
                        position: absolute; 
                        top: 40%; 
                        transform: translateY(-50%); 
                        z-index: 1000;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <img src="assets/images/logo.png" alt="KSWO Logo"
                            style="width: 85%; height: auto; object-fit: contain;">
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="presidents.php">Presidents</a></li>
                        <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                        <li class="nav-item"><a class="nav-link" href="transparency.php">Transparency</a></li>

                        <?php if (isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link btn btn-primary-custom ms-lg-3 px-4 shadow-sm"
                                href="dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php"><i
                                    class="fas fa-sign-out-alt ms-1"></i></a></li>
                        <?php
else: ?>
                        <li class="nav-item"><a class="nav-link btn btn-primary-custom ms-lg-3 px-4 shadow-sm"
                                href="join.php">Join Now</a></li>
                        <?php
endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <?php if ($current_page !== 'index.php'): ?>
    <section class="page-header-hero reveal">
        <div class="container position-relative" style="z-index: 1;">
            <h1 class="display-4 fw-bold mb-3">
                <?php echo $page_title; ?>
            </h1>
            <p class="lead mb-0 mx-auto" style="max-width: 700px;">
                <?php echo $page_subtitle; ?>
            </p>
        </div>
    </section>
    <?php
endif; ?>

    <script>
        window.addEventListener('scroll', function () {
            const header = document.getElementById('main-header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');

    </script>