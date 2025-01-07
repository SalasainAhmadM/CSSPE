-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2025 at 07:05 AM
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
(8, 'Yes', 'hahahahahha', '2024-12-17 10:59:25', 'zamboanga city'),
(9, 'yesssssssssssssss', 'hahhahahha', '2024-12-17 19:21:39', 'zamboanga city'),
(10, 'yes', 'dd', '2024-12-17 20:01:12', 'zamboanga cityrr'),
(11, 'asd', 'qwew', '2024-12-17 20:07:33', 'zamboanga cityeqwewe'),
(12, 'gahahhhha', 'dasdas', '2024-12-17 20:15:49', 'hhaha'),
(13, 'dasd', 'sada', '2024-12-17 20:18:51', 'zamboanga city'),
(14, 'NICEEEEEEEEEEEEEEEe', 'eeeeeee', '2024-12-17 20:19:18', 'zamboanga city'),
(15, '123', '1qwe', '2025-01-05 10:38:52', '123');

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
(21, 'College of Architecture1', 'yes', '../assets/img/CSSPE.png');

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
(6, 'nice', 'yes', '2024-12-01 09:53:48'),
(7, '123', 'qwe', '2025-01-05 10:33:28');

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
  `quantity_origin` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `users_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `brand`, `type`, `quantity`, `quantity_origin`, `created_at`, `updated_at`, `users_id`, `image`) VALUES
(2, 'Earth', 'qwerty 2', 'local', 'brand', '21', '27', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(3, 'Earth', 'asdasdasd', '22', 'brand', '10', '120', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(5, 'asdfg', 'asdasdasd', '22', 'origin', '126', '3', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(6, 'sdfsvsggv', 'asdasdasd', '22', 'origin', '100', '123', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, NULL),
(9, '123', 'qwewe', '1211', 'origin', '123', '123', '2024-12-29 17:07:29', '0000-00-00 00:00:00', 25, '67711151698a1-akagi.jpg'),
(10, 'we', '123123', 'qw', 'origin', '12', '12', '2024-12-29 17:09:34', '0000-00-00 00:00:00', 25, '677111da9bf07-bini.jpg'),
(11, '121', '121', '12', 'origin', '12', '12', '2025-01-05 18:15:56', '0000-00-00 00:00:00', 25, NULL),
(12, 'w2e', '22', '2312', 'origin', '22', '22', '2025-01-05 18:19:06', '0000-00-00 00:00:00', 25, NULL),
(13, '11111111111111', 'csdasdasd', '111111dwdawsda', 'origin', '44', '4444', '2025-01-05 18:23:41', '0000-00-00 00:00:00', 25, '677a5dadae056-download.jpg'),
(14, 'test', 'real', 'orig', 'origin', '0', '22', '2025-01-06 15:45:13', '0000-00-00 00:00:00', 25, '677b8a093554d-karimagi.jpg'),
(15, 'testtst', 'jordan', 'qwerty', 'origin', '23', '23', '2025-01-06 15:54:10', '0000-00-00 00:00:00', 25, '677b8c2216f19-download (2).jpg');

-- --------------------------------------------------------

--
-- Table structure for table `item_transactions`
--

CREATE TABLE `item_transactions` (
  `transaction_id` int(11) NOT NULL,
  `quantity_borrowed` int(11) NOT NULL,
  `quantity_returned` int(11) DEFAULT NULL,
  `borrowed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `return_date` date DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `schedule_from` time NOT NULL,
  `schedule_to` time NOT NULL,
  `class_date` date DEFAULT NULL,
  `status` enum('Pending','Approved','Returned') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_transactions`
--

INSERT INTO `item_transactions` (`transaction_id`, `quantity_borrowed`, `quantity_returned`, `borrowed_at`, `return_date`, `returned_at`, `item_id`, `users_id`, `schedule_from`, `schedule_to`, `class_date`, `status`) VALUES
(2, 0, 3, '2025-01-31 00:00:00', NULL, '2025-01-07 02:35:17', 2, 10, '00:00:19', '19:42:00', NULL, 'Returned'),
(17, 0, 3, '2025-01-31 00:00:00', NULL, '2025-01-07 02:36:51', 5, 10, '00:00:19', '19:42:00', NULL, 'Returned'),
(20, 1, 21, '2025-01-08 02:45:35', '2025-01-06', '2025-01-07 12:36:18', 2, 10, '02:46:00', '02:49:00', '2025-01-08', 'Approved'),
(22, 23, NULL, '2025-01-08 02:48:51', '2025-01-09', NULL, 6, 10, '02:49:00', '05:51:00', '2025-01-11', 'Pending'),
(24, 110, NULL, '2025-01-07 03:13:09', '2025-01-08', NULL, 3, 10, '03:14:00', '05:13:00', '2025-01-09', 'Pending');

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
(15, '../assets/uploads/6761bb7f03e7d_CANDEN ATTACHMENTS-merged.pdf', 'd', '0000-00-00 00:00:00', '2025-01-05 18:00:46', 'd'),
(17, '../assets/uploads/6761c11f6267f_Mukaram_CV.pdf', 'yes', '2024-12-17 11:21:19', NULL, 'yesssssssssssss'),
(18, '../assets/uploads/6771850e33494_qwer.pdf', 'test', '2024-12-29 10:21:18', NULL, '123'),
(19, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(20, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(21, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(22, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(23, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2024-12-29 10:33:43', NULL, '123'),
(24, '../assets/uploads/677187f7addda_qwer.pdf', 'test123', '2025-01-02 10:33:43', NULL, '123'),
(25, '../assets/uploads/677a589c4b387_qwer.pdf', '22123', '2025-01-05 03:02:04', NULL, '22123');

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
(12, '123', '1qwe', '2025-01-05 02:38:52', 'Announcements', 0),
(13, '22123', '22123', '2025-01-05 03:02:04', 'Memorandums', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notif_items`
--

CREATE TABLE `notif_items` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notif_items`
--

INSERT INTO `notif_items` (`id`, `description`, `created_at`) VALUES
(3, '11111111111111 has critical stocks.', '2025-01-07 03:39:26'),
(4, 'Item Earth with quantity 22 is overdue.', '2025-01-07 03:39:40'),
(5, 'Item Earth with quantity 22 is overdue.', '2025-01-07 03:58:27'),
(6, 'Earth has critical stocks.', '2025-01-07 12:06:27'),
(7, 'Item Earth with quantity 22 is overdue.', '2025-01-07 12:14:23'),
(8, 'Item Earth with quantity 12 is overdue.', '2025-01-07 12:14:23'),
(9, 'Item Earth with quantity 22 is overdue.', '2025-01-07 12:14:51'),
(10, 'Item Earth with quantity 12 is overdue.', '2025-01-07 12:14:51'),
(11, 'Item Earth with quantity 22 is overdue.', '2025-01-07 12:33:23'),
(12, 'Item Earth with quantity 12 is overdue.', '2025-01-07 12:33:23'),
(13, 'Item Earth with quantity 1 is overdue.', '2025-01-07 12:55:54'),
(14, 'Item Earth with quantity 12 is overdue.', '2025-01-07 12:55:54'),
(15, 'Item Earth with quantity 1 is overdue.', '2025-01-07 13:54:33'),
(16, 'Item Earth with quantity 1 is overdue.', '2025-01-07 13:54:43'),
(17, 'Item Earth with quantity 1 is overdue.', '2025-01-07 13:55:33'),
(18, 'Item Earth with quantity 1 is overdue.', '2025-01-07 13:55:42'),
(19, 'Item Earth with quantity 1 is overdue.', '2025-01-07 13:55:53'),
(20, 'Item Earth with quantity 1 is overdue.', '2025-01-07 13:57:37'),
(21, 'Item Earth with quantity 1 is overdue.', '2025-01-07 14:01:31');

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
(15, 'QWORD', 'ONE PIECE', '../assets/img/676ec35d69589.jpg'),
(16, '1213', '12312', '../assets/img/677a59f9b8179.jpg');

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

--
-- Dumping data for table `pending_users`
--

INSERT INTO `pending_users` (`id`, `first_name`, `last_name`, `middle_name`, `email`, `address`, `contact_no`, `rank`, `password`, `created_at`, `role`, `image`, `department`) VALUES
(12, 'Samwdw', 'Ricaldeasd', 'sda', 'binimadasdaslsadsoi352@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Assistant Professor', '$2y$10$kNQw68yomSgLPANy1htnbucG75OLiFGcpvu8p3rpvwEKG257P4rEO', '2025-01-07 06:01:24', 'instructor', 'CSSPE.png', 'College of Architecture1');

-- --------------------------------------------------------

--
-- Table structure for table `returned_items`
--

CREATE TABLE `returned_items` (
  `return_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_returned` varchar(255) NOT NULL,
  `returned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Good','Damaged','Lost','Replaced') NOT NULL,
  `remarks` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returned_items`
--

INSERT INTO `returned_items` (`return_id`, `transaction_id`, `item_id`, `quantity_returned`, `returned_at`, `status`, `remarks`) VALUES
(8, 2, 2, '3', '2025-01-07 02:35:17', 'Good', ''),
(9, 2, 2, '1', '2025-01-07 02:35:17', 'Lost', ''),
(10, 17, 5, '2', '2025-01-07 02:36:29', 'Good', ''),
(11, 17, 5, '4', '2025-01-07 02:36:29', 'Lost', ''),
(12, 17, 5, '1', '2025-01-07 02:36:51', 'Replaced', ''),
(13, 20, 2, '21', '2025-01-07 12:36:18', 'Good', '');

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
(10, 'User1', 'Test', 'D', 'testuser1@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Instructor', '$2y$10$rn4sfd1RZqYAV3dkteDdNOWblB3z6SyBHT8ItMxWC22MSrdtRjNwS', '2024-12-08 12:19:51', 'instructor', '', ''),
(20, 'Admin', 'Super', 'D', 'superadmin@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Associate Professor', '$2y$10$IWuK.Nnty9878oZXm/zoWOIHNzqNFnWjZt58tRYl1wdmzoubWVmuO', '2024-12-08 14:46:05', 'super_admin', '', 'College of Architecture1'),
(25, 'Invent', 'Tory', 'D', 'inventoryadmin@gmail.com', '12391', '5414141291', 'Instructor', '$2y$10$Ms8QUPjUWubmuwL9yJa7iu4tq7PsRrYIucPYl631hmtJb6LOLzw4G', '2024-12-08 15:22:56', 'inventory_admin', '25_profile.jpg', 'College of Architecture'),
(56, 'Infor', 'Mation', 'D', 'informationadmin@gmail.com', 'Rio Hondo', '213123', 'Instructor', '$2y$10$5LWJBHQEntvz6IvYzItoPuWySZiRMYOBjbFX1HoBx3vdtoismhJCW', '2024-12-17 18:03:21', 'information_admin', '56_profile.jpg', 'College of Architecture'),
(67, 'Micheal', 'Jordan', 'D', 'jordan@gmail.com', 'Hanapi Drive', '123', 'Assistant Professor', '$2y$10$IWuK.Nnty9878oZXm/zoWOIHNzqNFnWjZt58tRYl1wdmzoubWVmuO', '2025-01-07 05:44:22', 'instructor', 'CSSPE.png', ''),
(69, 'Maloi121', 'Cena121', 'D.', 'binimaloi121352@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '1213', 'Instructor', '$2y$10$MY2nvqjF2DulFQQQWL0X8Odtn9lmJJig33YVGiqm0NyHS7JZYM9/S', '2025-01-07 05:51:50', 'instructor', 'CSSPE.png', '');

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
-- Indexes for table `notif_items`
--
ALTER TABLE `notif_items`
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
-- Indexes for table `returned_items`
--
ALTER TABLE `returned_items`
  ADD PRIMARY KEY (`return_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `item_transactions`
--
ALTER TABLE `item_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `memorandums`
--
ALTER TABLE `memorandums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notif_items`
--
ALTER TABLE `notif_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pending_users`
--
ALTER TABLE `pending_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `returned_items`
--
ALTER TABLE `returned_items`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

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
