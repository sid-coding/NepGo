<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Cache-Control to prevent "Back" button showing logged-in state after logout
if (!headers_sent()) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
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


function calculateFare($km) {
    $dist = floatval($km) ?: 0;
    if ($dist <= 0) return 0;
    if ($dist <= 5) return 20;
    if ($dist <= 10) return 27;
    if ($dist <= 15) return 32;
    if ($dist <= 20) return 35;
    $extra = ceil($dist - 20) * 2;
    return 35 + $extra;
}

function estimateTime($km) {
    $dist = floatval($km) ?: 0;
    if ($dist <= 0) return "0";

    $travelTime = ceil($dist * 3);

    $buffer = ($dist > 15) ? 25 : 12;

    $minTime = $travelTime + ($buffer - 3);
    $maxTime = $travelTime + $buffer + 3;

    return $minTime . "-" . $maxTime;
}
?>
