<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msgType = '';
$currentUser = $_SESSION['username'];

// Form Submission Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bus_id      = $_POST['bus_id'] ?? ''; 
    $bus_number  = strtoupper(trim($_POST['bus_number'] ?? '')); 
    $bus_name    = trim($_POST['bus_name'] ?? '');
    $route_name  = trim($_POST['route_name'] ?? '');
    $start_point = trim($_POST['start_point'] ?? '');
    $end_point   = trim($_POST['end_point'] ?? '');
    $stops_raw   = trim($_POST['stops'] ?? '');
    $distance    = floatval($_POST['distance'] ?? 0);

    if (($bus_id === '' && $bus_number === '') || $start_point === '' || $end_point === '' || $stops_raw === '') {
        $msg = "Please fill all required fields.";
        $msgType = "danger";
    } else {
        try {
            $pdo->beginTransaction();

            if ($bus_id === 'new') {
                $stmt = $pdo->prepare("SELECT bus_id FROM buses WHERE bus_number = ?");
                $stmt->execute([$bus_number]);
                $existing_bus_id = $stmt->fetchColumn();

                if ($existing_bus_id) {
                    $bus_id = $existing_bus_id;
                } else {
                    $stmt = $pdo->prepare("INSERT INTO buses (bus_number, bus_name) VALUES (?, ?)");
                    $stmt->execute([$bus_number, $bus_name]);
                    $bus_id = $pdo->lastInsertId();
                }
            }

            // Regular user submissions are NEVER approved by default
            $stmt = $pdo->prepare("INSERT INTO routes (bus_id, route_name, start_point, end_point, distance_km, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$bus_id, $route_name, $start_point, $end_point, $distance, 0]);
            $route_id = $pdo->lastInsertId();

            $stops = array_filter(array_map('trim', explode(',', $stops_raw)));
            
            // CRITICAL: Ensure start and end points are in the stops list for searching
            if (!in_array($start_point, $stops)) array_unshift($stops, $start_point);
            if (!in_array($end_point, $stops)) $stops[] = $end_point;

            $stmt = $pdo->prepare("INSERT INTO stops (route_id, stop_name, stop_order) VALUES (?, ?, ?)");
            $order = 1;
            foreach ($stops as $stop) {
                if ($stop !== '') {
                    $stmt->execute([$route_id, $stop, $order++]);
                }
            }

            $pdo->commit();
            $msg = "Route submitted! It will be live after admin approval.";
            $msgType = "success";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Error: " . $e->getMessage();
            $msgType = "danger";
        }
    }
}

$allBuses = $pdo->query("SELECT * FROM buses ORDER BY bus_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$mySubmissions = $pdo->prepare("
    SELECT b.bus_number, b.bus_name, r.route_name, r.start_point, r.end_point, r.is_approved
    FROM routes r
    JOIN buses b ON r.bus_id = b.bus_id
    ORDER BY r.route_id DESC
");
$mySubmissions->execute();
$routesList = $mySubmissions->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – NepalGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.7">
    <script>
        function toggleNewBusFields() {
            const select = document.getElementById('bus_id');
            const newBusSection = document.getElementById('new_bus_fields');
            if (select.value === 'new') {
                newBusSection.style.display = 'block';
            } else {
                newBusSection.style.display = 'none';
            }
        }
    </script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="content-container">
    <div class="section-header">
        <h1>Contributor Dashboard</h1>
        <p>Logged in as: <strong><?= htmlspecialchars($currentUser) ?></strong></p>
        <div style="margin-top: 1rem;">
            <a href="logout.php" class="action-btn" style="background-color: #EF4444; color: white; border-radius: 4px;">Logout</a>
        </div>
    </div>

    <?php if ($msg !== ''): ?>
        <div class="status-alert alert-<?= $msgType ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="management-area-box" style="margin-bottom: 4rem;">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem;">Suggest a New Route</h2>
        <form method="POST">
            <div class="form-field-group">
                <label>Select Bus</label>
                <select name="bus_id" id="bus_id" class="form-input-control" onchange="toggleNewBusFields()" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($allBuses as $bus): ?>
                        <option value="<?= $bus['bus_id'] ?>"><?= htmlspecialchars($bus['bus_name']) ?> (<?= htmlspecialchars($bus['bus_number']) ?>)</option>
                    <?php endforeach; ?>
                    <option value="new">+ Register New Bus</option>
                </select>
            </div>

            <div id="new_bus_fields" style="display: none; background: #f9fafb; padding: 1.5rem; border-radius: 4px; border: 1px dashed #ccc; margin-bottom: 1.5rem;">
                <div class="admin-form-grid">
                    <div class="form-field-group">
                        <label>Bus Number</label>
                        <input type="text" name="bus_number" class="form-input-control" placeholder="BA 2 KHA 1234">
                    </div>
                    <div class="form-field-group">
                        <label>Bus Name</label>
                        <input type="text" name="bus_name" class="form-input-control" placeholder="Mahanagar Yatayat">
                    </div>
                </div>
            </div>

            <div class="form-field-group">
                <label>Route Name (Optional)</label>
                <input type="text" name="route_name" class="form-input-control" placeholder="e.g. Ring Road">
            </div>

            <div class="admin-form-grid">
                <div class="form-field-group">
                    <label>Starting Point</label>
                    <input type="text" name="start_point" class="form-input-control" placeholder="Kalanki" required>
                </div>
                <div class="form-field-group">
                    <label>Destination</label>
                    <input type="text" name="end_point" class="form-input-control" placeholder="Ratnapark" required>
                </div>
                <div class="form-field-group">
                    <label>Distance (KM)</label>
                    <input type="number" step="0.1" name="distance" class="form-input-control" placeholder="12.5" required>
                </div>
            </div>

            <div class="form-field-group">
                <label>List of Stops (Comma Separated)</label>
                <textarea name="stops" class="form-input-control" rows="3" placeholder="Stop 1, Stop 2, Stop 3..." required></textarea>
            </div>

            <button type="submit" class="action-btn primary-btn" style="width: 100%; padding: 1rem; margin-top: 1rem;">Submit Route for Review</button>
        </form>
    </div>

    <h2 style="margin-bottom: 1rem;">Recent Submissions</h2>
    <div class="data-table-wrapper">
        <table class="standard-data-table">
            <thead>
                <tr>
                    <th>Bus</th>
                    <th>Path</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routesList as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['bus_name']) ?></td>
                        <td><?= htmlspecialchars($r['start_point']) ?> - <?= htmlspecialchars($r['end_point']) ?></td>
                        <td>
                            <?php if ($r['is_approved']): ?>
                                <span style="color: #10B981; font-weight: 600;">Live</span>
                            <?php else: ?>
                                <span style="color: #f59e0b; font-weight: 600;">Pending Review</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($routesList)): ?>
                    <tr><td colspan="3" style="text-align:center; padding: 2rem;">No submissions yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
