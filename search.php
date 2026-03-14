<?php
require_once 'config.php';

// Only get from URL, no session persistence as requested
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');
$routes = [];

if (!empty($from) && !empty($to)) {
    $stmt = $pdo->prepare("
        SELECT r.route_id, b.bus_name, b.bus_image,
               r.start_point, r.end_point, r.distance_km,
               GROUP_CONCAT(s.stop_name ORDER BY s.stop_order SEPARATOR ',') AS stops
        FROM routes r
        JOIN buses b ON r.bus_id = b.bus_id
        JOIN stops s ON r.route_id = s.route_id
        WHERE r.is_approved = 1 AND r.route_id IN (
            SELECT s1.route_id 
            FROM stops s1
            JOIN stops s2 ON s1.route_id = s2.route_id
            WHERE s1.stop_name LIKE ? 
              AND s2.stop_name LIKE ? 
              AND s2.stop_order > s1.stop_order
        )
        GROUP BY r.route_id
        ORDER BY r.distance_km ASC
    ");

    $stmt->execute(["%$from%", "%$to%"]);
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results – NepalGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.7">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="content-container">
    <div class="section-header">
        <h1>Search Routes</h1>
        <p>Find the best way to reach your destination.</p>
    </div>

    <div class="bus-search-area" style="margin-bottom: 3rem;">
        <form class="search-form-wrapper" action="search.php" method="GET">
            <div class="form-input-group">
                <input type="text" name="from" class="search-input-field" placeholder="Starting point" value="<?= htmlspecialchars($from) ?>" autocomplete="off" spellcheck="false" required>
            </div>
            <div class="form-input-group">
                <input type="text" name="to" class="search-input-field" placeholder="Destination" value="<?= htmlspecialchars($to) ?>" autocomplete="off" spellcheck="false" required>
            </div>
            <button type="submit" class="search-submit-button">Search</button>
        </form>
    </div>

    <?php if (!empty($from) && !empty($to)): ?>
        <main class="results-display-area" style="padding: 0;">
            <h2 style="font-size: 1.5rem; margin-bottom: 1rem;">Available Routes</h2>
            
            <?php if (count($routes) > 0): ?>
                <div class="results-list-grid">
                    <?php foreach ($routes as $index => $route): ?>
                        <?php 
                            $fare = calculateFare($route['distance_km']);
                            $time = estimateTime($route['distance_km']);
                            $stops = explode(',', $route['stops']);
                            $imgSrc = $route['bus_image'] ? "assets/images/{$route['bus_image']}" : 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&w=500&q=60';
                        ?>
                        <div class="bus-result-card">
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($route['bus_name']) ?>" class="bus-image-preview">
                            <div class="bus-card-details">
                                <div class="bus-card-header">
                                    <h3 class="bus-name-heading"><?= htmlspecialchars($route['bus_name']) ?></h3>
                                    <?php if ($index === 0): ?>
                                        <span class="recommendation-badge">Recommended</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="bus-info-list">
                                    <p><strong>From:</strong> <?= htmlspecialchars($route['start_point']) ?></p>
                                    <p><strong>To:</strong> <?= htmlspecialchars($route['end_point']) ?></p>
                                    <div style="margin: 10px 0; display: flex; gap: 8px;">
                                        <span class="price-tag">Rs. <?= $fare ?></span>
                                        <span class="time-tag"><?= $time ?> min</span>
                                    </div>
                                    <p><strong>Distance:</strong> <?= $route['distance_km'] ?> km</p>
                                </div>

                                <button class="toggle-stops-action" onclick="toggleStops(this)">
                                    Show <?= count($stops) ?> Stops
                                </button>

                                <div class="stops-dropdown-list">
                                    <?php foreach ($stops as $stop): ?>
                                        <div class="stop-name-item"><?= htmlspecialchars($stop) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; background: white; border: 1px solid #eee;">
                    <p>No routes found for this search.</p>
                </div>
            <?php endif; ?>
        </main>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
