<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// --- 1. HANDLE ADMIN LOGIN ---
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND username = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
    } else {
        $error = "Invalid Admin Credentials.";
    }
}

// --- 2. SECURITY CHECK ---
$isAdmin = (isset($_SESSION['user_id']) && $_SESSION['username'] === 'admin');

// --- 3. HANDLE ADMIN ACTIONS (Only if logged in) ---
$msg = '';
$msgType = '';
if ($isAdmin) {
    // Approve Route
    if (isset($_GET['approve_route'])) {
        $rid = intval($_GET['approve_route']);
        $pdo->prepare("UPDATE routes SET is_approved = 1 WHERE route_id = ?")->execute([$rid]);
        $msg = "Route approved successfully!";
        $msgType = "success";
    }

    // Reject Route
    if (isset($_GET['reject_route'])) {
        $rid = intval($_GET['reject_route']);
        $pdo->prepare("DELETE FROM routes WHERE route_id = ?")->execute([$rid]);
        $msg = "Route suggestion rejected.";
        $msgType = "success";
    }

    // Approve User
    if (isset($_GET['approve_user'])) {
        $uid = intval($_GET['approve_user']);
        $pdo->prepare("UPDATE users SET is_approved = 1 WHERE user_id = ?")->execute([$uid]);
        $msg = "User account approved!";
        $msgType = "success";
    }

    // Add New Route Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_route'])) {
        $bus_id      = $_POST['bus_id'] ?? ''; 
        $bus_number  = strtoupper(trim($_POST['bus_number'] ?? '')); 
        $bus_name    = trim($_POST['bus_name'] ?? '');
        $route_name  = trim($_POST['route_name'] ?? '');
        $start_point = trim($_POST['start_point'] ?? '');
        $end_point   = trim($_POST['end_point'] ?? '');
        $stops_raw   = trim($_POST['stops'] ?? '');
        $distance    = floatval($_POST['distance'] ?? 0);

        if ($bus_id === '' || $start_point === '' || $end_point === '') {
            $msg = "Please select a bus and enter start/end points.";
            $msgType = "danger";
        } else {
            try {
                $pdo->beginTransaction();
                
                // If adding a new bus
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

                // Insert Route (Admin adds are approved by default)
                $stmt = $pdo->prepare("INSERT INTO routes (bus_id, route_name, start_point, end_point, distance_km, is_approved) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$bus_id, $route_name, $start_point, $end_point, $distance]);
                $route_id = $pdo->lastInsertId();

                // Prepare Stops
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
                $msg = "Route added and published successfully!";
                $msgType = "success";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $msg = "Error: " . $e->getMessage();
                $msgType = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin – NepalGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=2.0">
    <script>
        function toggleNewBusFields() {
            const select = document.getElementById('bus_id');
            const newBusSection = document.getElementById('new_bus_fields');
            newBusSection.style.display = (select.value === 'new') ? 'block' : 'none';
        }
    </script>
</head>
<body style="<?= !$isAdmin ? 'background-color: #111827;' : '' ?>">

<?php if (!$isAdmin): ?>
    <!-- HIDDEN LOGIN FORM -->
    <div class="content-container" style="max-width: 400px; margin-top: 10rem;">
        <div class="section-header"><h1 style="color: white;">Master Admin Access</h1></div>
        <?php if ($error): ?><div class="status-alert alert-danger"><?= $error ?></div><?php endif; ?>
        <div class="management-area-box" style="padding: 2.5rem; background: #1F2937; border: 1px solid #374151;">
            <form method="POST">
                <input type="hidden" name="admin_login" value="1">
                <div class="form-field-group">
                    <label style="color: #D1D5DB;">Admin Email</label>
                    <input type="email" name="email" class="form-input-control" placeholder="admin@nepalgo.com" required autofocus style="background: #374151; border-color: #4B5563; color: white;">
                </div>
                <div class="form-field-group">
                    <label style="color: #D1D5DB;">Password</label>
                    <input type="password" name="password" class="form-input-control" required style="background: #374151; border-color: #4B5563; color: white;">
                </div>
                <button type="submit" class="action-btn primary-btn" style="width: 100%; margin-top: 1rem; background: #4F46E5;">Login</button>
            </form>
        </div>
        <p style="text-align: center; margin-top: 2rem;"><a href="index.php" style="color: #6B7280; font-size: 0.9rem;">&larr; Back to Website</a></p>
    </div>
<?php else: ?>
    <?php include 'navbar.php'; ?>
    <div class="content-container">
        <div class="section-header">
            <h1>Master Admin Dashboard</h1>
            <p>Welcome back, Administrator.</p>
            <div style="margin-top: 1rem;">
                <a href="logout.php" class="action-btn" style="background-color: #EF4444; color: white; border-radius: 4px;">Logout</a>
            </div>
        </div>

        <?php if ($msg): ?><div class="status-alert alert-<?= $msgType ?>"><?= $msg ?></div><?php endif; ?>

        <!-- 1. ADD NEW ROUTE -->
        <div class="management-area-box" style="margin-bottom: 4rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem;">Add New Route Directly</h2>
            <form method="POST">
                <input type="hidden" name="add_route" value="1">
                <div class="form-field-group">
                    <label>Select Bus</label>
                    <select name="bus_id" id="bus_id" class="form-input-control" onchange="toggleNewBusFields()" required>
                        <option value="">-- Select --</option>
                        <?php 
                        $buses = $pdo->query("SELECT * FROM buses ORDER BY bus_name ASC")->fetchAll();
                        foreach ($buses as $bus) {
                            echo "<option value='{$bus['bus_id']}'>" . htmlspecialchars($bus['bus_name']) . " (" . htmlspecialchars($bus['bus_number']) . ")</option>";
                        }
                        ?>
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

                <div class="admin-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
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

                <button type="submit" class="action-btn primary-btn" style="width: 100%; padding: 1rem; margin-top: 1rem;">Publish Route</button>
            </form>
        </div>

        <!-- 2. PENDING SUGGESTIONS -->
        <h2 style="margin-bottom: 1.5rem; color: #f59e0b;">Pending Suggestions (Review)</h2>
        <div class="data-table-wrapper" style="margin-bottom: 4rem;">
            <table class="standard-data-table">
                <thead><tr><th>Bus</th><th>Path</th><th>Action</th></tr></thead>
                <tbody>
                    <?php 
                    $pending = $pdo->query("SELECT r.*, b.bus_name FROM routes r JOIN buses b ON r.bus_id = b.bus_id WHERE r.is_approved = 0")->fetchAll();
                    if (count($pending) > 0):
                        foreach ($pending as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['bus_name']) ?></td>
                                <td><?= htmlspecialchars($r['start_point']) ?> - <?= htmlspecialchars($r['end_point']) ?></td>
                                <td>
                                    <a href="?approve_route=<?= $r['route_id'] ?>" style="color: green; font-weight: 600;">Approve</a> | 
                                    <a href="?reject_route=<?= $r['route_id'] ?>" style="color: red; font-weight: 600;" onclick="return confirm('Reject this suggestion?')">Reject</a>
                                </td>
                            </tr>
                        <?php endforeach; 
                    else: ?>
                        <tr><td colspan="3" style="text-align:center; padding: 2rem;">No pending suggestions.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 3. LIVE ROUTES -->
        <h2 style="margin-bottom: 1.5rem; color: #10B981;">Live Routes (Registry)</h2>
        <div class="data-table-wrapper">
            <table class="standard-data-table">
                <thead><tr><th>Bus Number</th><th>Path</th><th>Action</th></tr></thead>
                <tbody>
                    <?php 
                    $live = $pdo->query("SELECT r.*, b.bus_number FROM routes r JOIN buses b ON r.bus_id = b.bus_id WHERE r.is_approved = 1 ORDER BY r.route_id DESC")->fetchAll();
                    foreach ($live as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['bus_number']) ?></td>
                            <td><?= htmlspecialchars($r['start_point']) ?> - <?= htmlspecialchars($r['end_point']) ?></td>
                            <td>
                                <a href="?reject_route=<?= $r['route_id'] ?>" onclick="return confirm('Permanently delete this live route?')" style="color: red; font-weight: 600;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
