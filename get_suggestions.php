<?php
// Including the database connection
require_once 'config.php';

// Getting the search text from the URL (e.g., ?q=ka)
$query = trim($_GET['q'] ?? '');
$suggestions = [];

// Only run the search if the user has typed at least 1 character
if (strlen($query) >= 1) {
    // This query finds unique stop names that match what the user is typing
    // I'm using LIMIT 8 to keep the suggestion list short
    $stmt = $pdo->prepare("
        SELECT DISTINCT stop_name 
        FROM stops 
        WHERE stop_name LIKE ? 
        LIMIT 8
    ");
    // Using % to find any stop name that contains the query text
    $stmt->execute(["%$query%"]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Telling the browser we are sending back a JSON list
header('Content-Type: application/json');
echo json_encode($suggestions);
?>
