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

$limit = 12;

// Get the current page or default to 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the starting point for the query
$offset = ($page - 1) * $limit;

// Query with LIMIT and OFFSET
$query = "SELECT * FROM memorandums LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$totalQuery = "SELECT COUNT(*) AS total FROM memorandums";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalItems = $totalRow['total'];

$totalPages = ceil($totalItems / $limit);

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
    <title>Memorandums</title>

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
            --secondary: #6c757d;
            --secondary-dark: #5a6268;
            --light: #f9f9f9;
            --lighter: #f5f5f5;
            --border: #f0f0f0;
            --text: #333;
            --text-light: #666;
            --white: #fff;
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

        .btn-secondary {
            background-color: var(--secondary);
            color: var(--white);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
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

        /* Search and filter styling */
        .search-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-bar {
            flex: 1;
            min-width: 200px;
            padding: 12px 20px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            background-color: var(--white);
        }

        .search-bar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 2px 15px rgba(107, 13, 13, 0.1);
        }

        .search-container::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 16px;
            z-index: 10;
        }

        .filter-dropdown {
            min-width: 180px;
            padding: 12px 18px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(107, 13, 13, 0.2);
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 15px) center;
            padding-right: 35px;
        }

        .filter-dropdown:hover {
            background-color: var(--primary-dark);
        }

        .filter-dropdown:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.3);
        }

        /* Memorandums grid */
        .memorandums-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 0;
            margin: 0 auto;
        }

        .memorandum-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .memorandum-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(107, 13, 13, 0.15);
            border-color: rgba(107, 13, 13, 0.1);
        }

        .memorandum-title {
            padding: 18px;
            border-bottom: 1px solid var(--border);
            background-color: var(--white);
        }

        .memorandum-title p {
            margin: 0;
            font-weight: bold;
            font-size: 17px;
            color: var(--text);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .memorandum-date {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            color: var(--text-light);
            font-size: 15px;
            flex-grow: 1;
        }

        .memorandum-date p {
            margin: 0;
            display: flex;
            align-items: center;
        }

        .memorandum-date p::before {
            content: "\f073";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            color: var(--primary);
        }

        .memorandum-actions {
            padding: 12px 18px 18px;
            display: flex;
            justify-content: center;
            margin-top: auto;
            background-color: var(--light);
            border-top: 1px solid var(--border);
        }

        .view-button {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            max-width: 140px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(107, 13, 13, 0.2);
        }

        .view-button:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 10px rgba(107, 13, 13, 0.3);
        }

        .view-button i {
            font-size: 14px;
        }

        /* Modal styling */
        .modal-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            padding: 0;
            overflow: hidden;
            animation: slideUp 0.3s ease;
            position: relative;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
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

        .modal-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 18px 20px;
            font-size: 18px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header p {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-body {
            padding: 0;
            overflow-y: auto;
            flex: 1;
        }

        .modal-image {
            display: flex;
            justify-content: center;
            padding: 25px;
            background-color: var(--lighter);
            border-bottom: 1px solid var(--border);
        }

        .modal-image img {
            max-width: 100%;
            height: auto;
            max-height: 220px;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .modal-details {
            padding: 20px 25px;
        }

        .detail-group {
            margin-bottom: 18px;
        }

        .detail-group:last-child {
            margin-bottom: 0;
        }

        .detail-group label {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
            display: block;
            font-size: 15px;
            display: flex;
            align-items: center;
        }

        .detail-group label i {
            color: var(--primary);
            margin-right: 8px;
            width: 20px;
            text-align: center;
        }

        .detail-group p {
            margin: 0;
            color: var(--text-light);
            background-color: var(--lighter);
            padding: 12px 15px;
            border-radius: 8px;
            line-height: 1.5;
            font-size: 15px;
        }

        .modal-actions {
            padding: 15px 25px 25px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid var(--border);
            background-color: var(--light);
        }

        .download-button,
        .close-button {
            min-width: 110px;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .download-button {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            box-shadow: 0 2px 5px rgba(107, 13, 13, 0.2);
        }

        .download-button:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 10px rgba(107, 13, 13, 0.3);
        }

        .close-button {
            background-color: var(--secondary);
            color: var(--white);
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .close-button:hover {
            background-color: var(--secondary-dark);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* Pagination styling */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 40px 0;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pagination a {
            color: var(--primary);
            text-decoration: none;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 30px;
            transition: all 0.3s;
            font-weight: 500;
            min-width: 18px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pagination a:hover {
            background-color: var(--light);
            border-color: var(--primary);
        }

        .pagination a.active {
            background-color: var(--primary);
            color: var(--white);
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(107, 13, 13, 0.2);
        }

        .pagination a.prev,
        .pagination a.next {
            background-color: var(--light);
            min-width: 100px;
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
            margin-bottom: 10px;
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .page-title i {
            margin-right: 12px;
            background-color: var(--light);
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--primary);
        }

        .page-description {
            color: var(--text-light);
            margin-bottom: 25px;
            font-size: 16px;
            max-width: 800px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 20px;
        }

        /* Empty state styling */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px 20px;
            background-color: var(--light);
            border-radius: 10px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state h3 {
            color: var(--text-light);
            margin-bottom: 10px;
            font-size: 18px;
        }

        .empty-state p {
            color: #888;
            max-width: 400px;
            margin: 0 auto;
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

            .memorandums-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 20px;
            }

            .page-title {
                font-size: 1.6rem;
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

            .memorandums-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 15px;
            }

            .page-content {
                padding: 20px;
            }

            .memorandum-title {
                padding: 15px;
            }

            .memorandum-title p {
                font-size: 16px;
            }

            .memorandum-date,
            .memorandum-actions {
                padding: 10px 15px;
            }

            .memorandum-date {
                font-size: 14px;
            }

            .view-button {
                font-size: 13px;
                padding: 8px 12px;
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
                padding-top: 0;
            }

            .toggle-btn {
                display: block !important;
            }

            .page-header {
                padding-left: 60px;
            }

            .search-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar,
            .filter-dropdown {
                width: 100%;
            }

            .memorandums-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }

            .page-content {
                padding: 15px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .page-title i {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .modal-content {
                width: 95%;
            }

            /* Make header text smaller on mobile */
            h1 {
                font-size: 1rem !important;
            }

            h2 {
                font-size: 1.3rem !important;
            }
        }

        @media (max-width: 576px) {
            .memorandums-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 12px;
            }

            .memorandum-title {
                padding: 12px;
            }

            .memorandum-title p {
                font-size: 15px;
            }

            .memorandum-date,
            .memorandum-actions {
                padding: 8px 12px;
            }

            .memorandum-date {
                font-size: 13px;
            }

            .view-button {
                font-size: 12px;
                padding: 7px 10px;
                max-width: 100%;
            }

            .pagination a {
                padding: 8px 12px;
                font-size: 14px;
                min-width: 15px;
            }

            .pagination a.prev,
            .pagination a.next {
                min-width: 80px;
            }

            .modal-header {
                padding: 15px;
                font-size: 16px;
            }

            .modal-image {
                padding: 15px;
            }

            .modal-details {
                padding: 15px;
            }

            .detail-group label {
                font-size: 14px;
            }

            .detail-group p {
                padding: 10px;
                font-size: 14px;
            }

            .modal-actions {
                padding: 12px 15px 15px;
                gap: 8px;
            }

            .download-button,
            .close-button {
                min-width: 100px;
                padding: 8px 12px;
                font-size: 13px;
            }
        }

        @media (max-width: 400px) {
            .memorandums-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .memorandum-card {
                max-width: 100%;
            }

            .pagination a {
                padding: 6px 10px;
                min-width: 32px;
                font-size: 13px;
            }

            .pagination a.prev,
            .pagination a.next {
                min-width: 60px;
                font-size: 12px;
            }

            .page-title {
                font-size: 1.3rem;
            }

            .page-title i {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }
        }

        /* Focus states for accessibility */
        button:focus,
        a:focus,
        input:focus,
        select:focus {
            outline: 2px solid rgba(107, 13, 13, 0.5);
            outline-offset: 2px;
        }

        /* Print styles */
        @media print {

            .sidebar,
            .search-container,
            .memorandum-actions,
            .toggle-btn,
            .pagination,
            .modal-actions {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .memorandums-grid {
                display: block;
            }

            .memorandum-card {
                page-break-inside: avoid;
                break-inside: avoid;
                margin-bottom: 15px;
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .modal-container {
                position: relative;
                display: block !important;
                height: auto;
                background: none;
                backdrop-filter: none;
            }

            .modal-content {
                box-shadow: none;
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Toggle Sidebar Button -->
    <button class="toggle-btn btn btn-primary">
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

            <a href="../homePage/memorandumHome.php" class="sidebar-link active">
                <i class="fas fa-file-alt"></i> Memorandums
            </a>

            <a href="../homePage/events.php" class="sidebar-link">
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
            <h2 class="page-title">
                <i class="fas fa-file-alt"></i> Memorandums
            </h2>

            <p class="page-description">Access and download official memorandums and documents from CSSPE.</p>

            <!-- Search & Filter -->
            <div class="search-container">
                <input class="search-bar" type="text" placeholder="Search memorandums..." oninput="searchCard()">
                <select class="filter-dropdown" id="filterDropdown" onchange="filterByDate()">
                    <option value="">Filter by date</option>
                    <option value="all">All dates</option>
                    <option value="day">Today only</option>
                    <option value="week">This week</option>
                    <option value="month">This month</option>
                </select>
            </div>

            <!-- Memorandum Cards -->
            <div class="memorandums-grid" id="inventoryContainer">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="memorandum-card">
                        <div class="memorandum-title">
                            <p><?php echo htmlspecialchars($row['title']); ?></p>
                        </div>
                        <div class="memorandum-date">
                            <p><?php echo date('F j, Y', strtotime($row['uploaded_at'])); ?></p>
                        </div>
                        <div class="memorandum-actions">
                            <button class="view-button" onclick="editProgram(
                        '<?php echo addslashes(htmlspecialchars($row['title'])); ?>', 
                        '<?php echo addslashes(htmlspecialchars($row['description'])); ?>',
                        '<?php echo addslashes(htmlspecialchars($row['file_path'])); ?>',
                        '<?php echo date('F j, Y', strtotime($row['uploaded_at'])); ?>'
                        )">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="prev">
                        <i class="fas fa-chevron-left" style="margin-right: 5px;"></i>Previous
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="next">
                        Next<i class="fas fa-chevron-right" style="margin-left: 5px;"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-container" id="memorandumModal">
        <div class="modal-content">
            <div class="modal-header">
                <p><i class="fas fa-file-alt"></i> View Memorandum</p>
            </div>

            <div class="modal-body">
                <div class="modal-image">
                    <img id="memorandumImage" src="../assets/img/freepik-untitled-project-20241018143133NtJY.png" alt="Memorandum preview">
                </div>

                <div class="modal-details">
                    <div class="detail-group">
                        <label><i class="fas fa-heading"></i>Title:</label>
                        <p id="memorandumTitle"></p>
                    </div>

                    <div class="detail-group">
                        <label><i class="fas fa-align-left"></i>Description:</label>
                        <p id="memorandumDescription"></p>
                    </div>

                    <div class="detail-group">
                        <label><i class="fas fa-calendar-day"></i>Date Uploaded:</label>
                        <p id="memorandumUploadedAt"></p>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <a id="memorandumDownloadLink" href="#" download>
                    <button class="download-button">
                        <i class="fas fa-download"></i> Download
                    </button>
                </a>
                <button onclick="cancelContainer()" class="close-button">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- The rest of your HTML remains the same until the script section -->

    <!-- Replace your existing script section with this one -->
    <script>
        // Elements
        const toggleBtn = document.querySelector('.toggle-btn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const memorandumModal = document.getElementById('memorandumModal');

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

        // Run on page load
        window.addEventListener('load', checkMobile);
        window.addEventListener('resize', checkMobile);

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

        // Updated function to properly handle file paths and downloads
        function editProgram(title, description, filePath, uploadedAt) {
            memorandumModal.style.display = 'flex';

            // Set the text content for title, description and date
            document.getElementById('memorandumDescription').textContent = description || 'No description available';
            document.getElementById('memorandumTitle').textContent = title || 'Untitled';
            document.getElementById('memorandumUploadedAt').textContent = uploadedAt || 'Unknown date';

            // Set the download link with proper attributes
            const downloadLink = document.getElementById('memorandumDownloadLink');

            // Ensure file path is complete and handle the download properly
            if (filePath && filePath !== '#') {
                // If the path doesn't start with "../assets/uploads/" add it
                let fullFilePath = filePath;
                if (!filePath.startsWith('../assets/uploads/')) {
                    fullFilePath = '../assets/uploads/' + filePath;
                }

                downloadLink.href = fullFilePath;
                downloadLink.setAttribute('download', ''); // Enable download attribute
                downloadLink.style.display = 'inline-block'; // Make sure it's visible

                // Extract filename from path for better download experience
                const fileName = fullFilePath.split('/').pop();
                if (fileName) {
                    downloadLink.setAttribute('download', fileName);
                }

                // Update the image preview based on file type
                const fileExtension = (fullFilePath.split('.').pop() || '').toLowerCase();
                const imageEl = document.getElementById('memorandumImage');

                if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                    // If it's an image, show the actual image
                    imageEl.src = fullFilePath;
                } else if (fileExtension === 'pdf') {
                    // If it's a PDF, show PDF icon
                    imageEl.src = '../assets/img/freepik-untitled-project-20241018143133NtJY.png';
                } else {
                    // For other file types, show a generic document icon
                    imageEl.src = '../assets/img/freepik-untitled-project-20241018143133NtJY.png';
                }
            } else {
                // If no file path, hide the download button
                downloadLink.style.display = 'none';
                // Set a default image
                document.getElementById('memorandumImage').src = '../assets/img/freepik-untitled-project-20241018143133NtJY.png';
            }

            // Prevent body scrolling when modal is open
            document.body.style.overflow = 'hidden';
        }

        function cancelContainer() {
            memorandumModal.style.display = 'none';
            // Re-enable body scrolling when modal is closed
            document.body.style.overflow = '';
        }

        // Close modal when clicking outside of it
        memorandumModal.addEventListener('click', function(e) {
            if (e.target === this) {
                cancelContainer();
            }
        });

        function filterByDate() {
            const filterValue = document.getElementById('filterDropdown').value;
            const cards = document.querySelectorAll('.memorandum-card');
            const today = new Date();

            cards.forEach(card => {
                const dateEl = card.querySelector('.memorandum-date p');
                if (!dateEl) return;

                const uploadedAtText = dateEl.textContent;
                const uploadedAt = new Date(uploadedAtText);
                let showCard = true;

                if (filterValue === 'day') {
                    showCard = uploadedAt.toDateString() === today.toDateString();
                } else if (filterValue === 'week') {
                    // Calculate the start and end of the week (consider Sunday as the start)
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay()); // Sunday
                    const weekEnd = new Date(today);
                    weekEnd.setDate(today.getDate() - today.getDay() + 6); // Saturday
                    showCard = uploadedAt >= weekStart && uploadedAt <= weekEnd;
                } else if (filterValue === 'month') {
                    showCard = uploadedAt.getMonth() === today.getMonth() && uploadedAt.getFullYear() === today.getFullYear();
                } else if (filterValue === 'all' || filterValue === '') {
                    showCard = true;
                }

                card.style.display = showCard ? '' : 'none';
            });

            // Show a message if no results are found
            checkNoResults();
        }

        function searchCard() {
            const searchQuery = document.querySelector('.search-bar').value.toLowerCase();
            const cards = document.querySelectorAll('.memorandum-card');

            cards.forEach(card => {
                const titleEl = card.querySelector('.memorandum-title p');
                if (!titleEl) return;

                const title = titleEl.textContent.toLowerCase();
                if (title.includes(searchQuery)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            // Show a message if no results are found
            checkNoResults();
        }

        function checkNoResults() {
            const container = document.getElementById('inventoryContainer');
            const cards = document.querySelectorAll('.memorandum-card');
            let visibleCards = 0;

            cards.forEach(card => {
                if (card.style.display !== 'none') {
                    visibleCards++;
                }
            });

            // Remove existing no results message if it exists
            const existingMessage = document.getElementById('noResultsMessage');
            if (existingMessage) {
                existingMessage.remove();
            }

            // Add a message if no cards are visible
            if (visibleCards === 0) {
                const noResults = document.createElement('div');
                noResults.id = 'noResultsMessage';
                noResults.className = 'empty-state';
                noResults.innerHTML = `
            <i class="fas fa-search"></i>
            <h3>No memorandums found</h3>
            <p>Try adjusting your search or filter criteria.</p>
        `;
                container.appendChild(noResults);
            }
        }
    </script>
</body>

</html>