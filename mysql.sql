-- phpMyAdmin SQL Dump
-- version 4.9.5deb2ubuntu0.1~esm1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 13, 2025 at 04:16 PM
-- Server version: 8.0.42-0ubuntu0.20.04.1
-- PHP Version: 7.4.3-4ubuntu2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ceres_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_recommendations`
--

CREATE TABLE `ai_recommendations` (
  `id` int NOT NULL,
  `farm_id` int NOT NULL,
  `icon_class` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `reason` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ai_recommendations`
--

INSERT INTO `ai_recommendations` (`id`, `farm_id`, `icon_class`, `title`, `description`, `reason`, `created_at`) VALUES
(1, 1, 'fas fa-tint irrigation-icon', 'Irrigation Adjustment', 'Recommended: reduce water usage by 15%', 'Rain forecasted in 24 hours', '2025-05-05 09:27:54'),
(2, 1, 'fas fa-seedling fertiliser-icon', 'Fertilisation Plan', 'Recommended: increase nitrogen by 10%', 'Soil nutrient levels are low', '2025-05-05 09:27:54'),
(3, 1, 'fas fa-solar-panel', 'Solar Panel Cleaning', 'Recommended: clean solar panels for +5% efficiency', 'Dust accumulation detected', '2025-05-05 09:27:54'),
(4, 2, 'fas fa-bug pest-icon', 'Pest Control', 'Recommended: deploy drone surveillance', 'Increased pest activity detected', '2025-05-05 09:27:54'),
(5, 2, 'fas fa-wind', 'Wind Turbine Checkup', 'Recommended: inspect wind turbines for wear', 'Slight drop in energy output', '2025-05-05 09:27:54'),
(6, 2, 'fas fa-cloud-sun', 'Weather-based Irrigation', 'Recommended: delay irrigation by 8 hours', 'Rain possibility of 40% tomorrow', '2025-05-05 09:27:54'),
(7, 2, 'fas fa-tint irrigation-icon', 'Irrigation Adjustment', 'Recommended: Reduce water usage by 15%', 'Rain forecasted in 24 hours', '2025-05-05 09:36:41'),
(8, 2, 'fas fa-seedling fertiliser-icon', 'Fertilisation Plan', 'Recommended: Increase nitrogen by 10%', 'Soil nutrient levels are low', '2025-05-05 09:36:41'),
(9, 2, 'fas fa-bug pest-icon', 'Pest Control', 'Recommended: Deploy drone surveillance', 'Increased pest activity detected', '2025-05-05 09:36:41');

-- --------------------------------------------------------

--
-- Table structure for table `energy_readings`
--

CREATE TABLE `energy_readings` (
  `id` int NOT NULL,
  `farm_id` int NOT NULL,
  `reading_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `solar_output` varchar(20) NOT NULL,
  `solar_usage` varchar(20) NOT NULL,
  `wind_output` varchar(20) NOT NULL,
  `wind_usage` varchar(20) NOT NULL,
  `battery_charge` varchar(10) NOT NULL,
  `battery_capacity` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `energy_readings`
--

INSERT INTO `energy_readings` (`id`, `farm_id`, `reading_time`, `solar_output`, `solar_usage`, `wind_output`, `wind_usage`, `battery_charge`, `battery_capacity`) VALUES
(1, 1, '2025-05-05 10:29:24', '78 kW', '65 kW', '54 kW', '40 kW', '85%', '200 kWh'),
(2, 2, '2025-05-05 10:29:24', '66 kW', '50 kW', '0', '0', '0%', '180 kWh');

-- --------------------------------------------------------

--
-- Table structure for table `farms`
--

CREATE TABLE `farms` (
  `id` int NOT NULL,
  `farm_name` varchar(255) NOT NULL,
  `farm_code` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL,
  `country` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `farms`
--

INSERT INTO `farms` (`id`, `farm_name`, `farm_code`, `location`, `country`) VALUES
(1, 'Fotovoltaica Macotera SL', 'FOTOMACO1', 'Salamanca Region', 'Spain'),
(2, 'Agia Sofia Estate', 'AGIA', 'Peloponnese Region', 'Greece');

-- --------------------------------------------------------

--
-- Table structure for table `farm_actuators`
--

CREATE TABLE `farm_actuators` (
  `id` int NOT NULL,
  `farm_id` int NOT NULL,
  `actuator_name` varchar(100) NOT NULL,
  `actuator_type` varchar(50) NOT NULL,
  `node_count` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `farm_actuators`
--

INSERT INTO `farm_actuators` (`id`, `farm_id`, `actuator_name`, `actuator_type`, `node_count`) VALUES
(1, 1, 'Irrigation Pump Array', 'irrigation', 2),
(3, 2, 'Sprinkler System', 'irrigation', 2),
(4, 2, 'Drone Docking Station', 'drone-control', 1),
(8, 1, 'Pivot Irrigation System', 'irrigation', 1),
(9, 1, 'Drip Irrigation Lines', 'irrigation', 3),
(10, 1, 'Tractor Control Module', 'tractor', 1),
(11, 1, 'Drone Launch Pad', 'drone-launch', 2);

-- --------------------------------------------------------

--
-- Table structure for table `farm_measurements`
--

CREATE TABLE `farm_measurements` (
  `id` int NOT NULL,
  `farm_id` int NOT NULL,
  `reading_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `soil_moisture` varchar(10) NOT NULL,
  `temperature` varchar(10) NOT NULL,
  `energy_solar` varchar(10) NOT NULL,
  `energy_wind` varchar(10) NOT NULL,
  `forecast` varchar(100) NOT NULL,
  `rain_today` varchar(10) NOT NULL,
  `rain_tomorrow` varchar(10) NOT NULL,
  `wind_speed` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `farm_measurements`
--

INSERT INTO `farm_measurements` (`id`, `farm_id`, `reading_time`, `soil_moisture`, `temperature`, `energy_solar`, `energy_wind`, `forecast`, `rain_today`, `rain_tomorrow`, `wind_speed`) VALUES
(1, 1, '2025-05-05 09:07:46', '42%', '23°C', '78%', '65%', 'Mostly sunny', '10%', '5%', '12 km/h (gusts 20 km'),
(2, 2, '2025-05-05 09:07:46', '38%', '24°C', 'N/A', 'N/A', 'Partly cloudy', '20%', '15%', '15 km/h (gusts 25 km'),
(3, 1, '2025-05-05 12:00:00', '42%', '13°C', '78%', 'N/A', 'Mostly Cloudy', '11%', 'N/A', '10 km/h NW'),
(4, 1, '2025-05-05 13:00:00', '42%', '15°C', '78%', 'N/A', 'Mostly Cloudy', '18%', 'N/A', '9 km/h NW'),
(5, 1, '2025-05-05 14:00:00', '42%', '15°C', '78%', 'N/A', 'Mostly Cloudy', '19%', 'N/A', '10 km/h NW'),
(6, 1, '2025-05-05 15:00:00', '42%', '15°C', '78%', 'N/A', 'Isolated Thunderstorms', '34%', 'N/A', '11 km/h NW'),
(7, 1, '2025-05-05 15:20:00', '42%', '15°C', '78%', 'N/A', 'Isolated Thunderstorms (Meeting)', '34%', 'N/A', '11 km/h NW'),
(8, 1, '2025-05-05 16:00:00', '42%', '14°C', '78%', 'N/A', 'Scattered Thunderstorms', '54%', 'N/A', '16 km/h NNW'),
(9, 1, '2025-05-05 17:00:00', '42%', '14°C', '78%', 'N/A', 'Thunderstorms', '71%', 'N/A', '16 km/h NNW'),
(10, 1, '2025-05-05 18:00:00', '42%', '13°C', '78%', 'N/A', 'Scattered Thunderstorms', '52%', 'N/A', '13 km/h NNW'),
(11, 1, '2025-05-05 19:00:00', '42%', '13°C', '78%', 'N/A', 'Few Showers', '31%', 'N/A', '10 km/h NW'),
(12, 1, '2025-05-05 20:00:00', '42%', '13°C', '78%', 'N/A', 'Partly Cloudy', '14%', 'N/A', '10 km/h NNW'),
(13, 1, '2025-05-05 21:00:00', '42%', '12°C', '78%', 'N/A', 'Showers', '36%', 'N/A', '8 km/h N'),
(14, 1, '2025-05-05 22:00:00', '42%', '11°C', '78%', 'N/A', 'Showers', '35%', 'N/A', '8 km/h NE'),
(15, 1, '2025-05-05 23:00:00', '42%', '10°C', '78%', 'N/A', 'Cloudy', '24%', 'N/A', '9 km/h ENE');

-- --------------------------------------------------------

--
-- Table structure for table `farm_sensors`
--

CREATE TABLE `farm_sensors` (
  `id` int NOT NULL,
  `farm_id` int NOT NULL,
  `sensor_name` varchar(100) NOT NULL,
  `sensor_type` varchar(50) NOT NULL,
  `node_count` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `farm_sensors`
--

INSERT INTO `farm_sensors` (`id`, `farm_id`, `sensor_name`, `sensor_type`, `node_count`) VALUES
(3, 2, 'Soil Moisture Node', 'soil-moisture', 4),
(4, 2, 'pH Node', 'ph-level', 1),
(13, 1, 'Soil Moisture Node', 'soil-moisture', 3),
(14, 1, 'Temperature Node', 'temperature', 2),
(15, 1, 'pH Node', 'ph-level', 1),
(16, 1, 'EC Node', 'ec-level', 1),
(17, 1, 'Weather Station', 'weather', 1),
(18, 1, 'Drone Telemetry', 'drone-sensor', 1),
(19, 1, 'Tractor GNSS Node', 'gnss', 2),
(20, 1, 'Light Sensor', 'light', 1),
(21, 1, 'Humidity Sensor', 'humidity', 1),
(22, 1, 'Rain Gauge', 'rain', 1);

-- --------------------------------------------------------

--
-- Table structure for table `field_data`
--

CREATE TABLE `field_data` (
  `id` int NOT NULL,
  `farm_id` int NOT NULL,
  `reading_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `soil_temperature` varchar(10) DEFAULT NULL,
  `soil_moisture` varchar(10) DEFAULT NULL,
  `ph_level` varchar(10) DEFAULT NULL,
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `field_data`
--

INSERT INTO `field_data` (`id`, `farm_id`, `reading_time`, `soil_temperature`, `soil_moisture`, `ph_level`, `notes`) VALUES
(1, 1, '2025-05-05 10:39:20', '22°C', '40%', '6.5', 'Slightly dry, recommended irrigation soon.'),
(2, 1, '2025-05-05 10:39:20', '20°C', '38%', '6.4', 'Cool morning; moderate moisture.'),
(3, 2, '2025-05-05 10:39:20', '25°C', '35%', '6.8', 'Sunny midday; check dryness.'),
(4, 2, '2025-05-05 10:39:20', '26°C', '30%', '6.6', 'Warm afternoon, potential watering needed.');

-- --------------------------------------------------------

--
-- Table structure for table `server_performance`
--

CREATE TABLE `server_performance` (
  `id` int NOT NULL,
  `farm_id` int NOT NULL,
  `server_name` varchar(50) NOT NULL,
  `measure_time` datetime NOT NULL,
  `cpu_usage` float NOT NULL,
  `memory_usage` float NOT NULL,
  `inbound_mbps` float NOT NULL,
  `outbound_mbps` float NOT NULL,
  `pods` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `server_performance`
--

INSERT INTO `server_performance` (`id`, `farm_id`, `server_name`, `measure_time`, `cpu_usage`, `memory_usage`, `inbound_mbps`, `outbound_mbps`, `pods`) VALUES
(1, 1, 'SensorNode1', '2025-05-10 10:00:00', 25, 40, 5, 4, 3),
(2, 1, 'SensorNode2', '2025-05-10 10:00:00', 20, 35, 3, 2, 2),
(3, 1, 'Wazuh', '2025-05-10 10:00:00', 30, 25, 2, 1, 2),
(4, 1, 'AI/DB', '2025-05-10 10:00:00', 50, 60, 8, 6, 4),
(5, 1, 'WebSrv', '2025-05-10 10:00:00', 35, 45, 4, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `task_schedules`
--

CREATE TABLE `task_schedules` (
  `id` int NOT NULL,
  `farm_id` int NOT NULL,
  `task_name` varchar(100) NOT NULL,
  `next_schedule` varchar(50) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `extra_info` varchar(200) DEFAULT NULL,
  `icon_class` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `task_schedules`
--

INSERT INTO `task_schedules` (`id`, `farm_id`, `task_name`, `next_schedule`, `duration`, `extra_info`, `icon_class`) VALUES
(1, 1, 'Irrigation', '08:00 AM', '30 minutes', 'Water Used: 50 Litres', 'fas fa-tint irrigation-icon'),
(2, 1, 'Fertilisation', '12:00 PM', ' - ', 'Fertiliser Type: Organic, Qty: 10 kg', 'fas fa-seedling fertiliser-icon'),
(3, 1, 'Drone Surveillance', '03:00 PM', '45 minutes', 'Coverage: 5 Hectares', 'fas fa-drone drone-icon'),
(4, 2, 'Irrigation', '07:30 AM', '40 minutes', 'Water Used: 60 Litres', 'fas fa-tint irrigation-icon'),
(5, 2, 'Fertilisation', '11:00 AM', ' - ', 'NPK Fertiliser, Qty: 12 kg', 'fas fa-seedling fertiliser-icon'),
(6, 2, 'Drone Surveillance', '02:00 PM', '60 minutes', 'Coverage: 10 Hectares', 'fas fa-drone drone-icon');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL DEFAULT 'User',
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `location`) VALUES
(1, 'admin', '$2y$10$l5oI7EZQXhX6Ibtxabbdi.Q3gPEvhC.yCs.b/9G.fOfidI/qDPVgq', 'System Administrator', 'Spain'),
(2, 'Maria Lopes', '$2y$10$EncryptedPw', 'Technician', 'Greece');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farm_id` (`farm_id`);

--
-- Indexes for table `energy_readings`
--
ALTER TABLE `energy_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farm_id` (`farm_id`);

--
-- Indexes for table `farms`
--
ALTER TABLE `farms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `farm_actuators`
--
ALTER TABLE `farm_actuators`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farm_id` (`farm_id`);

--
-- Indexes for table `farm_measurements`
--
ALTER TABLE `farm_measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farm_id` (`farm_id`);

--
-- Indexes for table `farm_sensors`
--
ALTER TABLE `farm_sensors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farm_id` (`farm_id`);

--
-- Indexes for table `field_data`
--
ALTER TABLE `field_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farm_id` (`farm_id`);

--
-- Indexes for table `server_performance`
--
ALTER TABLE `server_performance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_schedules`
--
ALTER TABLE `task_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farm_id` (`farm_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `energy_readings`
--
ALTER TABLE `energy_readings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `farms`
--
ALTER TABLE `farms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `farm_actuators`
--
ALTER TABLE `farm_actuators`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `farm_measurements`
--
ALTER TABLE `farm_measurements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `farm_sensors`
--
ALTER TABLE `farm_sensors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `field_data`
--
ALTER TABLE `field_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `server_performance`
--
ALTER TABLE `server_performance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `task_schedules`
--
ALTER TABLE `task_schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD CONSTRAINT `ai_recommendations_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `energy_readings`
--
ALTER TABLE `energy_readings`
  ADD CONSTRAINT `energy_readings_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `farm_actuators`
--
ALTER TABLE `farm_actuators`
  ADD CONSTRAINT `farm_actuators_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `farm_measurements`
--
ALTER TABLE `farm_measurements`
  ADD CONSTRAINT `farm_measurements_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`);

--
-- Constraints for table `farm_sensors`
--
ALTER TABLE `farm_sensors`
  ADD CONSTRAINT `farm_sensors_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `field_data`
--
ALTER TABLE `field_data`
  ADD CONSTRAINT `field_data_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_schedules`
--
ALTER TABLE `task_schedules`
  ADD CONSTRAINT `task_schedules_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
