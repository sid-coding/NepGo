-- NepalGo Database Export
-- Database: nepalgo

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `buses`
--
CREATE TABLE `buses` (
  `bus_id` int(11) NOT NULL AUTO_INCREMENT,
  `bus_number` varchar(20) NOT NULL,
  `bus_name` varchar(100) DEFAULT NULL,
  `bus_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bus_id`),
  UNIQUE KEY `bus_number` (`bus_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `routes`
--
CREATE TABLE `routes` (
  `route_id` int(11) NOT NULL AUTO_INCREMENT,
  `bus_id` int(11) DEFAULT NULL,
  `route_name` varchar(100) DEFAULT NULL,
  `start_point` varchar(100) DEFAULT NULL,
  `end_point` varchar(100) DEFAULT NULL,
  `distance_km` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`route_id`),
  KEY `bus_id` (`bus_id`),
  CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `stops`
--
CREATE TABLE `stops` (
  `stop_id` int(11) NOT NULL AUTO_INCREMENT,
  `route_id` int(11) DEFAULT NULL,
  `stop_name` varchar(100) DEFAULT NULL,
  `stop_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`stop_id`),
  KEY `route_id` (`route_id`),
  CONSTRAINT `stops_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 
COMMIT;
