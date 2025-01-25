-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2025 at 04:30 PM
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
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `deactivation_triggered` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `year`, `deactivation_triggered`) VALUES
(1, 2025, 1);

-- --------------------------------------------------------

--
-- Table structure for table `deactivation_logs`
--

CREATE TABLE `deactivation_logs` (
  `id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `log_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deactivation_logs`
--

INSERT INTO `deactivation_logs` (`id`, `message`, `log_date`) VALUES
(1, 'Deactivated all user accounts on 2025-01-24 at 14:51:21 (Day: 24, Month: 01, Year: 2025).', '2025-01-24');

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
  `type` enum('sport','gadgets') NOT NULL DEFAULT 'sport',
  `quantity` varchar(255) NOT NULL,
  `quantity_origin` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `users_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `brand`, `type`, `quantity`, `quantity_origin`, `created_at`, `updated_at`, `users_id`, `image`, `note`) VALUES
(2, 'Basketball', 'For PE use', 'local', 'sport', '18', '27', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, '67935efcae5a2-download.jpg', NULL),
(3, 'Volleyball', 'For PE use', 'FIBA brand', 'sport', '10', '120', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, '67935f0482682-download (2).jpg', NULL),
(5, 'Soccer Ball', 'For PE Use', 'Blue Lock', 'sport', '126', '3', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, '67935f0adc4c6-download (1).jpg', NULL),
(6, 'Television', 'For Watching Lectures', 'Yamaha', 'gadgets', '100', '123', '2024-12-28 16:33:12', '0000-00-00 00:00:00', 25, '67935f1251678-tv.jpg', NULL),
(9, 'DYNED Monitor', 'For Pierre', 'EPIC', 'sport', '112', '123', '2024-12-29 17:07:29', '0000-00-00 00:00:00', 25, '67935f1ad2118-dyned.jpg', NULL),
(10, 'Printer', 'for printing', 'local', 'gadgets', '8', '12', '2024-12-29 17:09:34', '0000-00-00 00:00:00', 25, '67935f295ec02-download (3).jpg', NULL),
(17, '18', '228', '228', 'gadgets', '228', '228', '2025-01-24 19:14:59', '0000-00-00 00:00:00', 20, NULL, NULL),
(18, '1', '123', '22', 'gadgets', '33', '33', '2025-01-24 23:07:39', '0000-00-00 00:00:00', 20, NULL, 'test11');

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
  `status` enum('Pending','Approved','Returned') NOT NULL DEFAULT 'Pending',
  `status_remark` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_transactions`
--

INSERT INTO `item_transactions` (`transaction_id`, `quantity_borrowed`, `quantity_returned`, `borrowed_at`, `return_date`, `returned_at`, `item_id`, `users_id`, `schedule_from`, `schedule_to`, `class_date`, `status`, `status_remark`) VALUES
(2, 0, 3, '2025-01-31 00:00:00', NULL, '2025-01-07 02:35:17', 2, 10, '00:00:19', '19:42:00', NULL, 'Returned', NULL),
(17, 0, 3, '2025-01-31 00:00:00', NULL, '2025-01-07 02:36:51', 5, 10, '00:00:19', '19:42:00', NULL, 'Returned', NULL),
(20, 0, 22, '2025-01-08 02:45:35', '2025-01-06', '2025-01-24 17:55:11', 2, 10, '02:46:00', '02:49:00', '2025-01-08', 'Returned', NULL),
(22, 23, NULL, '2025-01-08 02:48:51', '2025-01-09', NULL, 6, 10, '02:49:00', '05:51:00', '2025-01-11', 'Pending', NULL),
(24, 110, NULL, '2025-01-07 03:13:09', '2025-01-08', NULL, 3, 10, '03:14:00', '05:13:00', '2025-01-09', 'Pending', NULL),
(27, 2, NULL, '2025-01-08 23:39:49', '2025-01-02', NULL, 2, 67, '23:42:00', '23:42:00', '2025-01-17', 'Pending', NULL),
(29, 11, NULL, '2025-01-24 17:06:00', '2025-01-24', NULL, 9, 10, '17:07:00', '17:09:00', '2025-01-24', 'Approved', 'For Pierre'),
(30, 2, NULL, '2025-01-24 17:51:16', '2025-01-24', NULL, 2, 10, '17:55:00', '17:56:00', '2025-01-24', 'Approved', 'N/A'),
(31, 2, NULL, '2025-01-24 17:52:11', '2025-01-30', NULL, 10, 67, '17:57:00', '17:56:00', '2025-01-24', 'Approved', 'Its still in the office'),
(32, 2, NULL, '2025-01-24 17:52:50', '2025-01-24', NULL, 10, 67, '17:56:00', '17:57:00', '2025-01-24', 'Approved', 'still in the office');

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
(1, 'Basketball is being frequently borrowed.', '2025-01-24 17:51:16'),
(2, 'Printer is being frequently borrowed.', '2025-01-24 17:52:50'),
(3, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 18:53:46'),
(4, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 18:53:46'),
(5, 'Item Television with quantity 23 is overdue.', '2025-01-24 18:53:46'),
(6, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 19:02:32'),
(7, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 19:02:33'),
(8, 'Item Television with quantity 23 is overdue.', '2025-01-24 19:02:33'),
(9, 'Item Television with quantity 23 is overdue.', '2025-01-24 21:19:39'),
(10, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 21:19:39'),
(11, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 21:19:39'),
(12, 'Item Television with quantity 23 is overdue.', '2025-01-24 21:29:11'),
(13, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 21:29:11'),
(14, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 21:29:11'),
(15, 'Item Television with quantity 23 is overdue.', '2025-01-24 21:34:28'),
(16, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 21:34:28'),
(17, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 21:34:28'),
(18, 'Item Television with quantity 23 is overdue.', '2025-01-24 21:52:49'),
(19, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 21:52:49'),
(20, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 21:52:49'),
(21, 'Item Television with quantity 23 is overdue.', '2025-01-24 21:52:56'),
(22, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 21:52:56'),
(23, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 21:52:56'),
(24, 'Item Television with quantity 23 is overdue.', '2025-01-24 21:53:02'),
(25, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 21:53:02'),
(26, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 21:53:02'),
(27, 'Item Television with quantity 23 is overdue.', '2025-01-24 21:53:10'),
(28, 'Item Volleyball with quantity 110 is overdue.', '2025-01-24 21:53:10'),
(29, 'Item Basketball with quantity 2 is overdue.', '2025-01-24 21:53:10');

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
(16, '1213', '12312', '../assets/img/677a59f9b8179.jpg'),
(17, '11', '11', '../assets/img/CSSPE.png');

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
(12, 'Samwdw', 'Ricaldeasd', 'sda', 'binimadasdaslsadsoi352@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Assistant Professor', '$2y$10$kNQw68yomSgLPANy1htnbucG75OLiFGcpvu8p3rpvwEKG257P4rEO', '2025-01-07 06:01:24', 'instructor', 'CSSPE.png', 'College of Architecture1'),
(14, 'Damwdw', 'Ricaldeasd', 'sda', 'binimadasssdaslsadsoi352@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Assistant Professor', '$2y$10$kNQw68yomSgLPANy1htnbucG75OLiFGcpvu8p3rpvwEKG257P4rEO', '2025-01-07 06:01:24', 'instructor', 'CSSPE.png', 'College of Architecture1');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `organization_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_name`, `description`, `image`, `organization_id`) VALUES
(1, 'ghibli', '12312', '../assets/img/67887d140cc57.jpg', 15),
(4, '22', '222', '', 16);

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
   `unique_id_remark` VARCHAR(255) NULL
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
(12, 17, 5, '1', '2025-01-07 02:36:51', 'Replaced', 'Damaged but now replaced'),
(13, 20, 2, '21', '2025-01-07 12:36:18', 'Good', ''),
(14, 20, 2, '1', '2025-01-24 17:55:11', 'Good', ''),
(15, 20, 2, '1', '2025-01-24 17:55:11', 'Replaced', 'Lost but now Replaced'),
(16, 20, 2, '1', '2025-01-24 17:55:11', 'Replaced', 'Damaged but now Replaced by John Cena');

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` enum('1st Semester','2nd Semester','Summer') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`id`, `school_year`, `semester`, `start_date`, `end_date`, `created_at`) VALUES
(2, '2024-2025', '2nd Semester', '2025-01-01', '2025-05-26', '2025-01-24 14:45:39'),
(3, '2024-2025', '1st Semester', '2024-08-05', '2024-12-10', '2025-01-24 14:48:29');

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
  `department` varchar(255) NOT NULL,
  `ban` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `middle_name`, `email`, `address`, `contact_no`, `rank`, `password`, `created_at`, `role`, `image`, `department`, `ban`, `status`) VALUES
(10, 'User1', 'Test', 'D', 'testuser1@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Instructor', '$2y$10$6xLYYP1E4tpFpn3pTSTXpucbL8mk/kkH8/o0vrWecHo/v/GwamTAa', '2024-12-08 12:19:51', 'instructor', '', '', 0, 1),
(20, 'Admin', 'Super', 'D', 'superadmin@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Associate Professor', '$2y$10$Kn8/QISr70zZ.r.KMFXvFe4vVyTXKHFSeXksL8T6eDP/M80UvB8EK', '2024-12-08 14:46:05', 'super_admin', '', 'College of Architecture1', 0, 0),
(25, 'Invent', 'Tory', 'D', 'inventoryadmin@gmail.com', '12391', '5414141291', 'Instructor', '$2y$10$Ms8QUPjUWubmuwL9yJa7iu4tq7PsRrYIucPYl631hmtJb6LOLzw4G', '2024-12-08 15:22:56', 'inventory_admin', '25_profile.jpg', 'College of Architecture', 0, 0),
(56, 'Infor', 'Mation', 'D', 'informationadmin@gmail.com', 'Rio Hondo', '213123', 'Instructor', '$2y$10$5LWJBHQEntvz6IvYzItoPuWySZiRMYOBjbFX1HoBx3vdtoismhJCW', '2024-12-17 18:03:21', 'information_admin', '56_profile.jpg', 'College of Architecture', 0, 0),
(67, 'Micheal', 'Jordan', 'D', 'jordan@gmail.com', 'Hanapi Drive', '123', 'Assistant Professor', '$2y$10$IWuK.Nnty9878oZXm/zoWOIHNzqNFnWjZt58tRYl1wdmzoubWVmuO', '2025-01-07 05:44:22', 'instructor', 'CSSPE.png', '', 0, 1),
(69, 'Maloi121', 'Cena121', 'D.', 'binimaloi121352@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '1213', 'Instructor', '$2y$10$MY2nvqjF2DulFQQQWL0X8Odtn9lmJJig33YVGiqm0NyHS7JZYM9/S', '2025-01-07 05:51:50', 'instructor', 'CSSPE.png', '', 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deactivation_logs`
--
ALTER TABLE `deactivation_logs`
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
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_projects_organization` (`organization_id`);

--
-- Indexes for table `returned_items`
--
ALTER TABLE `returned_items`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deactivation_logs`
--
ALTER TABLE `deactivation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `item_transactions`
--
ALTER TABLE `item_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pending_users`
--
ALTER TABLE `pending_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `returned_items`
--
ALTER TABLE `returned_items`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
