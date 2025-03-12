<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);
$userid = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $image = $row['image'];
} else {
    $fullName = "User Not Found";
}

$query = "SELECT * FROM events";
$result = mysqli_query($conn, $query);

$query_notifications = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";
$result_notifications = mysqli_query($conn, $query_notifications);
$notificationCount = 0;

if ($result_notifications && $row_notifications = mysqli_fetch_assoc($result_notifications)) {
    $notificationCount = $row_notifications['notification_count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Calendar</title>

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Base styles */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            scroll-behavior: smooth;
            overflow-x: hidden;
            width: 100%;
        }

        /* Theme colors */
        :root {
            --primary: #6B0D0D;
            --primary-dark: #540A0A;
            --primary-light: rgba(107, 13, 13, 0.1);
            --secondary: #6c757d;
            --secondary-dark: #5a6268;
            --light: #f9f9f9;
            --lighter: #f5f5f5;
            --border: #f0f0f0;
            --text: #333;
            --text-light: #666;
            --white: #fff;
            --highlight: #FFE0E0;
            --today: #FFDEDE;
        }

        /* Layout */
        .sidebar {
            position: fixed;
            width: 250px;
            height: 100%;
            background-color: var(--primary);
            color: var(--white);
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        /* Button styling */
        .btn {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            white-space: nowrap;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            box-shadow: 0 2px 4px rgba(107, 13, 13, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 3px 6px rgba(107, 13, 13, 0.3);
        }

        /* Mobile navigation */
        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 101;
            display: none;
            padding: 8px;
            border-radius: 6px;
            background-color: var(--primary);
            color: var(--white);
            font-size: 18px;
            border: none;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .toggle-btn:hover {
            background-color: var(--primary-dark);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 99;
            backdrop-filter: blur(2px);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Header styling */
        .page-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-header img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .system-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* Content styling */
        .page-content {
            padding: 25px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 15px;
        }

        .page-title-text {
            display: flex;
            align-items: center;
        }

        .page-title i {
            margin-right: 12px;
            color: var(--primary);
        }

        /* Calendar controls */
        .calendar-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .month-display {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary);
            min-width: 200px;
            text-align: center;
        }

        .nav-btn {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(107, 13, 13, 0.2);
        }

        .nav-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(107, 13, 13, 0.3);
        }

        .calendar-views {
            display: flex;
            gap: 10px;
        }

        .view-btn {
            background-color: var(--light);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .view-btn.active {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        /* Search functions */
        .search-container {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .search-input {
            padding: 8px 15px;
            border: 1px solid var(--border);
            border-radius: 30px;
            width: 250px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.1);
            width: 300px;
        }

        /* Calendar styling */
        .calendar {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .calendar-header {
            background-color: var(--primary);
            color: var(--white);
            text-align: center;
            padding: 12px 0;
            font-weight: 500;
        }

        .calendar-day {
            min-height: 120px;
            border: 1px solid var(--border);
            padding: 8px;
            position: relative;
            transition: all 0.2s ease;
        }

        .calendar-day:hover {
            background-color: var(--lighter);
        }

        .calendar-day.other-month {
            background-color: var(--lighter);
            color: var(--text-light);
        }

        .calendar-day.today {
            background-color: var(--today);
        }

        .day-number {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            text-align: right;
        }

        .today .day-number {
            background-color: var(--primary);
            color: var(--white);
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
        }

        .calendar-events {
            display: flex;
            flex-direction: column;
            gap: 3px;
            overflow: hidden;
            max-height: calc(100% - 25px);
        }

        .calendar-event {
            background-color: var(--primary-light);
            color: var(--primary);
            border-left: 3px solid var(--primary);
            padding: 4px 6px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all 0.2s ease;
        }

        .calendar-event:hover {
            background-color: var(--highlight);
            transform: translateY(-1px);
        }

        .more-events {
            color: var(--primary);
            font-size: 11px;
            cursor: pointer;
            text-align: center;
            margin-top: 2px;
            font-weight: 500;
        }

        /* List view */
        .list-view {
            display: none;
            flex-direction: column;
            gap: 10px;
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            padding: 20px;
        }

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary);
        }

        .list-day {
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .list-day::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: var(--border);
        }

        .list-event {
            display: flex;
            background-color: var(--light);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .list-event:hover {
            background-color: var(--highlight);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .list-event-color {
            width: 6px;
            background-color: var(--primary);
        }

        .list-event-content {
            padding: 15px;
            flex: 1;
        }

        .list-event-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text);
        }

        .list-event-time {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        .list-event-desc {
            font-size: 14px;
            color: var(--text);
        }

        /* Event modal */
        .event-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .event-modal-content {
            background-color: var(--white);
            border-radius: 10px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .event-modal-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--white);
            font-size: 20px;
            cursor: pointer;
        }

        .event-modal-body {
            padding: 20px;
        }

        .event-detail {
            margin-bottom: 15px;
        }

        .event-detail-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .event-detail-label i {
            color: var(--primary);
            width: 16px;
        }

        .event-detail-value {
            color: var(--text);
            background-color: var(--light);
            padding: 10px;
            border-radius: 6px;
            font-size: 15px;
        }

        /* Sidebar styling */
        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header img {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 50%;
        }

        .sidebar-header span {
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-link {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: rgba(255, 255, 255, 0.5);
        }

        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }

        .sidebar-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }

        .sidebar-footer {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            position: absolute;
            bottom: 0;
            width: 100%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-footer i {
            width: 20px;
            margin-right: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
                width: calc(100% - 220px);
            }

            .calendar-day {
                min-height: 100px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }

            .page-content {
                padding: 20px;
            }

            .calendar-day {
                min-height: 90px;
                padding: 5px;
            }

            .calendar-event {
                padding: 3px 5px;
                font-size: 11px;
            }

            .search-input {
                width: 200px;
            }

            .search-input:focus {
                width: 240px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                left: -280px;
                box-shadow: none;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .toggle-btn {
                display: block !important;
            }

            .page-header {
                padding-left: 60px;
            }

            .page-content {
                padding: 15px;
            }

            .calendar-controls {
                flex-direction: column;
                align-items: flex-start;
            }

            .calendar-nav {
                width: 100%;
                justify-content: space-between;
            }

            .search-container {
                width: 100%;
                margin-left: 0;
            }

            .search-input {
                width: 100%;
            }

            .search-input:focus {
                width: 100%;
            }

            .calendar-views {
                width: 100%;
                justify-content: center;
            }

            .calendar-day {
                min-height: 80px;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.5rem;
            }

            .month-display {
                font-size: 18px;
            }

            .nav-btn {
                width: 36px;
                height: 36px;
            }

            .calendar-header {
                padding: 10px 0;
                font-size: 13px;
            }

            .day-number {
                font-size: 12px;
            }

            .today .day-number {
                width: 22px;
                height: 22px;
            }

            .calendar-event {
                font-size: 10px;
                padding: 2px 4px;
            }

            .view-btn {
                padding: 6px 12px;
                font-size: 13px;
            }

            .event-modal-content {
                max-width: 100%;
            }
        }

        /* For very small screens, change to a 1-day view on mobile */
        @media (max-width: 480px) {
            .calendar-grid {
                display: block;
            }

            .calendar-header {
                display: none;
            }

            .calendar-day {
                min-height: auto;
                padding: 10px;
                margin-bottom: 10px;
                border-radius: 8px;
            }

            .calendar-day:not(.has-events):not(.today) {
                display: none;
            }

            .calendar-day .day-number {
                text-align: left;
                font-size: 14px;
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }

            .calendar-day .day-number::before {
                content: attr(data-day-name);
                margin-right: 5px;
                font-weight: normal;
            }

            .calendar-events {
                max-height: none;
            }

            .calendar-event {
                font-size: 13px;
                padding: 6px 8px;
            }

            .today .day-number {
                width: auto;
                height: auto;
                background: none;
                color: var(--primary);
                justify-content: flex-start;
                margin-left: 0;
                border-radius: 0;
                font-weight: bold;
            }

            .more-events {
                text-align: left;
                font-size: 13px;
                margin-top: 8px;
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--lighter);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Focus states for accessibility */
        button:focus,
        a:focus,
        input:focus,
        select:focus {
            outline: 2px solid rgba(107, 13, 13, 0.5);
            outline-offset: 2px;
        }
    </style>
</head>

<body>
    <!-- Toggle Sidebar Button -->
    <button class="toggle-btn" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay (shown on mobile when sidebar is open) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>" alt="Profile">
            <span><?php echo $fullName; ?></span>
        </div>

        <div style="display: flex; flex-direction: column; padding: 10px 0;">
            <?php if ($_SESSION['user_role'] === 'inventory_admin'): ?>
                <a href="../inventoryAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Inventory Admin Panel
                </a>
            <?php elseif ($_SESSION['user_role'] === 'information_admin'): ?>
                <a href="../informationAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Information Admin Panel
                </a>
            <?php elseif ($_SESSION['user_role'] === 'super_admin'): ?>
                <a href="../superAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Super Admin Panel
                </a>
            <?php endif; ?>

            <a href="../homePage/profile.php" class="sidebar-link">
                <i class="fas fa-user"></i> Profile
            </a>

            <a href="../homePage/" class="sidebar-link">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>

            <a href="../homePage/borrowing.php" class="sidebar-link">
                <i class="fas fa-boxes"></i> Inventories
            </a>

            <a href="../homePage/memorandumHome.php" class="sidebar-link">
                <i class="fas fa-file-alt"></i> Memorandums
            </a>

            <a href="../homePage/events.php" class="sidebar-link active">
                <i class="fas fa-calendar-alt"></i> Events
            </a>

            <a href="../homePage/members.php" class="sidebar-link">
                <i class="fas fa-users"></i> Faculty Members
            </a>

            <a href="../homePage/organization.php" class="sidebar-link">
                <i class="fas fa-sitemap"></i> Organizations
            </a>

            <a href="../homePage/notification.php" class="sidebar-link">
                <i class="fas fa-bell"></i> Notifications
            </a>
        </div>

        <a href="../logout.php" class="sidebar-footer">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="page-header">
            <img src="../assets/img/CSSPE.png" alt="Logo">
            <h1 class="system-title">CSSPE Inventory & Information System</h1>
        </header>

        <!-- Content -->
        <div class="page-content">
            <div class="page-title">
                <div class="page-title-text">
                    <i class="fas fa-calendar-alt"></i> Event Calendar
                </div>
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search events...">
                </div>
            </div>

            <!-- Calendar Controls -->
            <div class="calendar-controls">
                <div class="calendar-nav">
                    <button id="prevMonth" class="nav-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="currentMonth" class="month-display">March 2025</div>
                    <button id="nextMonth" class="nav-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button id="todayBtn" class="btn btn-primary">
                        <i class="fas fa-calendar-day"></i> Today
                    </button>
                </div>
                <div class="calendar-views">
                    <button id="monthViewBtn" class="view-btn active">Month</button>
                    <button id="listViewBtn" class="view-btn">List</button>
                </div>
            </div>

            <!-- Month View Calendar -->
            <div id="monthView" class="calendar">
                <div class="calendar-grid">
                    <div class="calendar-header">Sunday</div>
                    <div class="calendar-header">Monday</div>
                    <div class="calendar-header">Tuesday</div>
                    <div class="calendar-header">Wednesday</div>
                    <div class="calendar-header">Thursday</div>
                    <div class="calendar-header">Friday</div>
                    <div class="calendar-header">Saturday</div>

                    <!-- Calendar days will be populated by JavaScript -->
                </div>
            </div>

            <!-- List View -->
            <div id="listView" class="list-view">
                <!-- List view will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="eventModal" class="event-modal">
        <div class="event-modal-content">
            <div class="event-modal-header">
                <span id="eventModalTitle">Event Details</span>
                <button class="close-modal" onclick="closeEventModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="event-modal-body">
                <div class="event-detail">
                    <div class="event-detail-label">
                        <i class="fas fa-calendar-day"></i> Date & Time
                    </div>
                    <div id="eventDateTime" class="event-detail-value">
                        March 15, 2025
                    </div>
                </div>
                <div class="event-detail">
                    <div class="event-detail-label">
                        <i class="fas fa-align-left"></i> Description
                    </div>
                    <div id="eventDescription" class="event-detail-value">
                        Event description goes here.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const monthViewBtn = document.getElementById('monthViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        const monthView = document.getElementById('monthView');
        const listView = document.getElementById('listView');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');
        const todayBtn = document.getElementById('todayBtn');
        const currentMonthDisplay = document.getElementById('currentMonth');
        const searchInput = document.getElementById('searchInput');
        const eventModal = document.getElementById('eventModal');
        const eventModalTitle = document.getElementById('eventModalTitle');
        const eventDateTime = document.getElementById('eventDateTime');
        const eventDescription = document.getElementById('eventDescription');
        const calendarGrid = document.querySelector('.calendar-grid');

        // Global variables
        let currentDate = new Date();
        let events = [];

        // Format date functions
        function formatDate(date) {
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        function formatMonthYear(date) {
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long'
            });
        }

        // Load events from PHP result
        function loadEvents() {
            // This array will store all the events from the PHP
            const loadedEvents = [];

            <?php
            // Reset the result pointer
            if (isset($result) && $result) {
                mysqli_data_seek($result, 0);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "loadedEvents.push({
                        title: '" . addslashes($row['title']) . "',
                        description: '" . addslashes($row['description']) . "',
                        date: new Date('" . addslashes($row['date_uploaded_at']) . "')
                    });\n";
                }
            }
            ?>

            return loadedEvents;
        }

        // Initialize and render the calendar
        function initCalendar() {
            // Load events from PHP
            events = loadEvents();

            // Render the current month
            renderMonth(currentDate);

            // Update month/year display
            updateMonthDisplay();

            // Render list view
            renderListView();
        }

        // Update month display text
        function updateMonthDisplay() {
            currentMonthDisplay.textContent = formatMonthYear(currentDate);
        }

        // Generate calendar days for the specified month
        function renderMonth(date) {
            // Clear previous calendar days (after the headers)
            const dayElements = calendarGrid.querySelectorAll('.calendar-day');
            dayElements.forEach(day => day.remove());

            // Get first day of the month
            const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
            // Get last day of the month
            const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

            // Get the day of the week for the first day (0 = Sunday, 6 = Saturday)
            const firstDayOfWeek = firstDay.getDay();

            // Get today's date for highlighting
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Days from previous month
            const prevMonthLastDay = new Date(date.getFullYear(), date.getMonth(), 0).getDate();

            // Add days from previous month
            for (let i = firstDayOfWeek - 1; i >= 0; i--) {
                const dayNumber = prevMonthLastDay - i;
                const dayDate = new Date(date.getFullYear(), date.getMonth() - 1, dayNumber);
                const dayElement = createDayElement(dayDate, true);
                calendarGrid.appendChild(dayElement);
            }

            // Add days from current month
            for (let i = 1; i <= lastDay.getDate(); i++) {
                const dayDate = new Date(date.getFullYear(), date.getMonth(), i);
                const isToday = dayDate.getTime() === today.getTime();
                const dayElement = createDayElement(dayDate, false, isToday);
                calendarGrid.appendChild(dayElement);
            }

            // Add days from next month to complete the grid
            const totalDaysRendered = calendarGrid.querySelectorAll('.calendar-day').length;
            const daysToAdd = 42 - totalDaysRendered; // 6 rows x 7 days = 42 cells

            for (let i = 1; i <= daysToAdd; i++) {
                const dayDate = new Date(date.getFullYear(), date.getMonth() + 1, i);
                const dayElement = createDayElement(dayDate, true);
                calendarGrid.appendChild(dayElement);
            }
        }

        // Create a day element for the calendar
        function createDayElement(date, isOtherMonth, isToday = false) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            if (isOtherMonth) dayElement.classList.add('other-month');
            if (isToday) dayElement.classList.add('today');

            // Day number
            const dayNumber = document.createElement('div');
            dayNumber.className = 'day-number';
            dayNumber.textContent = date.getDate();

            // Store day name for mobile view
            const dayName = date.toLocaleDateString('en-US', {
                weekday: 'short'
            });
            dayNumber.setAttribute('data-day-name', dayName);

            dayElement.appendChild(dayNumber);

            // Add events for this day
            const dayEvents = getEventsForDate(date);
            if (dayEvents.length > 0) {
                dayElement.classList.add('has-events');
                const eventsContainer = document.createElement('div');
                eventsContainer.className = 'calendar-events';

                // Limit visible events to 3, with a "more" link for the rest
                const visibleEvents = Math.min(dayEvents.length, 3);
                for (let i = 0; i < visibleEvents; i++) {
                    const eventElement = document.createElement('div');
                    eventElement.className = 'calendar-event';
                    eventElement.textContent = dayEvents[i].title;

                    // Store event data as a data attribute
                    eventElement.dataset.eventIndex = events.indexOf(dayEvents[i]);

                    // Add click event to show details
                    eventElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                        showEventDetails(dayEvents[i]);
                    });

                    eventsContainer.appendChild(eventElement);
                }

                // Add "more" indicator if needed
                if (dayEvents.length > 3) {
                    const moreElement = document.createElement('div');
                    moreElement.className = 'more-events';
                    moreElement.textContent = `+${dayEvents.length - 3} more`;

                    // Add click event to show all events for this day
                    moreElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                        // TODO: Implement a day view or event list for this day
                        showAllEventsForDay(date, dayEvents);
                    });

                    eventsContainer.appendChild(moreElement);
                }

                dayElement.appendChild(eventsContainer);
            }

            return dayElement;
        }

        // Get events for a specific date
        function getEventsForDate(date) {
            const year = date.getFullYear();
            const month = date.getMonth();
            const day = date.getDate();

            // Filter events that match this date
            return events.filter(event => {
                const eventDate = new Date(event.date);
                return eventDate.getFullYear() === year &&
                    eventDate.getMonth() === month &&
                    eventDate.getDate() === day;
            });
        }

        // Show event details in modal
        function showEventDetails(event) {
            eventModalTitle.textContent = event.title;
            eventDateTime.textContent = formatDate(new Date(event.date));
            eventDescription.textContent = event.description || 'No description available';

            // Show the modal
            eventModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent body scrolling
        }

        // Close event modal
        function closeEventModal() {
            eventModal.style.display = 'none';
            document.body.style.overflow = ''; // Restore body scrolling
        }

        // Show all events for a day
        function showAllEventsForDay(date, dayEvents) {
            // Switch to list view
            switchView('list');

            // Highlight the events for this day
            const formattedDate = formatDate(date);

            // Scroll to the date section in list view
            const listDays = document.querySelectorAll('.list-day');
            for (const day of listDays) {
                if (day.textContent.includes(formattedDate)) {
                    day.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    // Highlight the section
                    day.style.color = 'var(--primary-dark)';
                    day.style.fontSize = '1.1em';

                    // Reset after a delay
                    setTimeout(() => {
                        day.style.color = '';
                        day.style.fontSize = '';
                    }, 2000);

                    break;
                }
            }
        }

        // Render list view of events
        function renderListView() {
            // Clear the list view
            listView.innerHTML = '';

            // Clone the events array to avoid modifying the original
            const sortedEvents = [...events].sort((a, b) => new Date(a.date) - new Date(b.date));

            // Get the search term
            const searchTerm = searchInput.value.toLowerCase();

            // Filter events by search term if needed
            const filteredEvents = searchTerm ?
                sortedEvents.filter(event =>
                    event.title.toLowerCase().includes(searchTerm) ||
                    (event.description && event.description.toLowerCase().includes(searchTerm))
                ) : sortedEvents;

            if (filteredEvents.length === 0) {
                const emptyState = document.createElement('div');
                emptyState.className = 'empty-state';
                emptyState.innerHTML = `
                    <i class="fas fa-calendar-times"></i>
                    <h3>No events found</h3>
                    <p>There are no events matching your search criteria.</p>
                `;
                listView.appendChild(emptyState);
                return;
            }

            // Group events by date
            const eventsByDate = {};

            filteredEvents.forEach(event => {
                const dateStr = formatDate(new Date(event.date));
                if (!eventsByDate[dateStr]) {
                    eventsByDate[dateStr] = [];
                }
                eventsByDate[dateStr].push(event);
            });

            // Create a header for the list
            const header = document.createElement('div');
            header.className = 'list-header';
            header.innerHTML = `
                <span>Event</span>
                <span>Date</span>
            `;
            listView.appendChild(header);

            // Add each date group
            Object.keys(eventsByDate).forEach(dateStr => {
                const dayHeader = document.createElement('div');
                dayHeader.className = 'list-day';
                dayHeader.textContent = dateStr;
                listView.appendChild(dayHeader);

                // Add events for this day
                eventsByDate[dateStr].forEach(event => {
                    const eventElement = document.createElement('div');
                    eventElement.className = 'list-event';
                    eventElement.innerHTML = `
                        <div class="list-event-color"></div>
                        <div class="list-event-content">
                            <div class="list-event-title">${event.title}</div>
                            <div class="list-event-time">${formatDate(new Date(event.date))}</div>
                            <div class="list-event-desc">${event.description || 'No description available'}</div>
                        </div>
                    `;

                    // Add click event to show details
                    eventElement.addEventListener('click', () => {
                        showEventDetails(event);
                    });

                    listView.appendChild(eventElement);
                });
            });
        }

        // Switch between month and list views
        function switchView(view) {
            if (view === 'month') {
                monthView.style.display = 'block';
                listView.style.display = 'none';
                monthViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
            } else {
                monthView.style.display = 'none';
                listView.style.display = 'flex';
                monthViewBtn.classList.remove('active');
                listViewBtn.classList.add('active');

                // Make sure list view is rendered
                renderListView();
            }
        }

        // Go to previous month
        function goToPrevMonth() {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
            renderMonth(currentDate);
            updateMonthDisplay();
        }

        // Go to next month
        function goToNextMonth() {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
            renderMonth(currentDate);
            updateMonthDisplay();
        }

        // Go to today
        function goToToday() {
            currentDate = new Date();
            renderMonth(currentDate);
            updateMonthDisplay();
        }

        // Check if on mobile and show toggle button
        function checkMobile() {
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
                sidebar.classList.remove('active');
                mainContent.style.marginLeft = '0';

                // Check if sidebar is showing and hide it
                if (sidebar.style.left === '0px') {
                    sidebar.style.left = '-280px';
                    sidebarOverlay.classList.remove('active');
                }
            } else {
                toggleBtn.style.display = 'none';
                sidebar.style.left = '0';
                mainContent.style.marginLeft = sidebar.offsetWidth + 'px';
                mainContent.style.width = `calc(100% - ${sidebar.offsetWidth}px)`;
            }
        }

        // Toggle sidebar on mobile
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');

            if (sidebar.classList.contains('active')) {
                sidebar.style.left = '0';
                sidebarOverlay.classList.add('active');
            } else {
                sidebar.style.left = '-280px';
                sidebarOverlay.classList.remove('active');
            }
        });

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebar.style.left = '-280px';
            sidebarOverlay.classList.remove('active');
        });

        // View switch event listeners
        monthViewBtn.addEventListener('click', () => switchView('month'));
        listViewBtn.addEventListener('click', () => switchView('list'));

        // Month navigation listeners
        prevMonthBtn.addEventListener('click', goToPrevMonth);
        nextMonthBtn.addEventListener('click', goToNextMonth);
        todayBtn.addEventListener('click', goToToday);

        // Search events
        searchInput.addEventListener('input', function() {
            if (monthViewBtn.classList.contains('active')) {
                // If in month view, re-render the current month
                renderMonth(currentDate);
            } else {
                // If in list view, re-render the list
                renderListView();
            }
        });

        // Close modal when clicking outside
        eventModal.addEventListener('click', function(e) {
            if (e.target === eventModal) {
                closeEventModal();
            }
        });

        // Initialize the calendar when the page loads
        window.addEventListener('load', function() {
            initCalendar();
            checkMobile();
        });

        window.addEventListener('resize', checkMobile);
    </script>
</body>

</html>