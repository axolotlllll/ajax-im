-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2025 at 02:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') NOT NULL,
  `course` varchar(100) NOT NULL,
  `user_address` text DEFAULT NULL,
  `birthdate` date NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) DEFAULT NULL,
  `verification_code` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `email`, `gender`, `course`, `user_address`, `birthdate`, `profile_image`, `date_created`, `password`, `verification_code`, `is_verified`) VALUES
(40, 'mcmcmcm', 'dawton', 'asdas@asdas', 'Male', 'bsit', 'nipa', '2014-12-29', 'profiles/1743841888_FB_IMG_1742197423816.jpg', '2025-04-05 08:23:19', NULL, NULL, 0),
(41, 'fofofo', 'wawa', 'dsfds@lajqsl', 'Female', 'ccs', 'aaaa', '2016-01-05', 'profiles/1743842147_473212605_587462087248800_6558220378730490478_n.jpg', '2025-04-05 08:35:47', NULL, NULL, 0),
(42, 'mcjoe', 'dawaton', 'fattynigel@gmail.com', 'Male', 'bsits', 'nipa', '2002-01-12', '67fa51d74b7d5_C1hbTAT.jpg', '2025-04-12 11:43:19', '$2y$10$DBP58eRQP4vG2nEcIuroTe3hcLVbzQ67Ce4Zq8smjH/8rBzqIV83K', '258bbb3c0f81b7c3156802b48a4008b4', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verification_code` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `student_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') NOT NULL,
  `course` varchar(255) NOT NULL,
  `user_address` varchar(255) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default-profile.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `verification_code`, `is_verified`, `student_id`, `created_at`, `first_name`, `last_name`, `gender`, `course`, `user_address`, `birthdate`, `profile_image`) VALUES
(12, 'emma.rodriguez@example.com', '$2y$10$randomhashpassword1', NULL, 0, NULL, '2025-04-05 07:29:30', 'Emma', 'Rodriguez', 'Female', 'Computer Science', '123 Tech Lane, San Francisco, CA 94105', '2002-03-15', 'default-profile.jpg'),
(13, 'liam.chen@example.com', '$2y$10$randomhashpassword2', NULL, 0, NULL, '2025-04-05 07:29:30', 'Liam', 'Chen', 'Male', 'Data Science', '456 Innovation Road, Seattle, WA 98101', '2001-07-22', 'default-profile.jpg'),
(14, 'sophia.patel@example.com', '$2y$10$randomhashpassword3', NULL, 0, NULL, '2025-04-05 07:29:30', 'Sophia', 'Patel', 'Female', 'Artificial Intelligence', '789 Machine Learning Street, Boston, MA 02108', '2003-11-05', 'default-profile.jpg'),
(15, 'noah.williams@example.com', '$2y$10$randomhashpassword4', NULL, 0, NULL, '2025-04-05 07:29:30', 'Noah', 'Williams', 'Male', 'Cybersecurity', '321 Network Avenue, Austin, TX 78701', '2000-09-18', 'default-profile.jpg'),
(16, 'isabella.martinez@example.com', '$2y$10$randomhashpassword5', NULL, 0, NULL, '2025-04-05 07:29:30', 'Isabella', 'Martinez', 'Female', 'Software Engineering', '654 Code Street, Chicago, IL 60601', '2002-05-30', 'default-profile.jpg'),
(18, 'fattynigel@gmail.com', '$2y$10$enrTujfhIAgHYs4KerjUDex9147kVh4zWunQm9lkyXK3HbJEKpMR2', NULL, 1, NULL, '2025-04-12 11:57:47', 'mcjd', 'dawaton', 'Male', 'bsits', 'nipa', '2010-01-12', 'profiles/67fa55546b540_482114560_985072556586423_5985952189527056320_n.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
