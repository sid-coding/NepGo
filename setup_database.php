<?php
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

    $pdo->exec("CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_approved TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Users table ready.<br>";

    $pdo->exec("CREATE TABLE buses (
        bus_id INT AUTO_INCREMENT PRIMARY KEY,
        bus_number VARCHAR(20) NOT NULL UNIQUE,
        bus_name VARCHAR(100),
        bus_image VARCHAR(255) DEFAULT NULL
    )");
    echo "✅ Buses table ready.<br>";

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

    $pdo->exec("CREATE TABLE stops (
        stop_id INT AUTO_INCREMENT PRIMARY KEY,
        route_id INT,
        stop_name VARCHAR(100),
        distance_from_start DECIMAL(5,2) DEFAULT 0.0,
        stop_order INT,
        FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
    )");
    echo "✅ Stops table ready.<br>";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "<h3>3. Creating Master Admin...</h3>";
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_approved) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@nepalgo.com', $hashedPassword, 1]);
    echo "✅ Master Admin created (Email: <b>admin@nepalgo.com</b>).<br>";

    echo "<h3>4. Seeding 18 Real-Life Routes...</h3>";
    $pdo->beginTransaction();
    $stopStmt = $pdo->prepare("INSERT INTO stops (route_id, stop_name, distance_from_start, stop_order) VALUES (?, ?, ?, ?)");
    $busStmt = $pdo->prepare("INSERT INTO buses (bus_number, bus_name) VALUES (?, ?)");
    $routeStmt = $pdo->prepare("INSERT INTO routes (bus_id, route_name, start_point, end_point, distance_km, is_approved) VALUES (?, ?, ?, ?, ?, 1)");

    $routesData = [
        ['BA 3 KHA 2020', 'Sajha Yatayat', 'Patan-North Line', 'Lagankhel', 'Budhanilkantha', 14.5, [
            ['Lagankhel', 0.0], ['Jawalakhel', 1.2], ['Kupondole', 2.8], ['Tripureshwor', 3.5], 
            ['Ratnapark', 4.5], ['Lainchaur', 5.8], ['Maharajgunj', 9.2], ['Budhanilkantha', 14.5]
        ]],
        ['BA 1 KHA 8899', 'Mahanagar Yatayat', 'Ring Road (Clockwise)', 'Kalanki', 'Kalanki (Loop)', 27.2, [
            ['Kalanki', 0.0], ['Swayambhu', 3.2], ['Balaju', 6.5], ['Maharajgunj', 10.2], 
            ['Chabahil', 13.5], ['Gaushala', 15.2], ['Koteshwor', 18.8], ['Satdobato', 22.5], 
            ['Balkhu', 25.2], ['Kalanki', 27.2]
        ]],
        ['BA 4 KHA 1122', 'Bhaktapur Express', 'KTM-BKT Direct', 'Ratnapark', 'Kamalbinayak', 13.0, [
            ['Ratnapark', 0.0], ['Maitighar', 1.5], ['Baneshwor', 3.2], ['Koteshwor', 5.8], 
            ['Lokanthali', 7.2], ['Thimi', 9.5], ['Sallaghari', 11.2], ['Kamalbinayak', 13.0]
        ]],
        ['BA 2 KHA 7788', 'Nepal Yatayat', 'Gongabu-Balkot Line', 'Gongabu', 'Balkot', 16.5, [
            ['Gongabu', 0.0], ['Sohrakhutte', 2.5], ['Ratnapark', 4.5], ['Baneshwor', 7.2], 
            ['Koteshwor', 9.8], ['Jadibuti', 11.5], ['Balkot', 16.5]
        ]],
        ['BA 1 KHA 5544', 'City Bus', 'Core City Line', 'Kalanki', 'Ratnapark', 6.0, [
            ['Kalanki', 0.0], ['Kalimati', 2.5], ['Teku', 4.1], ['Tripureshwor', 5.2], ['Ratnapark', 6.0]
        ]],
        ['BA 3 KHA 9900', 'Swayambhu Yatayat', 'East-West Link', 'Swayambhu', 'Jorpati', 12.0, [
            ['Swayambhu', 0.0], ['Sitapaila', 2.2], ['Kalimati', 4.5], ['Ratnapark', 7.0], 
            ['Chabahil', 9.5], ['Bouddha', 10.8], ['Jorpati', 12.0]
        ]],
        ['BA 2 KHA 3311', 'Thankot Yatayat', 'Western Gateway', 'Thankot', 'Ratnapark', 11.5, [
            ['Thankot', 0.0], ['Gurjudhara', 2.5], ['Satungal', 4.8], ['Kalanki', 7.2], 
            ['Kalimati', 9.8], ['Ratnapark', 11.5]
        ]],
        ['BA 1 KHA 4422', 'Tokha Bus', 'Northern Link', 'Tokha', 'Ratnapark', 9.0, [
            ['Tokha', 0.0], ['Basundhara', 4.5], ['Samakhushi', 6.2], ['Lainchaur', 8.0], ['Ratnapark', 9.0]
        ]],
        ['BA 3 KHA 5566', 'Kapan Yatayat', 'Kapan Connect', 'Kapan', 'Ratnapark', 8.5, [
            ['Kapan', 0.0], ['Sukedhara', 3.5], ['Chabahil', 5.0], ['Gaushala', 6.8], ['Ratnapark', 8.5]
        ]],
        ['BA 1 KHA 1234', 'Dakshinkali Yatayat', 'Southern Core', 'Ratnapark', 'Chobhar', 10.0, [
            ['Ratnapark', 0.0], ['Tripureshwor', 1.5], ['Kalimati', 3.2], ['Balkhu', 6.8], ['Chobhar', 10.0]
        ]],
        ['BA 2 KHA 4433', 'Kirtipur Yatayat', 'Kirtipur Line', 'Ratnapark', 'Kirtipur', 9.0, [
            ['Ratnapark', 0.0], ['Kalimati', 3.5], ['Balkhu', 6.2], ['Kirtipur', 9.0]
        ]],
        ['BA 3 KHA 1212', 'Godawari Bus', 'Godawari Line', 'Lagankhel', 'Godawari', 10.5, [
            ['Lagankhel', 0.0], ['Satdobato', 2.5], ['Harisiddhi', 5.2], ['Thaiba', 8.0], ['Godawari', 10.5]
        ]],
        ['BA 1 KHA 7766', 'Kavre Bus', 'Panauti Express', 'Ratnapark', 'Panauti', 32.0, [
            ['Ratnapark', 0.0], ['Baneshwor', 3.5], ['Koteshwor', 6.2], ['Banepa', 26.5], ['Panauti', 32.0]
        ]],
        ['BA 2 KHA 9988', 'Airport Shuttle', 'Ring Road (Airport)', 'Kalanki', 'Airport', 13.5, [
            ['Kalanki', 0.0], ['Balkhu', 3.5], ['Satdobato', 7.2], ['Koteshwor', 11.5], ['Airport', 13.5]
        ]],
        ['BA 3 KHA 4455', 'Sankhu Bus', 'Sankhu Link', 'Ratnapark', 'Sankhu', 17.5, [
            ['Ratnapark', 0.0], ['Chabahil', 4.5], ['Bouddha', 6.2], ['Jorpati', 8.5], ['Sankhu', 17.5]
        ]],
        ['BA 1 KHA 2233', 'Dillibazar Micro', 'Pepsicola-Ratnapark', 'Ratnapark', 'Pepsicola', 7.5, [
            ['Ratnapark', 0.0], ['Putalisadak', 1.2], ['Dillibazar', 2.8], ['Gausala', 5.5], ['Pepsicola', 7.5]
        ]],
        ['BA 2 KHA 1122', 'Sundarijal Bus', 'Sundarijal Line', 'Ratnapark', 'Sundarijal', 15.0, [
            ['Ratnapark', 0.0], ['Chabahil', 4.5], ['Jorpati', 7.8], ['Gokarna', 10.5], ['Sundarijal', 15.0]
        ]],
        ['BA 3 KHA 8877', 'Lubhu Service', 'Lubhu Connect', 'Ratnapark', 'Lubhu', 11.0, [
            ['Ratnapark', 0.0], ['Baneshwor', 3.5], ['Koteshwor', 6.2], ['Imadol', 8.5], ['Lubhu', 11.0]
        ]]
    ];

    foreach ($routesData as $r) {
        $busStmt->execute([$r[0], $r[1]]);
        $bus_id = $pdo->lastInsertId();
        $routeStmt->execute([$bus_id, $r[2], $r[3], $r[4], $r[5]]);
        $route_id = $pdo->lastInsertId();
        foreach ($r[6] as $i => $stopData) {
            $stopStmt->execute([$route_id, $stopData[0], $stopData[1], $i + 1]);
        }
    }

    $pdo->commit();
    echo "✅ Successfully seeded 18 routes.<br>";

    echo "<hr>";
    echo "<h2>🎉 SYSTEM READY!</h2>";
    echo "<p>Go to <a href='index'>Home Page</a> or <a href='login'>Login</a>.</p>";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo "<h3>❌ Error during setup: " . $e->getMessage() . "</h3>";
}
?>
