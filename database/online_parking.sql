-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 07:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_parking`
--

-- --------------------------------------------------------

--
-- Table structure for table `a_bookings`
--

CREATE TABLE `a_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `vehicle_number` varchar(20) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `duration` int(11) NOT NULL,
  `end_time` time NOT NULL,
  `cost_per_hour` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `bkash_number` varchar(20) NOT NULL,
  `bkash_pin` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `a_bookings`
--

INSERT INTO `a_bookings` (`id`, `user_id`, `slot_id`, `vehicle_number`, `booking_date`, `booking_time`, `duration`, `end_time`, `cost_per_hour`, `total_cost`, `bkash_number`, `bkash_pin`, `status`, `created_at`) VALUES
(10, 24, 28, 'N23', '2025-05-07', '18:08:00', 3, '21:08:00', 50.00, 150.00, '01704442185', '11111', 'Pending', '2025-05-07 16:08:52'),
(11, 25, 27, 'D10', '2025-05-07', '18:29:00', 1, '19:29:00', 50.00, 50.00, '01779552185', '11111', 'Pending', '2025-05-07 16:29:53'),
(12, 25, 26, 'D10', '2025-05-07', '19:26:00', 5, '00:26:00', 50.00, 250.00, '111111111', 'soumic3', 'Pending', '2025-05-07 17:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `parking_slots`
--

CREATE TABLE `parking_slots` (
  `id` int(11) NOT NULL,
  `slot_number` varchar(10) NOT NULL,
  `location` varchar(100) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `vehicle_type` enum('bike','car') NOT NULL,
  `cost_per_hour` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_slots`
--

INSERT INTO `parking_slots` (`id`, `slot_number`, `location`, `is_available`, `vehicle_type`, `cost_per_hour`) VALUES
(1, 'B1', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(2, 'B2', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(3, 'B3', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(4, 'B4', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(5, 'B5', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(6, 'B6', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(7, 'B7', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(8, 'B8', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(9, 'B9', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(10, 'B10', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(11, 'B11', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(12, 'B12', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(13, 'B13', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(14, 'B14', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(15, 'B15', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(16, 'B16', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(17, 'B17', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(18, 'B18', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(19, 'B19', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(20, 'B20', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(21, 'B21', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(22, 'B22', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(23, 'B23', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(24, 'B24', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(25, 'B25', 'Bashundhara Shopping Mall', 1, 'bike', 20.00),
(26, 'C1', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(27, 'C2', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(28, 'C3', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(29, 'C4', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(30, 'C5', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(31, 'C6', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(32, 'C7', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(33, 'C8', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(34, 'C9', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(35, 'C10', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(36, 'C11', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(37, 'C12', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(38, 'C13', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(39, 'C14', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(40, 'C15', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(41, 'C16', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(42, 'C17', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(43, 'C18', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(44, 'C19', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(45, 'C20', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(46, 'C21', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(47, 'C22', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(48, 'C23', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(49, 'C24', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(50, 'C25', 'Bashundhara Shopping Mall', 1, 'car', 50.00),
(51, 'J1', 'Jamuna Future Park', 1, 'bike', 25.00),
(52, 'J2', 'Jamuna Future Park', 1, 'bike', 25.00),
(53, 'J3', 'Jamuna Future Park', 1, 'bike', 25.00),
(54, 'J4', 'Jamuna Future Park', 1, 'bike', 25.00),
(55, 'J5', 'Jamuna Future Park', 1, 'bike', 25.00),
(56, 'J6', 'Jamuna Future Park', 1, 'bike', 25.00),
(57, 'J7', 'Jamuna Future Park', 1, 'bike', 25.00),
(58, 'J8', 'Jamuna Future Park', 1, 'bike', 25.00),
(59, 'J9', 'Jamuna Future Park', 1, 'bike', 25.00),
(60, 'J10', 'Jamuna Future Park', 1, 'bike', 25.00),
(61, 'J11', 'Jamuna Future Park', 1, 'bike', 25.00),
(62, 'J12', 'Jamuna Future Park', 1, 'bike', 25.00),
(63, 'J13', 'Jamuna Future Park', 1, 'bike', 25.00),
(64, 'J14', 'Jamuna Future Park', 1, 'bike', 25.00),
(65, 'J15', 'Jamuna Future Park', 1, 'bike', 25.00),
(66, 'J16', 'Jamuna Future Park', 1, 'bike', 25.00),
(67, 'J17', 'Jamuna Future Park', 1, 'bike', 25.00),
(68, 'J18', 'Jamuna Future Park', 1, 'bike', 25.00),
(69, 'J19', 'Jamuna Future Park', 1, 'bike', 25.00),
(70, 'J20', 'Jamuna Future Park', 1, 'bike', 25.00),
(71, 'J21', 'Jamuna Future Park', 1, 'bike', 25.00),
(72, 'J22', 'Jamuna Future Park', 1, 'bike', 25.00),
(73, 'J23', 'Jamuna Future Park', 1, 'bike', 25.00),
(74, 'J24', 'Jamuna Future Park', 1, 'bike', 25.00),
(75, 'J25', 'Jamuna Future Park', 1, 'bike', 25.00),
(101, 'K1', 'Jamuna Future Park', 1, 'car', 60.00),
(102, 'K2', 'Jamuna Future Park', 1, 'car', 60.00),
(103, 'K3', 'Jamuna Future Park', 1, 'car', 60.00),
(104, 'K4', 'Jamuna Future Park', 1, 'car', 60.00),
(105, 'K5', 'Jamuna Future Park', 1, 'car', 60.00),
(106, 'K6', 'Jamuna Future Park', 1, 'car', 60.00),
(107, 'K7', 'Jamuna Future Park', 1, 'car', 60.00),
(108, 'K8', 'Jamuna Future Park', 1, 'car', 60.00),
(109, 'K9', 'Jamuna Future Park', 1, 'car', 60.00),
(110, 'K10', 'Jamuna Future Park', 1, 'car', 60.00),
(111, 'K11', 'Jamuna Future Park', 1, 'car', 60.00),
(112, 'K12', 'Jamuna Future Park', 1, 'car', 60.00),
(113, 'K13', 'Jamuna Future Park', 1, 'car', 60.00),
(114, 'K14', 'Jamuna Future Park', 1, 'car', 60.00),
(115, 'K15', 'Jamuna Future Park', 1, 'car', 60.00),
(116, 'K16', 'Jamuna Future Park', 1, 'car', 60.00),
(117, 'K17', 'Jamuna Future Park', 1, 'car', 60.00),
(118, 'K18', 'Jamuna Future Park', 1, 'car', 60.00),
(119, 'K19', 'Jamuna Future Park', 1, 'car', 60.00),
(120, 'K20', 'Jamuna Future Park', 1, 'car', 60.00),
(121, 'K21', 'Jamuna Future Park', 1, 'car', 60.00),
(122, 'K22', 'Jamuna Future Park', 1, 'car', 60.00),
(123, 'K23', 'Jamuna Future Park', 1, 'car', 60.00),
(124, 'K24', 'Jamuna Future Park', 1, 'car', 60.00),
(125, 'K25', 'Jamuna Future Park', 1, 'car', 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'admin_shahriar', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(22, 'soumic', 'soumic@gmail.com', '$2y$10$lf7tzeqF9mkB.nnNTCo/8eNm02wd4VHxAAELwPTUmStMxndku05kG', 'Admin'),
(24, 'soumic', 'shahriar@gmail.com', '$2y$10$gNQBdA7gXzwkwrt.FBJh5eFFxFVEwJa62Zmy3H7tbm6qoyWrT1sbi', 'User'),
(25, 'shahriar', 'shahriar12@gmail.com', '$2y$10$CwwdMywjS6ZlnuwizDuq.eq.JuHv1X7YlTTSltbdM70F61/A/qXQS', 'User');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `a_bookings`
--
ALTER TABLE `a_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slot_id` (`slot_id`),
  ADD KEY `a_bookings_ibfk_1` (`user_id`);

--
-- Indexes for table `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slot_number` (`slot_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `a_bookings`
--
ALTER TABLE `a_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `parking_slots`
--
ALTER TABLE `parking_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `a_bookings`
--
ALTER TABLE `a_bookings`
  ADD CONSTRAINT `a_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `a_bookings_ibfk_2` FOREIGN KEY (`slot_id`) REFERENCES `parking_slots` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
