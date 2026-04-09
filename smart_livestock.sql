-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 07:56 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_livestock`
--

-- --------------------------------------------------------

--
-- Table structure for table `animals`
--

CREATE TABLE `animals` (
  `tagId` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `animalType` varchar(50) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `breed` varchar(80) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `isPregnant` tinyint(1) DEFAULT 0,
  `isSick` tinyint(1) DEFAULT 0,
  `ownerContact` varchar(20) NOT NULL,
  `ownerName` varchar(100) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `animals`
--

INSERT INTO `animals` (`tagId`, `name`, `animalType`, `sex`, `breed`, `birthdate`, `isPregnant`, `isSick`, `ownerContact`, `ownerName`, `createdAt`, `updatedAt`) VALUES
('034F3F1A', 'kwizera jaiid', 'Imbwebwe (Dog)', 'Male', 'Local / Mixed', '2026-04-15', 0, 1, '0000000000', 'ignace imboni', '2026-04-09 17:20:35', '2026-04-09 17:22:07'),
('13166F09', 'Rwema Davi', 'Ihene (Goat)', 'Male', 'Alpine', '2026-04-10', 0, 0, '0789999999', 'kalagwa', '2026-04-08 11:46:05', '2026-04-08 11:46:05'),
('23635909', 'isine', 'Urukwavu (Rabbit)', 'Female', 'Californian', '2026-04-03', 0, 1, '0000000000', 'stevev bob', '2026-04-09 17:46:03', '2026-04-09 17:49:19'),
('735677FA', 'musheru', 'Inzovu (Elephant)', 'Male', 'African Bush', '2026-04-24', 0, 0, '0798789898', 'noeli', '2026-04-09 17:42:51', '2026-04-09 17:48:08'),
('D334F419', 'bihogo', 'Intama (Sheep)', 'Male', 'Suffolk', '2026-04-03', 0, 0, '0789999898', 'pedens', '2026-04-09 17:22:54', '2026-04-09 17:38:29'),
('E389681A', 'inyambo', 'Inka (Cow)', 'Female', 'Ankole / Inyambo', '2026-04-09', 1, 0, '0789999999', 'kwizera', '2026-04-08 12:08:16', '2026-04-08 14:34:46'),
('F34E01F8', 'bihogo', 'Inka (Cow)', 'Female', '', '0000-00-00', 1, 1, '0789999999', 'pedens', '2026-04-08 12:51:02', '2026-04-08 12:51:02');

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `id` int(11) NOT NULL,
  `tagId` varchar(50) NOT NULL,
  `type` enum('vaccination','pregnancy','disease') NOT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `nextEventDate` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `vetName` varchar(100) DEFAULT NULL,
  `vetContact` varchar(20) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`id`, `tagId`, `type`, `startDate`, `endDate`, `nextEventDate`, `notes`, `vetName`, `vetContact`, `createdAt`) VALUES
(14, '735677FA', 'disease', '2026-04-10', '2026-04-11', '2026-04-10', 'ohhhhh urarwaye', 'fausitin ', '0789654321', '2026-04-09 17:45:31');

-- --------------------------------------------------------

--
-- Table structure for table `pending_tags`
--

CREATE TABLE `pending_tags` (
  `id` int(11) NOT NULL,
  `tagId` varchar(50) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scan_logs`
--

CREATE TABLE `scan_logs` (
  `id` int(11) NOT NULL,
  `tagId` varchar(50) NOT NULL,
  `scannedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `deviceId` varchar(50) DEFAULT NULL,
  `status` enum('FOUND','NOT_FOUND','OFFLINE') DEFAULT 'FOUND'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_mode`
--

CREATE TABLE `system_mode` (
  `id` int(11) NOT NULL DEFAULT 1,
  `registerMode` tinyint(1) DEFAULT 0,
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_mode`
--

INSERT INTO `system_mode` (`id`, `registerMode`, `updatedAt`) VALUES
(1, 1, '2026-04-09 17:42:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user','veterinarian','owner') NOT NULL DEFAULT 'owner',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullName`, `username`, `email`, `phone`, `password`, `role`, `status`, `createdAt`) VALUES
(1, 'Kwizera Jaiid', 'kwizerajaiid', 'kwizera@gmail.com', '+250788000000', '$2y$10$Y8ReAvgcQHg8wjVfST16Uu.n0xlPW6rtZGKnxYIfWWmUd/6p7XBu2', 'admin', 'approved', '2026-04-02 09:43:11'),
(2, 'Rwema Davi', 'karangwa', 'rwemadavi@gmail.com', '0793620486', '$2y$10$CNzy.1.78kvcHe.2opcDEuDgdF4oiee6Sj1rR2R0TnzOT6fSLZ1hW', 'veterinarian', 'approved', '2026-04-06 12:44:00'),
(3, 'Rwema Davi', 'makaka', 'rwemadav@gmail.com', '0793620486', '$2y$10$aSzM5ZjJnDOyJ4hjCsx69u6Zl2kwJ2EN22Dc7dRwSeev002guE3Hq', 'owner', 'approved', '2026-04-06 13:15:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_requests`
--

CREATE TABLE `user_requests` (
  `id` int(11) NOT NULL,
  `fullName` varchar(120) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `role` enum('veterinarian','owner') DEFAULT 'owner',
  `password` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `reviewedBy` int(11) DEFAULT NULL,
  `reviewedAt` timestamp NULL DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_requests`
--

INSERT INTO `user_requests` (`id`, `fullName`, `username`, `email`, `phone`, `role`, `password`, `reason`, `status`, `reviewedBy`, `reviewedAt`, `createdAt`) VALUES
(1, 'makaka david', 'makaka', 'makaka@gmail.com', '0793620486', 'owner', '$2y$10$BwX4otQm.DRQOTgWj.JEceAIipxN1siiGNlq2UXFkuErCveF2dAHm', 'ndi nyirazo adminwee!', 'REJECTED', NULL, NULL, '2026-04-06 11:51:46'),
(2, 'Rwema Davi', 'makaka', 'rwemadavi@gmail.com', '0793620486', 'owner', '$2y$10$byzqrpsqPTDIMngExJBzHuOetUbrV48AL1peDfwo7cn4v.J/ODidG', 'saas', 'REJECTED', NULL, NULL, '2026-04-06 12:31:10'),
(3, 'makama dav', 'makaka', 'rwemadavi@gmail.com', '0785567878', 'owner', '$2y$10$1x96VmKKwhP8PynUzQvyN.6c1qdFVX7fpCWr63ga7URFyTWKuomxK', 'ndi muganga', 'REJECTED', NULL, NULL, '2026-04-06 12:38:49'),
(4, 'Rwema Davi', 'karangwa', 'rwemadavi@gmail.com', '0793620486', 'veterinarian', '$2y$10$CNzy.1.78kvcHe.2opcDEuDgdF4oiee6Sj1rR2R0TnzOT6fSLZ1hW', 'ndi muganga', 'APPROVED', 1, '2026-04-06 12:44:00', '2026-04-06 12:43:15'),
(5, 'Rwema Davi', 'makaka', 'rwemadav@gmail.com', '0793620486', 'owner', '$2y$10$aSzM5ZjJnDOyJ4hjCsx69u6Zl2kwJ2EN22Dc7dRwSeev002guE3Hq', 'ndi nyirazo', 'APPROVED', 1, '2026-04-06 13:15:16', '2026-04-06 13:14:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `animals`
--
ALTER TABLE `animals`
  ADD PRIMARY KEY (`tagId`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_health_tag` (`tagId`);

--
-- Indexes for table `pending_tags`
--
ALTER TABLE `pending_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tagId` (`tagId`);

--
-- Indexes for table `scan_logs`
--
ALTER TABLE `scan_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scan_tag` (`tagId`),
  ADD KEY `idx_scan_time` (`scannedAt`);

--
-- Indexes for table `system_mode`
--
ALTER TABLE `system_mode`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_requests`
--
ALTER TABLE `user_requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pending_tags`
--
ALTER TABLE `pending_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `scan_logs`
--
ALTER TABLE `scan_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_requests`
--
ALTER TABLE `user_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `fk_health_tag` FOREIGN KEY (`tagId`) REFERENCES `animals` (`tagId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
