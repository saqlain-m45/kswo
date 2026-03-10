<?php
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isSuperAdmin()
{
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin';
}

if (!isSuperAdmin()) {
    header("Location: login.php");
    exit();
}
?>