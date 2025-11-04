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
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(64) NOT NULL,
  `rfid_key` varchar(64) NOT NULL,
  `name` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `course` varchar(128) NOT NULL,
  `membership_data` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `rfid_key`, `name`, `email`, `course`, `membership_data`, `password`) VALUES
('00000000', '2459782947', 'Joe De Ala', 'joemanueldeala021@gmail.com', 'BS ECE', '2025-10-01 10:29:16', '$2y$10$JKd/xiTct9gdRWOrawb8buUuO9ZGAM6GJbc0Y5y9XfnBwBP239YSS'),
('00044422', '98990992', 'Hello World', 'hellow@gmail.com', 'BS CS', '2025-09-17 10:22:58', '$2y$10$D8FxZfaegjQF2l7Vv5afZOqnrs7.YqnXrUBZBSlycrVHB1cThebCy'),
('09844222', '11111111', 'Jef Bonyad', 'jefbon@gmail.com', 'BS ECE', '2025-09-17 02:33:50', '$2y$10$nkiHUY4Bs4Nni3g46SilweMsaJ1WLDDmVbCngItsWt/fIX25N5xfi'),
('11111111', '12345678', 'Juan Dela Cruz', 'juandelacruz@gmail.co', 'BS ECE', '2025-09-16 10:54:51', '$2y$10$g5qqmw0bstk3RjD1eUDO6.blBt3WiwNTBfbAppEfOi4Ez13/dVROK'),
('22222222', '33333333', 'alyssa miranda', 'innovcentralph@gmail.com', 'BS IT', '2025-09-16 10:54:56', '$2y$10$8/SI2u5JJV/CR76gyO6sfOoqZjcOFi0YV7fqkvKOf.cpJ10kYLrha');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_id` (`rfid_key`),
  ADD UNIQUE KEY `email` (`email`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
