-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 06:15 AM
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
-- Database: `lisa`
--

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `student_id` varchar(64) NOT NULL,
  `book_id` varchar(64) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `borrow_date` date NOT NULL,
  `return_date` date NOT NULL,
  `status` varchar(16) NOT NULL,
  `flag` varchar(8) DEFAULT NULL,
  `location` varchar(32) DEFAULT NULL,
  `penalty_paid` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `student_id`, `book_id`, `transaction_date`, `borrow_date`, `return_date`, `status`, `flag`, `location`, `penalty_paid`) VALUES
(11, '11111111', '01237582', '2025-09-25 20:32:43', '2025-09-25', '2025-10-02', 'Returned', 'DONE', 'TableA', 0),
(12, '11111111', '09162553', '2025-09-25 23:13:46', '2025-09-25', '2025-10-02', 'Borrowed', 'DONE', 'TableA', 0),
(13, '11111111', '09875533', '2025-09-25 23:47:29', '2025-09-25', '2025-10-02', 'Borrowed', 'DONE', 'TableA', 0),
(14, '11111111', '09162553', '2025-09-28 15:34:48', '2025-09-25', '2025-10-02', 'Returned', 'DONE', 'TableA', 0),
(15, '11111111', '01237582', '2025-09-28 15:39:36', '2025-09-28', '2025-10-05', 'Borrowed', 'DONE', 'TableA', 0),
(16, '11111111', '01237582', '2025-09-28 16:54:42', '2025-09-28', '2025-10-05', 'Returned', 'DONE', 'TableA', 0),
(17, '11111111', '09875533', '2025-09-29 18:48:02', '2025-09-25', '2025-10-02', 'Returned', 'DONE', 'TableA', 0),
(18, '11111111', '01237582', '2025-09-29 21:15:46', '2025-09-29', '2025-10-06', 'Borrowed', 'DONE', 'TableA', 0),
(19, '11111111', '01237582', '2025-09-30 09:46:42', '2025-09-29', '2025-10-06', 'Returned', 'DONE', 'TableA', 0),
(20, '11111111', '01237582', '2025-09-30 17:57:39', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableA', 0),
(21, '11111111', '09162553', '2025-09-30 17:57:40', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableA', 0),
(22, '11111111', '09875533', '2025-09-30 19:18:49', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableA', 0),
(23, '11111111', '09162553', '2025-09-30 19:19:11', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableA', 0),
(24, '11111111', '01237582', '2025-09-30 18:23:17', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableA', 0),
(25, '11111111', '01237582', '2025-09-30 19:19:18', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableA', 0),
(26, '11111111', '01237582', '2025-09-30 19:28:20', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableA', 0),
(27, '11111111', '09875533', '2025-09-30 19:28:21', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableA', 0),
(28, '11111111', '87192445', '2025-09-30 19:28:04', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableA', 0),
(29, '11111111', '01237582', '2025-10-01 00:25:05', '2025-09-30', '2025-10-07', 'Borrowed', 'PENDING', 'TableA', 0),
(30, '11111111', '09162553', '2025-10-01 00:09:28', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableA', 0),
(31, '22222222', '09875533', '2025-09-30 23:43:33', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableB', 0),
(32, '22222222', '12311678', '2025-09-30 23:43:35', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableB', 0),
(33, '22222222', '12345678', '2025-09-30 23:43:34', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableB', 0),
(34, '11111111', '87192445', '2025-09-30 23:43:35', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableA', 0),
(35, '22222222', '09875533', '2025-10-01 00:09:33', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableB', 0),
(36, '11111111', '09875533', '2025-10-01 00:25:07', '2025-10-01', '2025-10-08', 'Borrowed', 'DONE', 'TableA', 0),
(37, '22222222', '12345678', '2025-10-01 00:25:06', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableB', 0),
(38, '11111111', '09162553', '2025-10-01 00:26:49', '2025-09-30', '2025-10-07', 'Borrowed', 'DONE', 'TableA', 0),
(39, '11111111', '09875533', '2025-10-01 16:13:45', '2025-10-01', '2025-10-08', 'Returned', 'DONE', 'TableA', 0),
(40, '11111111', '09162553', '2025-10-01 16:13:46', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableA', 0),
(41, '00000000', '09162553', '2025-10-01 18:42:16', '2025-10-01', '2025-10-08', 'Borrowed', 'PENDING', 'TableA', 0),
(42, '00000000', '09875533', '2025-10-01 18:42:17', '2025-10-01', '2025-10-08', 'Borrowed', 'PENDING', 'TableA', 0),
(43, '22222222', '12311678', '2025-10-01 18:45:15', '2025-09-30', '2025-10-07', 'Returned', 'DONE', 'TableB', 0),
(44, '11111111', '12311678', '2025-10-02 10:56:21', '2025-10-02', '2025-10-09', 'Borrowed', 'PENDING', 'TableA', 0),
(45, '11111111', '12345678', '2025-10-13 18:54:36', '2025-10-02', '2025-10-09', 'Borrowed', 'DONE', 'TableA', 0),
(46, '11111111', '12345678', '2025-10-13 18:55:15', '2025-10-02', '2025-10-09', 'Fetching', 'ACTIVE', 'TableA', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
