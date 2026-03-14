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
 * Following approximate DoTM Kathmandu Valley rates
 */
function calculateFare($km) {
    $dist = floatval($km) ?: 0;
    if ($dist <= 0) return 0;
    if ($dist <= 5) return 20;
    if ($dist <= 10) return 27;
    if ($dist <= 15) return 32;
    if ($dist <= 20) return 35;
    
    // For very long distances (e.g. Banepa/Panauti), add approx Rs 2 per KM after 20
    $extra = ceil($dist - 20) * 2;
    return 35 + $extra;
}

/**
 * Estimates travel time in minutes based on distance
 * Considers Kathmandu's average bus speed (~12 km/h) and traffic buffers
 */
function estimateTime($km) {
    $dist = floatval($km) ?: 0;
    if ($dist <= 0) return "0";
    
    // Average speed of 12km/h means 1km takes 5 minutes
    $travelTime = ceil($dist * 5);
    
    // Add traffic/stop buffer (approx 10 mins for short, 20+ for long)
    $buffer = ($dist > 15) ? 25 : 12;
    
    $minTime = $travelTime + ($buffer - 5);
    $maxTime = $travelTime + $buffer + 5;
    
    return $minTime . "-" . $maxTime;
}
?>
