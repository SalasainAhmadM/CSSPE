-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2024 at 01:04 PM
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
-- Database: `csspe`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_uploaded_at` datetime DEFAULT current_timestamp(),
  `location` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `description`, `date_uploaded_at`, `location`) VALUES
(6, 'Nzro Day', 'nice', '2024-12-14 18:33:42', '     niceasdas');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `description`, `image`) VALUES
(18, 'qwe', 'qwe', '../assets/img/675c709414b53.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `date_uploaded_at`) VALUES
(3, 'bice', 'yes', '2024-12-01 18:34:25');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `users_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` int(11) NOT NULL,
  `organization_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `organization_name`, `description`, `image`) VALUES
(10, 'qwed', 'qweas', '../assets/img/675c5398d477f.jpg'),
(11, 'qwed', '123', '../assets/img/675c6ef4eed31.png');

-- --------------------------------------------------------

--
-- Table structure for table `pending_users`
--

CREATE TABLE `pending_users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `rank` enum('Instructor','Assistant Professor','Associate Professor','Professor') NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(50) NOT NULL DEFAULT 'Instructor',
  `image` varchar(255) DEFAULT NULL,
  `department` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `rank` enum('Instructor','Assistant Professor','Associate Professor','Professor') NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(50) NOT NULL DEFAULT 'Instructor',
  `image` varchar(255) DEFAULT NULL,
  `department` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `middle_name`, `email`, `address`, `contact_no`, `rank`, `password`, `created_at`, `role`, `image`, `department`) VALUES
(10, 'Nadeer', 'Mukaram', 'R', 'nzro12@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Instructor', '$2y$10$uHsRU8jwklQdKXBAULkATed9wj81iLjVQAZfwFfnbGUYs0imEROx.', '2024-12-08 12:19:51', 'instructor', NULL , 'College of Architecture'),
(20, 'Casca', 'Nad', 'R', 'cascanad@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', '', '$2y$10$b.eMiQeyKdy4qUG9rT/TB.w3o0Vmte53YbeA6WpQNnZF5LMTaHlTC', '2024-12-08 14:46:05', 'super_admin', NULL, 'College of Architecture'),
(25, 'Invent', 'Tory', 'D', 'test@gmail.com', '123', '54141412', '', '$2y$10$Ms8QUPjUWubmuwL9yJa7iu4tq7PsRrYIucPYl631hmtJb6LOLzw4G', '2024-12-08 15:22:56', 'inventory_admin', NULL, 'College of Architecture'),
(47, 'Casca', 'Nadeer', 'R', 'nzro@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Instructor', '$2y$10$bgIY43.lS5YGOvMl2NajUea8PW8ntj357oYFGcpzbAGCubL9x0Wcm', '2024-12-12 15:41:00', 'instructor', '675c6f1032a0f.jpg', ''),
(48, 'Nadeerrrrrr', 'Re', 'Mukaramrrrrrrrrrrrrrrr', 'zoro0wqewqewqe000@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '12312312', 'Associate Professor', '$2y$10$jWoAT.aYeDvyj4i6VR6VNewlHLd.yEsrN9bCbur.bh5a1BE.vhgzS', '2024-12-13 15:52:27', 'Instructor', '675c66c8928f6.jpg', 'College of Architecture');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_items_users` (`users_id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pending_users`
--
ALTER TABLE `pending_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pending_users`
--
ALTER TABLE `pending_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
