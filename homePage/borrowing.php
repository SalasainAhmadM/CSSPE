<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);
$userid = $_SESSION['user_id'];

$query_notifications = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";
$result_notifications = mysqli_query($conn, $query_notifications);
$notificationCount = 0;

if ($result_notifications && $row_notifications = mysqli_fetch_assoc($result_notifications)) {
    $notificationCount = $row_notifications['notification_count'];
}

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

$limit = 6;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$itemsQuery = "SELECT * FROM items LIMIT $limit OFFSET $offset";
$itemsResult = $conn->query($itemsQuery);

$totalitemsQuery = "SELECT COUNT(*) AS total FROM items";
$totalitemsResult = $conn->query($totalitemsQuery);
$totalRow = mysqli_fetch_assoc($totalitemsResult);
$totalItems = $totalRow['total'];

$totalPages = ceil($totalItems / $limit);

// Fetch users with role 'Instructor'
$teacherQuery = "SELECT id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name FROM users WHERE role = 'Instructor'";
$teacherResult = $conn->query($teacherQuery);

$originMultipleQuery = "SELECT id, name, brand FROM items";
$originMultipleResult = $conn->query($originMultipleQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing</title>

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Base styles */
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            scroll-behavior: smooth;
        }

        * {
            box-sizing: border-box;
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

        .bg-dark-red {
            background-color: var(--primary);
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

        /* SweetAlert2 custom styles */
        .swal-title-custom {
            font-size: 20px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .swal-html-custom {
            text-align: left;
        }

        .swal-popup-custom {
            border-radius: 12px;
            padding: 20px;
        }

        /* Inventory Grid Styling */
        .inventoryGrid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 25px;
            margin-top: 15px;
        }

        .inventoryContainer {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            border: 1px solid var(--border);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .inventoryContainer:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(107, 13, 13, 0.15);
            border-color: rgba(107, 13, 13, 0.1);
        }

        .subInventoryContainer {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .imageContainer {
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            background-color: var(--light);
            border-bottom: 1px solid var(--border);
            height: 150px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .imageContainer:hover {
            background-color: var(--lighter);
        }

        .imageContainer:hover:after {
            content: "Click for details";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 8px 0;
            background-color: rgba(107, 13, 13, 0.7);
            color: var(--white);
            font-size: 12px;
            text-align: center;
        }

        .imageContainer img {
            max-height: 110px;
            max-width: 90%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .imageContainer:hover img {
            transform: scale(1.05);
        }

        .infoContainer {
            padding: 15px 15px 5px;
            border-bottom: 1px dashed var(--border);
        }

        .infoContainer p {
            margin: 0;
            font-weight: bold;
            font-size: 18px;
            color: var(--primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .infoContainer1 {
            padding: 8px 15px;
            display: flex;
            align-items: center;
            min-height: 24px;
        }

        .infoContainer1 p {
            margin: 0;
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .infoContainer1 i {
            flex-shrink: 0;
            margin-right: 8px;
            width: 16px;
            text-align: center;
            color: var(--primary);
        }

        .infoContainer1:last-of-type {
            background-color: var(--light);
            font-weight: 500;
            color: var(--text);
            margin-top: auto;
        }

        .buttonContainer {
            padding: 15px;
            display: flex;
            justify-content: center;
            background-color: var(--light);
            border-top: 1px solid var(--border);
            margin-top: auto;
        }

        .addButton,
        .confirmButton {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            width: 100%;
            max-width: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(107, 13, 13, 0.2);
        }

        .addButton:hover,
        .confirmButton:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 10px rgba(107, 13, 13, 0.3);
        }

        .addButton1 {
            background-color: var(--secondary);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .addButton1:hover {
            background-color: var(--secondary-dark);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* Search bar */
        .searchAddContainer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 25px;
            max-width: 600px;
        }

        .searchContainer {
            flex-grow: 1;
            max-width: 70%;
        }

        .addButton.size {
            margin-left: 30px;
            white-space: nowrap;
        }


        .searchBar {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .searchBar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 2px 15px rgba(107, 13, 13, 0.1);
        }

        .searchContainer::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 16px;
        }

        /* Pagination */
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

        /* Modal Styling */
        .editContainer {
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

        .subAddContainer {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
            padding: 0;
            overflow: hidden;
            animation: slideUp 0.3s ease;
            position: relative;
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

        .titleContainer {
            background-color: var(--primary);
            color: var(--white);
            padding: 20px;
            font-size: 20px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }

        .titleContainer p {
            margin: 0;
        }

        .subLoginContainer {
            padding: 25px;
        }

        .inputContainer {
            margin-bottom: 20px;
        }

        .inputContainer label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
            font-size: 15px;
        }

        .inputEmail {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s;
            background-color: var(--light);
        }

        .inputEmail:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.1);
            outline: none;
            background-color: var(--white);
        }

        .inputEmail:read-only {
            background-color: var(--lighter);
            color: #555;
            /* cursor: not-allowed; */
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

        /* Header styling */
        .header {
            background-color: var(--primary);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .header h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* Page content */
        .page-content {
            padding: 20px;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            border-bottom: 2px solid var(--border);
            padding-bottom: 15px;
        }

        .page-title i {
            margin-right: 15px;
            background-color: var(--lighter);
            padding: 12px;
            border-radius: 50%;
            color: var(--primary);
        }

        .page-title span {
            font-size: 16px;
            font-weight: normal;
            margin-left: 15px;
            color: var(--text-light);
        }

        /* Empty state */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background-color: var(--light);
            border-radius: 10px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
            display: block;
        }

        .empty-state h3 {
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #888;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Multiple Borrow */
        .addContainerMultiple {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .subAddContainerMultiple {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 1200px;
            overflow-y: auto;
            max-height: 90vh;
        }

        .subLoginContainerMultiple {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .itemRow,
        .detailsRow {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .inputContainer {
            flex: 1;
        }

        .addMoreButton {
            background: none;
            border: none;
            cursor: pointer;
            color: #6B0D0D;
            font-size: 16px;
            padding: 5px;
            margin-left: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .addMoreButton:hover {
            color: #4a0a0a;
        }

        .inputContainer input[type="number"] {
            padding-right: 40px;
        }

        .deleteRowButton {
            background: none;
            border: none;
            cursor: pointer;
            color: #d9534f;
            font-size: 16px;
            padding: 5px;
            margin-left: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .deleteRowButton:hover {
            color: #c9302c;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .subAddContainerMultiple {
                width: 95%;
                padding: 15px;
            }

            .itemRow,
            .detailsRow {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }

            .inputContainer {
                width: 100%;
            }

            .inputContainer input,
            .inputContainer select {
                width: 100%;
                font-size: 14px;
            }

            .addMoreButton {
                font-size: 14px;
            }

            .deleteRowButton {
                font-size: 14px;
            }

            .titleContainer {
                font-size: 18px;
            }

            .inputContainer label {
                font-size: 14px;
            }

            .inputContainer[style*="position: relative"] {
                flex-direction: column;
            }

            .inputContainer[style*="position: relative"] input {
                width: 100%;
            }

            .inputContainer[style*="position: relative"] .addMoreButton {
                margin-left: 0;
                margin-top: 10px;
            }


        }


        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
            }

            .inventoryGrid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .inventoryGrid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                padding: 20px 15px;
                gap: 15px;
            }

            .imageContainer {
                height: 120px;
            }

            .imageContainer img {
                max-height: 90px;
            }

            .page-title {
                font-size: 1.6rem;
            }

            .searchContainer {
                margin: 0 15px;
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

            .header {
                padding-left: 60px;
            }

            .inventoryGrid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                padding: 15px 10px;
                gap: 15px;
            }

            .imageContainer {
                height: 110px;
                padding: 15px;
            }

            .imageContainer img {
                max-height: 80px;
            }

            .infoContainer p {
                font-size: 16px;
            }

            .infoContainer1 p {
                font-size: 13px;
            }

            .searchContainer {
                max-width: 100%;
            }

            /* Make header text smaller on mobile */
            h1 {
                font-size: 1rem !important;
            }

            h2 {
                font-size: 1.3rem !important;
            }

            .page-title span {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .inventoryGrid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                padding: 10px;
                gap: 12px;
            }

            .searchBar {
                font-size: 14px;
                padding: 10px 15px 10px 40px;
            }

            .imageContainer {
                height: 100px;
                padding: 10px;
            }

            .imageContainer img {
                max-height: 70px;
            }

            .infoContainer {
                padding: 10px 10px 5px;
            }

            .infoContainer p {
                font-size: 14px;
            }

            .infoContainer1 {
                padding: 5px 10px;
            }

            .infoContainer1 p {
                font-size: 12px;
            }

            .buttonContainer {
                padding: 10px;
            }

            .addButton {
                padding: 8px 12px;
                font-size: 13px;
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

            .subLoginContainer {
                padding: 20px 15px;
            }

            .titleContainer {
                padding: 15px;
                font-size: 18px;
            }

            .inputEmail {
                padding: 10px 12px;
                font-size: 14px;
            }

            .page-title i {
                padding: 10px;
                font-size: 14px;
            }
        }

        @media (max-width: 400px) {
            .inventoryGrid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .imageContainer {
                height: 130px;
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
        }

        /* Focus states for accessibility */
        button:focus,
        a:focus,
        input:focus,
        select:focus {
            outline: 2px solid rgba(107, 13, 13, 0.5);
            outline-offset: 2px;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body.dark-mode-supported {
                --light: #2a2a2a;
                --lighter: #222;
                --border: #333;
                --text: #eee;
                --text-light: #ccc;
                --white: #1a1a1a;

                background-color: #181818;
                color: #eee;
            }

            body.dark-mode-supported .searchBar,
            body.dark-mode-supported .inputEmail {
                background-color: #222;
                border-color: #444;
                color: #eee;
            }

            body.dark-mode-supported .pagination a {
                background-color: #222;
                border-color: #444;
                color: #eee;
            }
        }

        /* Print styles */
        @media print {

            .sidebar,
            .searchContainer,
            .buttonContainer,
            .toggle-btn {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .inventoryGrid {
                display: block;
            }

            .inventoryContainer {
                page-break-inside: avoid;
                break-inside: avoid;
                margin-bottom: 15px;
                box-shadow: none !important;
                border: 1px solid #ddd !important;
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

            <a href="../homePage/borrowing.php" class="sidebar-link active">
                <i class="fas fa-boxes"></i> Inventories
            </a>

            <a href="../homePage/memorandumHome.php" class="sidebar-link">
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
        <div class="header">
            <img src="/dionSe/assets/img/CSSPE.png" alt="Logo">
            <h1>CSSPE Inventory & Information System</h1>
        </div>

        <!-- Content -->
        <div class="page-content">
            <div class="content-container">
                <h2 class="page-title">
                    <i class="fas fa-boxes"></i>
                    Inventory Items
                    <span>Browse and borrow available items</span>
                </h2>

                <div class="searchAddContainer">
                    <div class="searchContainer">
                        <input id="searchBar" class="searchBar" type="text" placeholder="Search by item name...">
                    </div>
                    <button onclick="addMultiple()" class="addButton size">
                        <i class="fas fa-plus"></i> Borrow Multiple Items
                    </button>
                </div>
                <!-- Inventory Grid -->
                <div id="inventoryGrid" class="inventoryGrid">
                    <?php if ($itemsResult->num_rows > 0): ?>
                        <?php while ($item = $itemsResult->fetch_assoc()): ?>
                            <div class="inventoryContainer" data-title="<?= htmlspecialchars($item['name']) ?>">
                                <div class="subInventoryContainer">
                                    <div class="imageContainer"
                                        onclick="showNote('<?= htmlspecialchars($item['note'] ?: 'No note available') ?>')">
                                        <img class="image"
                                            src="../assets/uploads/<?= htmlspecialchars($item['image'] ?: '../../assets/img/CSSPE.png') ?>"
                                            alt="Item Image">
                                    </div>

                                    <div class="infoContainer">
                                        <p><?= htmlspecialchars($item['name']) ?></p>
                                    </div>
                                    <div class="infoContainer1">
                                        <i class="fas fa-tag"></i>
                                        <p><?= htmlspecialchars($item['brand']) ?></p>
                                    </div>
                                    <div class="infoContainer1">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?= htmlspecialchars($item['description']) ?></p>
                                    </div>
                                    <div class="infoContainer1">
                                        <i class="fas fa-cubes"></i>
                                        <p><strong>Available:</strong> <?= htmlspecialchars($item['quantity']) ?></p>
                                    </div>
                                    <div class="buttonContainer">
                                        <button
                                            onclick="borrowItem(<?= htmlspecialchars($item['id']) ?>, '<?= htmlspecialchars($item['name']) ?>', '<?= htmlspecialchars($item['brand']) ?>')"
                                            class="addButton">
                                            <i class="fas fa-hand-holding"></i>Borrow
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h3>No items available</h3>
                            <p>There are currently no inventory items available in the system.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="prev">
                            <i class="fas fa-chevron-left" style="margin-right: 5px;"></i>Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>"
                            class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="next">
                            Next<i class="fas fa-chevron-right" style="margin-left: 5px;"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Modal for borrowing item -->
        <div class="editContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p><i class="fas fa-hand-holding" style="margin-right: 10px;"></i>Borrowed Item</p>
                </div>

                <div class="subLoginContainer">
                    <!-- hidden id -->
                    <div class="inputContainer" style="display: none;">
                        <input id="itemId" type="hidden">
                    </div>

                    <!-- Item Name -->
                    <div class="inputContainer">
                        <label>Item Name:</label>
                        <input id="itemName" class="inputEmail" type="text" readonly>
                    </div>

                    <!-- Brand Selection Dropdown -->
                    <div class="inputContainer">
                        <label>Brand:</label>
                        <select id="itemBrand" class="inputEmail">
                            <option value="" disabled selected>Select a Brand</option>
                        </select>
                    </div>

                    <!-- Available Quantity -->
                    <div class="inputContainer">
                        <label>Available:</label>
                        <input id="available_quantity" placeholder="Available Quantity" class="inputEmail" type="text"
                            readonly>
                    </div>


                    <input id="teacherSelect" class="inputEmail" value="<?php echo htmlspecialchars($userid); ?>"
                        type="hidden">

                    <!-- Quantity -->
                    <div class="inputContainer">
                        <label>Quantity:</label>
                        <input id="quantity" class="inputEmail" type="number" placeholder="Quantity" min="1">
                    </div>

                    <div class="inputContainer">
                        <label for="student">Assign Student</label>
                        <input id="student" class="inputEmail" placeholder="Enter Student Name (Optional)" type="text">
                    </div>
                    <!-- Return Date -->
                    <div class="inputContainer">
                        <label>Return Date:</label>
                        <input id="returnDate" class="inputEmail" type="date">
                    </div>

                    <!-- Buttons -->
                    <div class="inputContainer"
                        style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button class="addButton1">Cancel</button>
                        <button class="confirmButton">Borrow</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Borrow Multiple Items Modal -->
        <div class="addContainerMultiple">
            <div class="subAddContainerMultiple">
                <div style="height: 40px; margin-bottom: 10px" class="titleContainer">
                    <i class="fas fa-hand-holding"></i>
                    <p>Borrow Multiple Items</p>
                </div>

                <div class="subLoginContainerMultiple">
                    <!-- Default Item Row -->
                    <div class="itemRow" data-row="1">
                        <div class="inputContainer">
                            <label for="origin_item_1">Item</label>
                            <select name="origin_item_1" id="origin_item_1" class="inputEmail"
                                onchange="fetchMultipleItemDetails(this.value, 1)">
                                <option value="">Select an item</option>
                                <?php while ($originMultiple = $originMultipleResult->fetch_assoc()): ?>
                                    <option value="<?= $originMultiple['id'] ?>">
                                        <?= htmlspecialchars($originMultiple['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="inputContainer">
                            <label for="item_brand_1">Brand</label>
                            <select name="item_brand_1" id="item_brand_1" class="inputEmail">
                                <option value="">Select a brand</option>
                            </select>
                        </div>

                        <div class="inputContainer">
                            <label for="item_quantity_1">Available</label>
                            <input id="item_quantity_1" class="inputEmail" placeholder="Available" type="text" readonly>
                        </div>

                        <div class="inputContainer">
                            <label for="quantity_1">Quantity</label>
                            <input id="quantity_1" class="inputEmail" placeholder="Quantity" type="number" min="1">
                        </div>

                        <input id="teacher_1" class="inputEmail" value="<?php echo htmlspecialchars($userid); ?>"
                            type="hidden">


                        <div class="inputContainer">
                            <label for="student_1">Assign Student</label>
                            <input id="student_1" class="inputEmail" placeholder="Enter Student Name (Optional)"
                                type="text">
                        </div>

                        <div class="inputContainer" style="position: relative; display: flex; flex-direction: column;">
                            <label for="returnDate_1">Return Date</label>
                            <div style="display: flex; align-items: center;">
                                <input id="returnDate_1" class="inputEmail" placeholder="Enter Return Date" type="date"
                                    style="flex: 1;">
                                <button class="addMoreButton" onclick="addMoreItems()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Item Rows Will Be Added Here -->
                    <div id="additionalItemRows"></div>
                </div>

                <!-- Action Buttons -->
                <div class="inputContainer"
                    style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button class="addButton1" style="width: 100px;"
                        onclick="closeMultipleBorrowModal()">Cancel</button>
                    <button class="addButton" style="width: 100px;" onclick="confirmMultipleBorrow()">Borrow</button>
                </div>
            </div>
        </div>

        <script>
            // Function to open the Borrow Multiple Items modal
            function addMultiple() {
                const addContainerMultiple = document.querySelector('.addContainerMultiple');
                addContainerMultiple.style.display = 'flex';
            }

            // Function to close the Borrow Multiple Items modal
            function closeMultipleBorrowModal() {
                const addContainerMultiple = document.querySelector('.addContainerMultiple');
                addContainerMultiple.style.display = 'none';
            }

            function fetchMultipleItemDetails(itemId, rowIndex) {
                if (!itemId) {
                    document.getElementById(`item_brand_${rowIndex}`).innerHTML = '<option value="">Select a brand</option>';
                    document.getElementById(`item_quantity_${rowIndex}`).value = '';
                    return;
                }

                const xhr = new XMLHttpRequest();
                xhr.open('GET', `../inventoryAdmin/endpoints/fetch_multipleitem_details.php?item_id=${itemId}`, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                const brands = response.data;
                                const brandSelect = document.getElementById(`item_brand_${rowIndex}`);
                                brandSelect.innerHTML = '<option value="">Select a brand</option>';

                                brands.forEach(brand => {
                                    const option = document.createElement('option');
                                    option.value = brand.id;
                                    option.textContent = `${brand.brand} (Available: ${brand.quantity})`;
                                    option.setAttribute('data-quantity', brand.quantity);
                                    brandSelect.appendChild(option);
                                });

                                // Ensure only this row's quantity updates
                                brandSelect.onchange = function () {
                                    const selectedBrand = brandSelect.options[brandSelect.selectedIndex];
                                    document.getElementById(`item_quantity_${rowIndex}`).value = selectedBrand.dataset.quantity || '';
                                };

                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: response.message || 'Failed to fetch item details',
                                    confirmButtonColor: '#6B0D0D'
                                });
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                    }
                };
                xhr.send();
            }

            let rowCounter = 1; // Initialize row counter

            function addMoreItems() {
                rowCounter++; // Increment row count
                let newRow = document.createElement("div");
                newRow.classList.add("itemRow");
                newRow.setAttribute("data-row", rowCounter);
                newRow.innerHTML = `
        <div class="inputContainer">
            <label for="origin_item_${rowCounter}">Item</label>
            <select name="origin_item_${rowCounter}" id="origin_item_${rowCounter}" class="inputEmail"
                onchange="fetchMultipleItemDetails(this.value, ${rowCounter})">
                ${document.getElementById("origin_item_1").innerHTML} 
            </select>
        </div>

        <div class="inputContainer">
            <label for="item_brand_${rowCounter}">Brand</label>
            <select name="item_brand_${rowCounter}" id="item_brand_${rowCounter}" class="inputEmail">
                <option value="">Select a brand</option>
            </select>
        </div>

        <div class="inputContainer">
            <label for="item_quantity_${rowCounter}">Available</label>
            <input id="item_quantity_${rowCounter}" class="inputEmail" placeholder="Available" type="text" readonly>
        </div>

        <div class="inputContainer">
            <label for="quantity_${rowCounter}">Quantity</label>
            <input id="quantity_${rowCounter}" class="inputEmail" placeholder="Quantity" type="number" min="1">
        </div>

        <!-- Hidden input for teacher ID -->
        <input id="teacher_${rowCounter}" class="inputEmail" value="${document.getElementById("teacher_1").value}" type="hidden">

        <div class="inputContainer">
            <label for="student_${rowCounter}">Assign Student</label>
            <input id="student_${rowCounter}" class="inputEmail" placeholder="Enter Student Name (Optional)" type="text">
        </div>

        <div class="inputContainer" style="position: relative; display: flex; flex-direction: column;">
            <label for="returnDate_${rowCounter}">Return Date</label>
            <div style="display: flex; align-items: center;">
                <input id="returnDate_${rowCounter}" class="inputEmail" placeholder="Enter Return Date" type="date"
                    style="flex: 1;">
                <button class="addMoreButton" onclick="addMoreItems()">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    `;

                document.getElementById("additionalItemRows").appendChild(newRow);
            }
            function confirmMultipleBorrow() {
                let items = [];
                let allValid = true;

                document.querySelectorAll('.itemRow').forEach((row) => {
                    const rowIndex = row.getAttribute('data-row');
                    const itemId = document.getElementById(`origin_item_${rowIndex}`).value;
                    const teacherId = document.getElementById(`teacher_${rowIndex}`).value;
                    const brandSelect = document.getElementById(`item_brand_${rowIndex}`);
                    const brandId = brandSelect.options[brandSelect.selectedIndex].value;
                    const quantity = parseInt(document.getElementById(`quantity_${rowIndex}`).value, 10);
                    const student = document.getElementById(`student_${rowIndex}`).value;
                    const returnDate = document.getElementById(`returnDate_${rowIndex}`).value;
                    const availableQuantity = parseInt(document.getElementById(`item_quantity_${rowIndex}`).value, 10);

                    if (!itemId || !teacherId || isNaN(quantity) || !returnDate || !brandId) {
                        allValid = false;
                        return;
                    }

                    if (quantity > availableQuantity) {
                        allValid = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Quantity Exceeded!',
                            text: `The quantity you are trying to borrow (${quantity}) exceeds the available stock (${availableQuantity}). Please adjust the quantity.`,
                            confirmButtonColor: '#6B0D0D',
                        });
                        return;
                    }

                    if (quantity <= 0) {
                        allValid = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Quantity',
                            text: `Quantity must be greater than zero.`,
                            confirmButtonColor: '#6B0D0D',
                        });
                        return;
                    }

                    items.push({ item_id: itemId, brand_id: brandId, teacher: teacherId, quantity, student, return_date: returnDate });
                });

                if (!allValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing or Invalid Fields',
                        text: 'Please check all fields before proceeding.',
                        confirmButtonColor: '#6B0D0D',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirm Borrow',
                    text: 'Are you sure you want to proceed with these transactions?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#6B0D0D',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, confirm!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', '../inventoryAdmin/endpoints/borrow_multiple_items.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/json');
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4) {
                                if (xhr.status === 200) {
                                    try {
                                        const response = JSON.parse(xhr.responseText);

                                        if (response.status === 'success') {
                                            closeMultipleBorrowModal(); // Close modal on success

                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Borrowing Confirmed!',
                                                text: response.message,
                                                showConfirmButton: false,
                                                timer: 3000
                                            }).then(() => {
                                                location.reload();
                                            });

                                            // Reset form
                                            document.querySelectorAll('.itemRow').forEach(row => row.remove());
                                            addMoreItems(); // Re-add the first row
                                            setTimeout(fetchTransactions, 1000);
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: response.message,
                                                confirmButtonColor: '#6B0D0D',
                                            });
                                        }
                                    } catch (e) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.message,
                                            showConfirmButton: false,
                                            timer: 3000,
                                            confirmButtonColor: '#6B0D0D'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to communicate with the server.',
                                        confirmButtonColor: '#6B0D0D',
                                    });
                                }
                            }
                        };

                        xhr.send(JSON.stringify({ items }));
                    }
                });
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Elements
            const toggleBtn = document.querySelector('.toggle-btn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

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
                }
            }

            // Run on page load
            window.addEventListener('load', checkMobile);
            window.addEventListener('resize', checkMobile);

            // Toggle sidebar on mobile
            toggleBtn.addEventListener('click', function () {
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
            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('active');
                sidebar.style.left = '-280px';
                sidebarOverlay.classList.remove('active');
            });

            function setMinDate() {
                const manilaTime = new Date().toLocaleString("en-US", {
                    timeZone: "Asia/Manila"
                });
                const today = new Date(manilaTime);

                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, "0");
                const dd = String(today.getDate()).padStart(2, "0");
                const minDate = `${yyyy}-${mm}-${dd}`;

                const dateInputs = document.querySelectorAll('input[type="date"]');
                dateInputs.forEach(input => {
                    input.min = minDate;
                });
            }

            setMinDate();

            function showNote(note) {
                Swal.fire({
                    title: '<i class="fas fa-clipboard-list" style="color: #6B0D0D; margin-right: 10px;"></i>Item Note',
                    html: `<div style="text-align: left; padding: 10px; background-color: #f9f9f9; border-radius: 8px; margin-top: 15px;">${note}</div>`,
                    icon: false,
                    confirmButtonText: '<i class="fas fa-check"></i> Got it',
                    confirmButtonColor: '#6B0D0D',
                    customClass: {
                        title: 'swal-title-custom',
                        htmlContainer: 'swal-html-custom',
                        popup: 'swal-popup-custom'
                    }
                });
            }

            function borrowItem(itemId, itemName) {
                fetch('checkBanStatus.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ userId: <?= json_encode($_SESSION['user_id']) ?> })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.banned) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Access Denied',
                                text: 'You are currently banned from borrowing items.',
                                confirmButtonText: 'Okay',
                                confirmButtonColor: '#6B0D0D'
                            });
                        } else {
                            document.getElementById('itemId').value = itemId;
                            document.getElementById('itemName').value = itemName;
                            document.getElementById('available_quantity').value = ''; // Reset available quantity

                            // Fetch brands for the selected item
                            fetch(`fetch_brands.php?item_id=${itemId}`)
                                .then(response => response.json())
                                .then(brands => {
                                    const brandSelect = document.getElementById('itemBrand');
                                    brandSelect.innerHTML = '';

                                    // Default "Select a Brand" option
                                    let defaultOption = document.createElement('option');
                                    defaultOption.value = '';
                                    defaultOption.textContent = 'Select a Brand';
                                    defaultOption.disabled = true;
                                    defaultOption.selected = true;
                                    brandSelect.appendChild(defaultOption);

                                    if (brands.length > 0) {
                                        brands.forEach(brand => {
                                            let option = document.createElement('option');
                                            option.value = brand.id;
                                            option.dataset.quantity = brand.quantity; // Store quantity in data attribute
                                            option.textContent = `${brand.name} (Available: ${brand.quantity})`;
                                            brandSelect.appendChild(option);
                                        });

                                        // Auto-update Available Quantity when brand is selected
                                        brandSelect.addEventListener('change', function () {
                                            let selectedOption = brandSelect.options[brandSelect.selectedIndex];
                                            document.getElementById('available_quantity').value = selectedOption.dataset.quantity;
                                        });

                                    } else {
                                        let option = document.createElement('option');
                                        option.textContent = 'No available brands';
                                        option.disabled = true;
                                        brandSelect.appendChild(option);
                                    }
                                })
                                .catch(error => console.error('Error fetching brands:', error));

                            document.querySelector('.editContainer').style.display = 'flex';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while checking your ban status.',
                            confirmButtonText: 'Okay',
                            confirmButtonColor: '#6B0D0D'
                        });
                    });
            }

            document.querySelector('.confirmButton').addEventListener('click', function () {
                // Get form values
                const itemId = document.getElementById('itemId').value;
                const brandId = document.getElementById('itemBrand').value; // Get selected brand ID
                const teacherId = document.getElementById('teacherSelect').value;
                const quantity = document.getElementById('quantity').value;
                const returnDate = document.getElementById('returnDate').value;
                const student = document.getElementById('student').value;

                if (!teacherId || !quantity || !returnDate || !brandId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Please fill in all required fields.',
                        confirmButtonColor: '#6B0D0D'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirm Borrow',
                    text: 'Are you sure you want to borrow this item?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6B0D0D',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Borrow'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the form using fetch
                        const formData = new FormData();
                        formData.append('item_id', itemId);
                        formData.append('brand_id', brandId); // Include brand ID
                        formData.append('teacher', teacherId);
                        formData.append('quantity', quantity);
                        formData.append('return_date', returnDate);
                        formData.append('student', student); // Add the student name

                        fetch('borrow_item.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: data.message,
                                        showConfirmButton: false,
                                        timer: 3000,
                                        confirmButtonColor: '#6B0D0D'
                                    });
                                    closeModal();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message,
                                        confirmButtonColor: '#6B0D0D'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while processing your request.',
                                    confirmButtonColor: '#6B0D0D'
                                });
                            });
                    }
                });
            });
            function closeModal() {
                const modal = document.querySelector('.editContainer');
                modal.style.display = 'none';
            }

            document.querySelector('.addButton1').addEventListener('click', closeModal);



            const searchBar = document.getElementById('searchBar');
            const inventoryGrid = document.getElementById('inventoryGrid');

            searchBar.addEventListener('input', function () {
                const searchTerm = searchBar.value.toLowerCase();
                const inventoryContainers = inventoryGrid.getElementsByClassName('inventoryContainer');

                for (const container of inventoryContainers) {
                    const title = container.getAttribute('data-title').toLowerCase();
                    container.style.display = title.includes(searchTerm) ? '' : 'none';
                }
            });
        </script>
</body>

</html>