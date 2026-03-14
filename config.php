<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = '127.0.0.1';
$DB_NAME = 'nepalgo';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/* ===============================================
   SHARED HELPER FUNCTIONS
=============================================== */

/**
 * Calculates estimated fare based on distance (KM)
 */
function calculateFare($km) {
    $dist = floatval($km) ?: 0;
    if ($dist <= 5) return 20;
    if ($dist <= 10) return 25;
    if ($dist <= 15) return 30;
    return 40;
}

/**
 * Estimates travel time in minutes based on distance
 */
function estimateTime($km) {
    $dist = floatval($km) ?: 0;
    $baseMins = ceil(($dist / 15) * 60);
    return $baseMins . "-" . ($baseMins + 10);
}
?>
