<?php
require_once 'config.php';

$query = trim($_GET['q'] ?? '');
$suggestions = [];

if (strlen($query) >= 1) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT stop_name 
        FROM stops 
        WHERE stop_name LIKE ? 
        LIMIT 8
    ");
    $stmt->execute(["%$query%"]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

header('Content-Type: application/json');
echo json_encode($suggestions);
?>
