<?php
require_once 'config.php';

$buses = $pdo->query("
    SELECT b.bus_id, b.bus_name, b.bus_number, b.bus_image, 
           count(r.route_id) as route_count
    FROM buses b
    JOIN routes r ON b.bus_id = r.bus_id
    WHERE r.is_approved = 1
    GROUP BY b.bus_id, b.bus_name, b.bus_number, b.bus_image
    ORDER BY b.bus_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Fleet – NepalGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.7">
    <style>
        .results-list-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 1.5rem !important;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="content-container">
    <div class="section-header">
        <h1>Bus Fleet</h1>
        <p>Explore all buses and vehicles currently active in our network.</p>
    </div>

    <div class="results-list-grid">
        <?php foreach ($buses as $bus): ?>
            <?php 
                $imgSrc = $bus['bus_image'] ? "assets/images/{$bus['bus_image']}" : 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=500&q=60';
            ?>
            <div class="bus-result-card">
                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($bus['bus_name']) ?>" class="bus-image-preview">
                <div class="bus-card-details">
                    <h3 class="bus-name-heading"><?= htmlspecialchars($bus['bus_name']) ?></h3>
                    <p class="text-gray" style="font-size: 0.9rem; margin-bottom: 1rem;">Vehicle No: <?= htmlspecialchars($bus['bus_number']) ?></p>
                    <div class="bus-info-list">
                        <p><strong>Total Routes:</strong> <?= $bus['route_count'] ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
