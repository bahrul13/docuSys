-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 02:10 PM
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
(3, 'MSI Level 1', '68ef085d72be7-AREA X-ADMINISTRATION.pdf', '2025-10-15'),
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
(7, 'QMSO', 'qmso@cotsu.edu.ph', '$2y$10$4APiI45P4sOtpoA2j21jIuT7eJfTA488oOi0EyvPIUwjyOMKgqDl2', 'admin', NULL, NULL, '2025-11-25');

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
-- AUTO_INCREMENT for table `trba`
--
ALTER TABLE `trba`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
