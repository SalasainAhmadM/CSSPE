-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2025 at 04:52 AM
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
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `quantity` varchar(255) NOT NULL,
  `origin_quantity` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(19, 'Soccer Ball', 'For Major League', 'Mikasa', 'sport', '10', '15', '2025-01-25 19:53:04', '0000-00-00 00:00:00', 20, '6794d0a055109-download (1).jpg', 'dont take out'),
(20, 'Basketball', 'For Inter High', 'Shohoku', 'sport', '2', '4', '2025-01-25 20:59:26', '0000-00-00 00:00:00', 25, '6794e02e60fc9-download.jpg', ''),
(21, 'Shohoku Ball', 'For Basbetball Games', 'Kanagawa', 'sport', '20', '20', '2025-03-11 11:30:18', '0000-00-00 00:00:00', 20, '67cfae4a17131-sd.jpg', 'Don\'t Borrow for fun');

-- --------------------------------------------------------

--
-- Table structure for table `item_quantities`
--

CREATE TABLE `item_quantities` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `unique_id` varchar(255) DEFAULT NULL,
    `unique_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_quantities`
--

INSERT INTO `item_quantities` (`id`, `item_id`, `unique_id`) VALUES
(5, 19, '110225001'),
(6, 19, '110225002'),
(7, 19, '110225003'),
(8, 19, '110225004'),
(9, 19, '110225005'),
(10, 19, '110225007'),
(11, 19, '110225008'),
(12, 19, '110225009'),
(13, 19, '110225010'),
(14, 19, '110225011'),
(15, 19, '110225012'),
(16, 19, '110225013'),
(17, 19, '110225014'),
(18, 19, '110225015'),
(20, 19, '110225016'),
(34, 20, '110225017'),
(35, 20, '110225018'),
(36, 20, '110225019'),
(37, 20, '110225020'),
(38, 21, '110325001'),
(39, 21, '110325002'),
(40, 21, '110325003'),
(41, 21, '110325004'),
(42, 21, '110325005'),
(43, 21, '110325006'),
(44, 21, '110325007'),
(45, 21, '110325008'),
(46, 21, '110325009'),
(47, 21, '110325010'),
(48, 21, '110325011'),
(49, 21, '110325012'),
(50, 21, '110325013'),
(51, 21, '110325014'),
(52, 21, '110325015'),
(53, 21, '110325016'),
(54, 21, '110325017'),
(55, 21, '110325018'),
(56, 21, '110325019'),
(57, 21, '110325020');

-- --------------------------------------------------------

--
-- Table structure for table `item_status_tracking`
--

CREATE TABLE `item_status_tracking` (
  `id` int(11) NOT NULL,
  `item_quantity_id` int(11) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_status_tracking`
--

INSERT INTO `item_status_tracking` (`id`, `item_quantity_id`, `updated_at`, `remarks`) VALUES
(1, 5, '2025-01-26 04:19:10', 'Damaged'),
(2, 6, '2025-01-26 04:19:10', 'Damaged'),
(3, 7, '2025-01-26 04:19:10', 'Lost'),
(4, 8, '2025-01-26 04:19:10', 'Replaced'),
(5, 10, '2025-01-30 03:49:00', 'Damaged'),
(6, 5, '2025-01-30 03:50:07', 'Damaged'),
(7, 6, '2025-01-30 03:50:07', 'Lost'),
(8, 7, '2025-01-30 03:50:07', 'Replaced'),
(9, 5, '2025-01-30 03:53:41', 'Damaged'),
(10, 5, '2025-01-30 07:40:42', 'Damaged'),
(11, 9, '2025-01-30 15:25:54', 'Damaged'),
(12, 10, '2025-01-30 15:25:54', 'Lost'),
(13, 11, '2025-01-30 15:25:54', 'Replaced');

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
  `brand_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL,
  `schedule_from` time DEFAULT NULL,
  `schedule_to` time DEFAULT NULL,
  `class_date` date DEFAULT NULL,
  `status` enum('Pending','Approved','Returned') NOT NULL DEFAULT 'Pending',
  `status_remark` varchar(255) DEFAULT NULL,
  `assigned_student` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_transactions`
--

INSERT INTO `item_transactions` (`transaction_id`, `quantity_borrowed`, `quantity_returned`, `borrowed_at`, `return_date`, `returned_at`, `item_id`, `users_id`, `schedule_from`, `schedule_to`, `class_date`, `status`, `status_remark`, `assigned_student`) VALUES
(41, 2, 2, '2025-01-25 23:10:12', '2025-01-28', '2025-01-25 23:43:17', 19, 67, NULL, NULL, NULL, 'Returned', 'still at home', NULL),
(43, 3, 3, '2025-01-25 23:53:13', '2025-01-27', '2025-01-26 00:07:21', 20, 67, NULL, NULL, NULL, 'Returned', 'get it in my office', NULL),
(44, 2, 2, '2025-01-26 01:04:47', '2025-01-26', '2025-01-26 01:12:31', 19, 67, NULL, NULL, NULL, 'Returned', 'N/A', NULL),
(45, 4, 4, '2025-01-26 01:13:16', '2025-01-29', '2025-01-26 02:15:56', 19, 67, NULL, NULL, NULL, 'Returned', '123', NULL),
(49, 5, 5, '2025-01-26 02:19:34', '2025-01-29', '2025-01-26 02:41:57', 19, 67, NULL, NULL, NULL, 'Returned', 'noted', NULL),
(50, 1, 1, '2025-01-26 02:19:46', '2025-02-06', '2025-01-26 02:24:34', 20, 67, NULL, NULL, NULL, 'Returned', '123', NULL),
(53, 2, 2, '2025-01-26 02:44:59', '2025-01-28', '2025-01-26 02:46:07', 19, 67, NULL, NULL, NULL, 'Returned', 'testest', NULL),
(54, 4, 4, '2025-01-26 02:48:32', '2025-01-28', '2025-01-26 02:49:05', 19, 67, NULL, NULL, NULL, 'Returned', 'FOR THE WIN', NULL),
(55, 5, 5, '2025-01-26 02:51:08', '2025-01-28', '2025-01-26 02:58:30', 19, 67, NULL, NULL, NULL, 'Returned', '123', NULL),
(56, 4, 4, '2025-01-26 03:05:01', '2025-01-28', '2025-01-26 03:11:39', 19, 67, NULL, NULL, NULL, 'Returned', 'still in the barracks', NULL),
(58, 2, 2, '2025-01-26 03:13:28', '2025-01-29', '2025-01-26 03:14:11', 20, 67, NULL, NULL, NULL, 'Returned', 'qwqrwqe', NULL),
(59, 2, 2, '2025-01-26 03:14:54', '2025-01-29', '2025-01-26 03:15:32', 19, 67, NULL, NULL, NULL, 'Returned', 'qwewqe', NULL),
(61, 2, 2, '2025-01-26 03:19:02', '2025-01-29', '2025-01-26 03:22:20', 19, 67, NULL, NULL, NULL, 'Returned', 'egg', NULL),
(62, 3, 3, '2025-01-26 03:27:31', '2025-01-29', '2025-01-26 03:28:29', 19, 67, NULL, NULL, NULL, 'Returned', 'still in shoppee', NULL),
(64, 5, 5, '2025-01-26 03:47:01', '2025-01-30', '2025-01-26 03:48:05', 19, 67, NULL, NULL, NULL, 'Returned', 'lets g', NULL),
(65, 4, 4, '2025-01-26 04:08:31', '2025-01-29', '2025-01-26 04:09:11', 19, 67, NULL, NULL, NULL, 'Returned', 'test', NULL),
(66, 4, 4, '2025-01-26 04:14:14', '2025-01-29', '2025-01-26 04:14:49', 19, 67, NULL, NULL, NULL, 'Returned', 'lets goooooo', NULL),
(67, 6, 6, '2025-01-26 04:17:52', '2025-02-06', '2025-01-26 04:19:10', 19, 67, NULL, NULL, NULL, 'Returned', 'testest', NULL),
(70, 5, 5, '2025-01-29 22:40:10', '2025-02-07', '2025-01-30 03:50:07', 19, 67, NULL, NULL, NULL, 'Returned', 'N/A', NULL),
(71, 2, 2, '2025-01-30 03:58:45', '2025-02-01', '2024-12-24 07:40:42', 19, 67, NULL, NULL, NULL, 'Returned', 'test', NULL),
(76, 2, 2, '2025-01-30 03:21:32', '2025-01-31', '2025-01-30 03:49:00', 19, 25, NULL, NULL, NULL, 'Returned', 'pls take out', NULL),
(77, 2, 2, '2025-01-30 03:52:10', '2025-01-31', '2025-02-04 03:53:41', 19, 67, NULL, NULL, NULL, 'Returned', 'test', NULL),
(80, 2, NULL, '2024-12-16 08:13:17', '2025-01-31', NULL, 20, 67, NULL, NULL, NULL, 'Pending', NULL, NULL),
(81, 2, NULL, '2025-01-30 12:40:05', '2025-01-31', NULL, 19, 10, NULL, NULL, NULL, 'Approved', 'tst1', 'luffy d'),
(82, 5, 5, '2025-01-30 15:16:05', '2025-01-31', '2025-01-30 15:25:54', 19, 10, NULL, NULL, NULL, 'Returned', 'tst2', NULL),
(83, 2, NULL, '2025-03-11 11:21:24', '2025-03-11', NULL, 19, 10, NULL, NULL, NULL, 'Pending', NULL, 'test bro');

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
(1, 'Soccer Ball is being frequently borrowed.', '2025-01-26 03:05:01'),
(2, 'Soccer Ball is being frequently borrowed.', '2025-01-26 03:14:54'),
(3, 'Soccer Ball is being frequently borrowed.', '2025-01-26 03:19:02'),
(4, 'Soccer Ball is being frequently borrowed.', '2025-01-26 03:27:31'),
(5, 'Soccer Ball is being frequently borrowed.', '2025-01-26 03:46:38'),
(6, 'Soccer Ball is being frequently borrowed.', '2025-01-26 03:47:01'),
(7, 'Soccer Ball is being frequently borrowed.', '2025-01-26 04:08:31'),
(8, 'Soccer Ball is being frequently borrowed.', '2025-01-26 04:14:14'),
(9, 'Soccer Ball is being frequently borrowed.', '2025-01-26 04:17:52'),
(10, 'Soccer Ball (Mikasa) with quantity of 5 was borrowed by Admin Super.', '2025-01-29 07:37:06'),
(11, 'Soccer Ball (Mikasa) with quantity of 6 was borrowed by Admin Super.', '2025-01-29 07:37:19'),
(12, 'Soccer Ball is being frequently borrowed.', '2025-01-29 22:40:10'),
(13, 'Soccer Ball (Mikasa) with quantity of 2 was borrowed by Invent Tory.', '2025-01-30 02:56:57'),
(14, 'Soccer Ball is being frequently borrowed.', '2025-01-30 02:58:21'),
(15, 'Basketball (Shohoku) with quantity of 2 was borrowed by Invent Tory.', '2025-01-30 03:01:48'),
(16, 'Soccer Ball (Mikasa) with quantity of 2 was borrowed by Invent Tory.', '2025-01-30 03:02:22'),
(18, 'Soccer Ball (Mikasa) with quantity of 2 was borrowed by Invent Tory.', '2025-01-30 03:21:32'),
(19, 'Soccer Ball is being frequently borrowed.', '2025-01-30 03:21:32'),
(20, 'Soccer Ball is being frequently borrowed.', '2025-01-30 03:52:10'),
(21, 'Soccer Ball is being frequently borrowed.', '2025-01-30 03:58:45'),
(22, 'Soccer Ball is being frequently borrowed.', '2025-01-30 08:13:04'),
(23, 'Soccer Ball (Mikasa) with quantity of 2 was borrowed by User1 Test.', '2025-01-30 12:40:05'),
(24, 'Soccer Ball is being frequently borrowed.', '2025-01-30 12:40:05'),
(25, 'Soccer Ball (Mikasa) with quantity of 5 was borrowed by User1 Test.', '2025-01-30 15:16:06'),
(26, 'Soccer Ball is being frequently borrowed.', '2025-01-30 15:16:06'),
(27, 'Item Soccer Ball with quantity 2 is overdue.', '2025-03-11 09:33:36'),
(28, 'Item Basketball with quantity 2 is overdue.', '2025-03-11 09:33:36'),
(29, 'Item Soccer Ball with quantity 2 is overdue.', '2025-03-11 09:33:54'),
(30, 'Item Basketball with quantity 2 is overdue.', '2025-03-11 09:33:54');

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
  `quantity_returned` varchar(255) DEFAULT NULL,
  `returned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Good','Damaged','Lost','Replaced') NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `unique_id_remark` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returned_items`
--

INSERT INTO `returned_items` (`return_id`, `transaction_id`, `item_id`, `quantity_returned`, `returned_at`, `status`, `remarks`, `unique_id_remark`) VALUES
(4, 67, 19, '1', '2024-12-24 04:19:10', 'Replaced', 'Replaced', '175699'),
(6, 76, 19, '1', '2025-01-30 03:49:00', 'Good', '', '953731'),
(11, 70, 19, '1', '2025-01-30 03:50:07', 'Good', '', '175699'),
(12, 70, 19, '1', '2025-01-30 03:50:07', 'Good', '', '475543'),
(20, 71, 19, '1', '2024-12-23 03:50:07', 'Replaced', 'Lost but now Replaced', '435386'),
(21, 82, 19, '1', '2025-01-30 15:25:54', 'Damaged', 'Damaged', '475543'),
(22, 82, 19, '1', '2025-01-30 15:25:54', 'Lost', 'Lost', '004971'),
(23, 82, 19, '1', '2025-01-30 15:25:54', 'Replaced', 'Replaced', '953731'),
(24, 82, 19, '1', '2025-01-30 15:25:54', 'Good', '', '925151'),
(25, 82, 19, '1', '2025-01-30 15:25:54', 'Good', '', '098685');

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
-- Table structure for table `transaction_item_quantities`
--

CREATE TABLE `transaction_item_quantities` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `item_quantity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_item_quantities`
--

INSERT INTO `transaction_item_quantities` (`id`, `transaction_id`, `item_quantity_id`) VALUES
(42, 80, 34),
(43, 80, 35),
(44, 81, 7),
(45, 81, 8),
(51, 83, 5),
(52, 83, 6);

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
(10, 'User1', 'Test', 'D', 'testuser1@gmail.com', 'Hannah Drive, Rio Hondo, Zamboanga City', '54141412', 'Instructor', '$2y$10$6xLYYP1E4tpFpn3pTSTXpucbL8mk/kkH8/o0vrWecHo/v/GwamTAa', '2024-12-08 12:19:51', 'instructor', '', '', 0, 0),
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
-- Indexes for table `brands`
--
ALTER TABLE `brands`
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
-- Indexes for table `item_quantities`
--
ALTER TABLE `item_quantities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `item_status_tracking`
--
ALTER TABLE `item_status_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_quantity_id` (`item_quantity_id`);

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
-- Indexes for table `transaction_item_quantities`
--
ALTER TABLE `transaction_item_quantities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `item_quantity_id` (`item_quantity_id`);

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
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `item_quantities`
--
ALTER TABLE `item_quantities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `item_status_tracking`
--
ALTER TABLE `item_status_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `item_transactions`
--
ALTER TABLE `item_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaction_item_quantities`
--
ALTER TABLE `transaction_item_quantities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

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
-- Constraints for table `item_quantities`
--
ALTER TABLE `item_quantities`
  ADD CONSTRAINT `item_quantities_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_status_tracking`
--
ALTER TABLE `item_status_tracking`
  ADD CONSTRAINT `item_status_tracking_ibfk_1` FOREIGN KEY (`item_quantity_id`) REFERENCES `item_quantities` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `transaction_item_quantities`
--
ALTER TABLE `transaction_item_quantities`
  ADD CONSTRAINT `transaction_item_quantities_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `item_transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_item_quantities_ibfk_2` FOREIGN KEY (`item_quantity_id`) REFERENCES `item_quantities` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
