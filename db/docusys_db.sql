-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2025 at 08:08 AM
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
(9, 'Master of Information Technology', '2025-08-04', '6892ac446c218-Dumpao_L. 2x2 pic.pdf', '2025-08-06'),
(10, 'BS in Information Technology', '2025-07-27', '6892b06885009-Dumpao_L. 2x2 pic.pdf', '2025-08-06'),
(11, 'Master of Science in Information Technology', '2025-08-01', '6892b3cc99abe-Dumpao_L. 2x2 pic.pdf', '2025-08-06'),
(13, 'Master of Science in Information Technology', '2025-08-14', '68934f3ce15a2-Dumpao_L. 2x2 pic.pdf', '2025-08-06'),
(14, 'BS in Social Work', '2025-08-13', '68934f5f45e72-Dumpao_L. 2x2 pic.pdf', '2025-08-06');

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
(2, 'Bachelor of Secondary Education', 'Level 2', '2025-08-01', '689d72af59975-Dumpao_L. 2x2 pic.pdf', '2025-08-14'),
(4, 'Master of Information Technology', 'Level 4', '2025-07-27', '689d794853871-Dumpao_L. 2x2 pic.pdf', '2025-08-14');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fullname`, `email`, `password`, `role`, `reset_token`, `reset_expiry`) VALUES
(6, 'Bahrul', 'ungadbahrul94@gmail.com', '$2y$10$XJvY982okMLUDrW95CG2COpG.Y.Vea32A91gDYdo7YkOIa.x540py', 'admin', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `copc`
--
ALTER TABLE `copc`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `sfr`
--
ALTER TABLE `sfr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
