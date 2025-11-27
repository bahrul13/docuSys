-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 02:15 AM
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
-- Database: `docusys_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `copc`
--

CREATE TABLE `copc` (
  `id` int(11) NOT NULL,
  `program` varchar(255) NOT NULL,
  `issuance_date` date NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `copc`
--

INSERT INTO `copc` (`id`, `program`, `issuance_date`, `file_name`, `uploaded_at`) VALUES
(9, 'Master of Information Technology', '2025-08-04', 'AREA X-ADMINISTRATION.pdf', '2025-08-06'),
(10, 'BS in Information Technology', '2025-07-29', 'DBM-PriceList-as-of-090925.pdf', '2025-08-06'),
(11, 'Master of Science in Information Technology', '2025-08-01', 'DBM-PriceList-as-of-090925.pdf', '2025-08-06'),
(13, 'Master of Science in Information Technology', '2025-08-14', 'AREA X-ADMINISTRATION.pdf', '2025-08-06'),
(14, 'BS in Social Work', '2025-08-13', 'DBM-PriceList-as-of-090925.pdf', '2025-08-06'),
(16, 'Bachelor of Secondary Education', '2025-11-18', '69258f8a55a32-Graduate School Thesis Dissertation Policy Paper Capstone Format.pdf', '2025-11-25');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `document` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `document`, `file_name`, `uploaded_at`) VALUES
(3, 'MSI Level 1', 'Graduate School Thesis Dissertation Policy Paper Capstone Format.pdf', '2025-10-15'),
(4, 'MSI Level II', 'AREA X-ADMINISTRATION.pdf', '2025-10-15');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dept` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `name`, `dept`) VALUES
(9, 'BS in Information Technology', 'BSIT'),
(10, 'BS in Social Work', 'BSSW'),
(11, 'Master of Information Technology', 'MIT'),
(13, 'Master of Arts Political Science', ''),
(15, 'Bachelor of Secondary Education', ''),
(25, 'Master of Science in Information Technology', '');

-- --------------------------------------------------------

--
-- Table structure for table `sfr`
--

CREATE TABLE `sfr` (
  `id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `survey_type` varchar(255) NOT NULL,
  `survey_date` date NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `date_uploaded` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sfr`
--

INSERT INTO `sfr` (`id`, `program_name`, `survey_type`, `survey_date`, `file_name`, `date_uploaded`) VALUES
(5, 'Bachelor of Secondary Education', 'Level 4', '2025-09-03', 'DBM-PriceList-as-of-090925.pdf', '2025-10-15');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_logs`
--

CREATE TABLE `transaction_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `documents` varchar(255) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_logs`
--

INSERT INTO `transaction_logs` (`id`, `user_id`, `documents`, `record_id`, `action`, `description`, `log_time`) VALUES
(7, 1, 'user', 15, 'Delete User', 'Deleted User: Bahrul', '2025-11-26 11:22:30'),
(8, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:23:00'),
(9, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:23:43'),
(10, 14, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:24:27'),
(11, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:34:22'),
(12, 1, 'copc', 16, 'View Document', 'Viewed Document: ', '2025-11-26 11:36:57'),
(13, 1, 'copc', 16, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:38:08'),
(14, 1, 'sfr', 5, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:41:47'),
(15, 1, 'sfr', 5, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:42:13'),
(16, 1, 'copc', 16, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:42:19'),
(17, 1, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:44:50'),
(18, 1, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:47:10'),
(19, 1, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:49:36'),
(20, 1, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:50:03'),
(21, 1, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:50:21'),
(22, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:50:58'),
(23, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:51:50'),
(24, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:52:30'),
(25, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:52:37'),
(26, 1, 'documents', 3, 'Update Document', 'Updated Document record: MSI Level 1. ', '2025-11-26 11:52:50'),
(27, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-26 11:52:51'),
(28, 1, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:53:27'),
(29, 1, 'sfr', 5, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:54:30'),
(30, 1, 'copc', 16, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:55:27'),
(31, 1, 'copc', 16, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 11:55:50'),
(32, 1, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:10:58'),
(33, 14, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 12:11:37'),
(34, 14, 'copc', 16, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-26 12:11:55'),
(35, 14, 'copc', 13, 'View Document', 'Viewed Document: Master of Science in Information Technology', '2025-11-26 12:11:59'),
(36, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:12:05'),
(37, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:13:21'),
(38, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:13:37'),
(39, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:13:46'),
(40, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:13:51'),
(41, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:13:55'),
(42, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:14:12'),
(43, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:14:19'),
(44, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:14:31'),
(45, 14, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:15:20'),
(46, 1, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-26 12:15:54'),
(47, 1, 'sfr', 5, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-27 00:19:44'),
(48, 1, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-27 00:19:55'),
(49, 1, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-27 00:20:00'),
(50, 1, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-27 00:24:50'),
(51, 1, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-27 00:26:17'),
(52, 1, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-27 00:28:08'),
(53, 1, 'documents', 3, 'View Document', 'Viewed Document: MSI Level 1', '2025-11-27 00:28:19'),
(54, 1, 'documents', 4, 'View Document', 'Viewed Document: MSI Level II', '2025-11-27 00:28:32'),
(55, 1, 'copc', 16, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-27 00:29:18'),
(56, 1, 'sfr', 5, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-27 00:29:56'),
(57, 1, 'trba', 3, 'View Document', 'Viewed Document: Bachelor of Secondary Education', '2025-11-27 00:31:04'),
(58, 1, 'auth', 1, 'logout', 'User logged out', '2025-11-27 00:59:05'),
(59, 14, 'auth', 14, 'login', 'User logged in', '2025-11-27 01:02:11'),
(60, 14, 'auth', 14, 'logout', 'User logged out', '2025-11-27 01:02:13'),
(61, 1, 'auth', 1, 'login', 'User logged in', '2025-11-27 01:02:24'),
(62, 1, 'auth', 1, 'logout', 'User logged out: ', '2025-11-27 01:06:58'),
(63, 1, 'auth', 1, 'login', 'User logged in: QMSO', '2025-11-27 01:07:43'),
(64, 1, 'auth', 1, 'logout', 'User logged out: QMSO', '2025-11-27 01:07:47'),
(65, 1, 'auth', 1, 'login', 'User logged in: QMSO', '2025-11-27 01:13:52'),
(66, 1, 'auth', 1, 'logout', 'User logged out: qmso@cotsu.edu.ph', '2025-11-27 01:13:56');

-- --------------------------------------------------------

--
-- Table structure for table `trba`
--

CREATE TABLE `trba` (
  `id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `survey_type` varchar(255) NOT NULL,
  `survey_date` date NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `date_uploaded` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trba`
--

INSERT INTO `trba` (`id`, `program_name`, `survey_type`, `survey_date`, `file_name`, `date_uploaded`) VALUES
(3, 'Bachelor of Secondary Education', 'Level 2', '2025-09-01', 'DBM-PriceList-as-of-090925.pdf', '2025-09-24');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fullname`, `email`, `password`, `role`, `reset_token`, `reset_expiry`, `date_created`) VALUES
(1, 'QMSO', 'qmso@cotsu.edu.ph', '$2y$10$DvYD.6ITv63S.r252yZY7utP9MWorcYOpJ0cLD5eO.3VG/NgXS/Re', 'admin', NULL, NULL, '2025-11-26'),
(14, 'mamako', 'sad@email.com', '$2y$10$VRpoGJo4QEs.ZgziIy6TAuW2UuFCpeK4uEA138lbkDYtKErHE7lRi', 'user', NULL, NULL, '2025-11-26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `copc`
--
ALTER TABLE `copc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sfr`
--
ALTER TABLE `sfr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trba`
--
ALTER TABLE `trba`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `copc`
--
ALTER TABLE `copc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `sfr`
--
ALTER TABLE `sfr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `trba`
--
ALTER TABLE `trba`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaction_logs`
--
ALTER TABLE `transaction_logs`
  ADD CONSTRAINT `transaction_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
