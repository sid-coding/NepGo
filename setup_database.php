<?php
/**
 * NepalGo - Master Database Setup (Email Auth Version)
 */

require_once 'config.php';

try {
    echo "<h1>🚀 NepalGo - Fresh System Setup</h1>";
    echo "<hr>";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    echo "<h3>1. Cleaning old data...</h3>";
    $pdo->exec("DROP TABLE IF EXISTS stops");
    $pdo->exec("DROP TABLE IF EXISTS routes");
    $pdo->exec("DROP TABLE IF EXISTS buses");
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "✅ Old tables removed.<br>";

    echo "<h3>2. Creating fresh tables...</h3>";

    // Users Table (Added Email)
    $pdo->exec("CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_approved TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Users table ready.<br>";

    // Buses Table
    $pdo->exec("CREATE TABLE buses (
        bus_id INT AUTO_INCREMENT PRIMARY KEY,
        bus_number VARCHAR(20) NOT NULL UNIQUE,
        bus_name VARCHAR(100),
        bus_image VARCHAR(255) DEFAULT NULL
    )");
    echo "✅ Buses table ready.<br>";

    // Routes Table
    $pdo->exec("CREATE TABLE routes (
        route_id INT AUTO_INCREMENT PRIMARY KEY,
        bus_id INT,
        route_name VARCHAR(100),
        start_point VARCHAR(100),
        end_point VARCHAR(100),
        distance_km DECIMAL(5,2) DEFAULT 0.0,
        is_approved TINYINT(1) DEFAULT 0,
        FOREIGN KEY (bus_id) REFERENCES buses(bus_id) ON DELETE CASCADE
    )");
    echo "✅ Routes table ready.<br>";

    // Stops Table
    $pdo->exec("CREATE TABLE stops (
        stop_id INT AUTO_INCREMENT PRIMARY KEY,
        route_id INT,
        stop_name VARCHAR(100),
        stop_order INT,
        FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
    )");
    echo "✅ Stops table ready.<br>";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 3. Create Default Admin (Email: admin@nepalgo.com, Pass: admin123)
    echo "<h3>3. Creating Master Admin...</h3>";
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_approved) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@nepalgo.com', $hashedPassword, 1]);
    echo "✅ Master Admin created (Email: <b>admin@nepalgo.com</b>).<br>";

    // 4. Seed 18 Real Routes
    echo "<h3>4. Seeding 18 Real-Life Routes...</h3>";
    $pdo->beginTransaction();
    $stopStmt = $pdo->prepare("INSERT INTO stops (route_id, stop_name, stop_order) VALUES (?, ?, ?)");
    $busStmt = $pdo->prepare("INSERT INTO buses (bus_number, bus_name) VALUES (?, ?)");
    $routeStmt = $pdo->prepare("INSERT INTO routes (bus_id, route_name, start_point, end_point, distance_km, is_approved) VALUES (?, ?, ?, ?, ?, 1)");

    $routesData = [
        ['BA 3 KHA 2020', 'Sajha Yatayat', 'Patan-North Line', 'Lagankhel', 'Budhanilkantha', 14.5, ['Lagankhel', 'Jawalakhel', 'Kupondole', 'Tripureshwor', 'Ratnapark', 'Lainchaur', 'Maharajgunj', 'Budhanilkantha']],
        ['BA 1 KHA 8899', 'Mahanagar Yatayat', 'Ring Road (Clockwise)', 'Kalanki', 'Kalanki (Loop)', 27.2, ['Kalanki', 'Swayambhu', 'Balaju', 'Maharajgunj', 'Chabahil', 'Gaushala', 'Koteshwor', 'Satdobato', 'Balkhu', 'Kalanki']],
        ['BA 4 KHA 1122', 'Bhaktapur Express', 'KTM-BKT Direct', 'Ratnapark', 'Kamalbinayak', 13.0, ['Ratnapark', 'Maitighar', 'Baneshwor', 'Koteshwor', 'Lokanthali', 'Thimi', 'Sallaghari', 'Kamalbinayak']],
        ['BA 2 KHA 7788', 'Nepal Yatayat', 'Gongabu-Balkot Line', 'Gongabu', 'Balkot', 16.5, ['Gongabu', 'Sohrakhutte', 'Ratnapark', 'Baneshwor', 'Koteshwor', 'Jadibuti', 'Balkot']],
        ['BA 1 KHA 5544', 'City Bus', 'Core City Line', 'Kalanki', 'Ratnapark', 6.0, ['Kalanki', 'Kalimati', 'Teku', 'Tripureshwor', 'Ratnapark']],
        ['BA 3 KHA 9900', 'Swayambhu Yatayat', 'East-West Link', 'Swayambhu', 'Jorpati', 12.0, ['Swayambhu', 'Sitapaila', 'Kalimati', 'Ratnapark', 'Chabahil', 'Bouddha', 'Jorpati']],
        ['BA 2 KHA 3311', 'Thankot Yatayat', 'Western Gateway', 'Thankot', 'Ratnapark', 11.5, ['Thankot', 'Gurjudhara', 'Satungal', 'Kalanki', 'Kalimati', 'Ratnapark']],
        ['BA 1 KHA 4422', 'Tokha Bus', 'Northern Link', 'Tokha', 'Ratnapark', 9.0, ['Tokha', 'Basundhara', 'Samakhushi', 'Lainchaur', 'Ratnapark']],
        ['BA 3 KHA 5566', 'Kapan Yatayat', 'Kapan Connect', 'Kapan', 'Ratnapark', 8.5, ['Kapan', 'Sukedhara', 'Chabahil', 'Gaushala', 'Ratnapark']],
        ['BA 1 KHA 1234', 'Dakshinkali Yatayat', 'Southern Core', 'Ratnapark', 'Chobhar', 10.0, ['Ratnapark', 'Tripureshwor', 'Kalimati', 'Balkhu', 'Chobhar']],
        ['BA 2 KHA 4433', 'Kirtipur Yatayat', 'Kirtipur Line', 'Ratnapark', 'Kirtipur', 9.0, ['Ratnapark', 'Kalimati', 'Balkhu', 'Kirtipur']],
        ['BA 3 KHA 1212', 'Godawari Bus', 'Godawari Line', 'Lagankhel', 'Godawari', 10.5, ['Lagankhel', 'Satdobato', 'Harisiddhi', 'Thaiba', 'Godawari']],
        ['BA 1 KHA 7766', 'Kavre Bus', 'Panauti Express', 'Ratnapark', 'Panauti', 32.0, ['Ratnapark', 'Baneshwor', 'Koteshwor', 'Banepa', 'Panauti']],
        ['BA 2 KHA 9988', 'Airport Shuttle', 'Ring Road (Airport)', 'Kalanki', 'Airport', 13.5, ['Kalanki', 'Balkhu', 'Satdobato', 'Koteshwor', 'Airport']],
        ['BA 3 KHA 4455', 'Sankhu Bus', 'Sankhu Link', 'Ratnapark', 'Sankhu', 17.5, ['Ratnapark', 'Chabahil', 'Bouddha', 'Jorpati', 'Sankhu']],
        ['BA 1 KHA 2233', 'Dillibazar Micro', 'Pepsicola-Ratnapark', 'Ratnapark', 'Pepsicola', 7.5, ['Ratnapark', 'Putalisadak', 'Dillibazar', 'Gausala', 'Pepsicola']],
        ['BA 2 KHA 1122', 'Sundarijal Bus', 'Sundarijal Line', 'Ratnapark', 'Sundarijal', 15.0, ['Ratnapark', 'Chabahil', 'Jorpati', 'Gokarna', 'Sundarijal']],
        ['BA 3 KHA 8877', 'Lubhu Service', 'Lubhu Connect', 'Ratnapark', 'Lubhu', 11.0, ['Ratnapark', 'Baneshwor', 'Koteshwor', 'Imadol', 'Lubhu']]
    ];

    foreach ($routesData as $r) {
        $busStmt->execute([$r[0], $r[1]]);
        $bus_id = $pdo->lastInsertId();
        $routeStmt->execute([$bus_id, $r[2], $r[3], $r[4], $r[5]]);
        $route_id = $pdo->lastInsertId();
        foreach ($r[6] as $i => $stop) {
            $stopStmt->execute([$route_id, $stop, $i + 1]);
        }
    }

    $pdo->commit();
    echo "✅ Successfully seeded 18 routes.<br>";

    echo "<hr>";
    echo "<h2>🎉 SYSTEM READY!</h2>";
    echo "<p>Go to <a href='index.php'>Home Page</a> or <a href='login.php'>Login</a>.</p>";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo "<h3>❌ Error during setup: " . $e->getMessage() . "</h3>";
}
?>
