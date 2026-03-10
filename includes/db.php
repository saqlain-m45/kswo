<?php
// Database configuration
$host = 'localhost';
$dbname = 'kswo_db';
$username = 'root';
$password = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Global functions
function sanitize($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($path)
{
    header("Location: $path");
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn()
{
    return isset($_SESSION['admin_id']);
}
?>
