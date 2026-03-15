<?php
// Starting the session so the website can remember the user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// My local database connection details
$DB_HOST = '127.0.0.1';
$DB_NAME = 'nepalgo';
$DB_USER = 'root';
$DB_PASS = '';

try {
    // I'm using PDO because it's safer and better for security
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    // Setting it to show errors if something goes wrong with the database
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If it doesn't connect, show the error and stop the page
    die("Database connection failed: " . $e->getMessage());
}


// This function calculates the bus fare based on the distance in KM.
// I'm using the standard rates for the Kathmandu Valley area.
function calculateFare($km) {
    $dist = floatval($km) ?: 0;
    if ($dist <= 0) return 0;
    
    // For short distances up to 5km, it's 20 rupees
    if ($dist <= 5) return 20;
    // Rates increase as the distance gets longer
    if ($dist <= 10) return 27;
    if ($dist <= 15) return 32;
    if ($dist <= 20) return 35;
    
    // For very long trips (like Banepa or Panauti), I'm adding 2 rupees for every extra KM after 20km
    $extra = ceil($dist - 20) * 2;
    return 35 + $extra;
}

// This function estimates the travel time in minutes.
// I'm considering Kathmandu's traffic and the average speed of public buses.
function estimateTime($km) {
    $dist = floatval($km) ?: 0;
    if ($dist <= 0) return "0";
    
    // I'm assuming the bus moves at around 12km/h, so 1km takes about 5 minutes
    $travelTime = ceil($dist * 5);
    
    // Adding extra time because of traffic jams and passengers getting on/off
    $buffer = ($dist > 15) ? 25 : 12;
    
    $minTime = $travelTime + ($buffer - 5);
    $maxTime = $travelTime + $buffer + 5;
    
    return $minTime . "-" . $maxTime;
}
?>
