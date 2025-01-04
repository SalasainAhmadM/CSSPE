-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2025 at 01:40 PM
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
(6, 'Nzro Day', 'nice', '2024-12-14 18:33:42', '     niceasdas'),
(8, 'Yes', 'hahahahahha', '2024-12-17 18:49:24', 'zamboanga city'),
(9, 'yesssssssssssssss', 'hahhahahha', '2024-12-17 19:21:39', 'zamboanga city'),
(10, 'yes', 'dd', '2024-12-17 20:01:12', 'zamboanga cityrr'),
(11, 'asd', 'qwew', '2024-12-17 20:07:33', 'zamboanga cityeqwewe'),
(12, 'gahahhhha', 'dasdas', '2024-12-17 20:15:49', 'hhaha'),
(13, 'dasd', 'sada', '2024-12-17 20:18:51', 'zamboanga city'),
(14, 'NICEEEEEEEEEEEEEEEe', 'eeeeeee', '2024-12-17 20:19:18', 'zamboanga city');

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
(21, 'College of Architecture', 'yes', '../assets/img/CSSPE.png');

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
(3, 'bice', 'yes', '2024-12-01 18:07:28'),
(4, 'nice', 'yes', '2024-12-01 18:07:28');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `type` enum('origin','brand') NOT NULL DEFAULT 'origin',
  `quantity` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `users_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `brand`, `type`, `quantity`, `created_at`, `updated_at`, `users_id`, `image`) VALUES
(1, 'Earth', 'qwerty', 'Orig', 'origin', '12', '2024-12-28 16:32:52', '0000-00-00 00:00:00', 25, '677176e8106c3-69ce7c36886481c490338f7465e00bd9.png'),
(2, 'Earth', 'qwerty 2', 'local', 'brand', '12', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(3, 'Earth', 'asdasdasd', '22', 'brand', '123', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(4, 'qwerty', 'asdasdasd', '22', 'origin', '123', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(5, 'asdfg', 'asdasdasd', '22', 'origin', '123', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(6, 'sdfsvsggv', 'asdasdasd', '22', 'origin', '123', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(7, 'qwertyqwerty', 'asdasdasd', '22', 'origin', '123', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(8, 'Item test', 'new', 'orig', 'origin', '12', '2024-12-29 16:55:36', '0000-00-00 00:00:00', 25, NULL),
(9, '123', 'qwewe', '1211', 'origin', '123', '2024-12-29 17:07:29', '0000-00-00 00:00:00', 25, '67711151698a1-akagi.jpg'),
(10, 'we', '123123', 'qw', 'origin', '12', '2024-12-29 17:09:34', '0000-00-00 00:00:00', 25, '677111da9bf07-bini.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `item_transactions`
--

CREATE TABLE `item_transactions` (
  `transaction_id` int(11) NOT NULL,
  `quantity_borrowed` int(11) NOT NULL,
  `quantity_returned` int(11) DEFAULT NULL,
  `borrowed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `returned_at` datetime DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `schedule_from` time NOT NULL,
  `schedule_to` time NOT NULL,
  `status` enum('Pending','Approved') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_transactions`
--

INSERT INTO `item_transactions` (`transaction_id`, `quantity_borrowed`, `quantity_returned`, `borrowed_at`, `returned_at`, `item_id`, `users_id`, `schedule_from`, `schedule_to`, `status`) VALUES
(1, 2, NULL, '2025-01-04 00:00:00', NULL, 4, 47, '00:00:19', '19:40:00', 'Pending'),
(2, 3, NULL, '2025-01-31 00:00:00', NULL, 2, 10, '00:00:19', '19:42:00', 'Pending'),
(3, 4, NULL, '2025-01-17 20:07:04', NULL, 1, 47, '00:00:20', '20:13:00', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `memorandums`
--

CREATE TABLE `memorandums` (
  `id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memorandums`
--

INSERT INTO `memorandums` (`id`, `file_path`, `title`, `uploaded_at`, `updated_at`, `description`) VALUES
(9, '../assets/uploads/6761741983914_Mukaram_CV.pdf', 'qwewew1', '2024-12-17 05:52:41', '2024-12-30 02:01:52', 'qweqw1'),
(15, '../assets/uploads/6761bb7f03e7d_CANDEN ATTACHMENTS-merged.pdf', 'd', '0000-00-00 00:00:00', '2024-12-30 02:01:57', 'd'),
(16, '../assets/uploads/6761bbdab8b5e_1576632750_1568344676_Customer Request Form (Front) (1) (2).pdf', 'hahhaha', '2024-12-17 10:58:50', NULL, 'yesssssssssssssssssss'),
(17, '../assets/uploads/6761c11f6267f_Mukaram_CV.pdf', 'yes', '2024-12-17 11:21:19', NULL, 'yesssssssssssss'),
(18, '../assets/uploads/6771850e33494_qwer.pdf', 'test', '2024-12-29 10:21:18', NULL, '123'),
(19, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(20, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(21, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(22, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(23, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(24, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2025-01-02 10:33:43', NULL, '123');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('Memorandums','Announcements') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `description`, `uploaded_at`, `type`, `is_read`) VALUES
(3, 'yes', 'yesssssssssssss', '2024-12-17 11:21:19', 'Announcements', 1),
(4, 'yesssssssssssssss', 'hahhahahha', '2024-12-17 11:21:39', 'Memorandums', 1),
(5, 'yes', 'dd', '2024-12-17 12:01:12', 'Announcements', 1),
(6, 'asd', 'qwew', '2024-12-17 12:07:33', 'Announcements', 1),
(7, 'gahahhhha', 'dasdas', '2024-12-17 12:15:49', 'Announcements', 1),
(8, 'dasd', 'sada', '2024-12-17 12:18:51', 'Announcements', 1),
(9, 'NICEEEEEEEEEEEEEEEe', 'eeeeeee', '2024-12-17 12:19:18', 'Announcements', 1),
(10, 'test', '123', '2024-12-29 10:21:18', 'Memorandums', 0),
(11, 'test123', '123', '2024-12-29 10:33:43', 'Memorandums', 0);

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
(14, 'SWORD', 'ONE PIECE', '../assets/img/676ec35d69589.jpg');

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
(10, 'Nadeer', 'Mukaram', 'R', 'nzro12@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Instructor', '$2y$10$uHsRU8jwklQdKXBAULkATed9wj81iLjVQAZfwFfnbGUYs0imEROx.', '2024-12-08 12:19:51', 'instructor', NULL, 'College of Architecture'),
(20, 'Casca', 'Nad', 'R', 'cascanad@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', '', '$2y$10$b.eMiQeyKdy4qUG9rT/TB.w3o0Vmte53YbeA6WpQNnZF5LMTaHlTC', '2024-12-08 14:46:05', 'super_admin', NULL, 'College of Architecture'),
(25, 'Invent', 'Tory', 'D', 'test1@gmail.com', '12391', '5414141291', 'Instructor', '$2y$10$Ms8QUPjUWubmuwL9yJa7iu4tq7PsRrYIucPYl631hmtJb6LOLzw4G', '2024-12-08 15:22:56', 'inventory_admin', 'akagi.jpg', 'College of Architecture'),
(47, 'Casca', 'Nadeer', 'R', 'nzro@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Professor', '$2y$10$bgIY43.lS5YGOvMl2NajUea8PW8ntj357oYFGcpzbAGCubL9x0Wcm', '2024-12-12 15:41:00', 'instructor', '676ec35d69589.jpg', ''),
(48, 'Nadeerrrrrr', 'Re', 'Mukaramrrrrrrrrrrrrrrr', 'zoro0wqewqewqe000@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '12312312', 'Associate Professor', '$2y$10$jWoAT.aYeDvyj4i6VR6VNewlHLd.yEsrN9bCbur.bh5a1BE.vhgzS', '2024-12-13 15:52:27', 'Instructor', '', 'College of Architecture'),
(49, 'Casca', 'Nadeer', 'R', 'nzro123@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Instructor', '$2y$10$5LWJBHQEntvz6IvYzItoPuWySZiRMYOBjbFX1HoBx3vdtoismhJCW', '2024-12-12 15:41:00', 'instructor', '', 'College of Architecture'),
(56, 'Nzroooo', 'Mkrmoooo', 'R', 'nadeer12345@gmail.com', 'Rio Hondo', '213123', 'Instructor', '$2y$10$5LWJBHQEntvz6IvYzItoPuWySZiRMYOBjbFX1HoBx3vdtoismhJCW', '2024-12-17 18:03:21', 'information_admin', 'CSSPE.png', 'College of Architecture');

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
-- Indexes for table `item_transactions`
--
ALTER TABLE `item_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_item_transactions_item` (`item_id`),
  ADD KEY `fk_item_transactions_users` (`users_id`);

--
-- Indexes for table `memorandums`
--
ALTER TABLE `memorandums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `item_transactions`
--
ALTER TABLE `item_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `memorandums`
--
ALTER TABLE `memorandums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pending_users`
--
ALTER TABLE `pending_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `item_transactions`
--
ALTER TABLE `item_transactions`
  ADD CONSTRAINT `fk_item_transactions_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_transactions_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
