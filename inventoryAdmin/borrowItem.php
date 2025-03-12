<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $inventoryAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $image = $row['image'];
} else {
    $fullName = "User Not Found";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Items</title>

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

        /* Search bar and filters */
        .searchContainer {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .searchBar {
            flex: 1;
            min-width: 200px;
            padding: 12px 20px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .searchBar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 2px 12px rgba(107, 13, 13, 0.1);
        }

        .searchContainer::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 18px;
            top: 13px;
            color: var(--primary);
            font-size: 16px;
            z-index: 1;
        }

        .printButton {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .addButton,
        .addButton1 {
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .addButton {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 5px rgba(107, 13, 13, 0.2);
        }

        .addButton:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 10px rgba(107, 13, 13, 0.3);
            transform: translateY(-2px);
        }

        .addButton1 {
            background-color: var(--secondary);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .addButton1:hover {
            background-color: var(--secondary-dark);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .size {
            min-width: 120px;
        }

        /* Table styles */
        .tableContainer {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--primary);
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tr:hover {
            background-color: rgba(249, 249, 249, 0.8);
        }

        td.button {
            white-space: nowrap;
            display: flex;
            gap: 5px;
        }

        /* Tooltip styles */
        .hover-unique-id {
            position: relative;
            cursor: help;
            text-decoration: underline;
            text-decoration-style: dotted;
            text-decoration-color: var(--secondary);
        }

        .hover-unique-id .tooltip {
            visibility: hidden;
            position: absolute;
            top: 125%;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--white);
            color: var(--text);
            border: 1px solid #ddd;
            padding: 10px;
            white-space: pre-wrap;
            z-index: 10;
            font-family: Arial, sans-serif;
            font-size: 14px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            min-width: 150px;
            max-width: 300px;
            transition: visibility 0.1s, opacity 0.3s;
            opacity: 0;
        }

        .hover-unique-id .tooltip::after {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 50%;
            margin-left: -8px;
            border-width: 8px;
            border-style: solid;
            border-color: transparent transparent #ddd transparent;
        }

        .hover-unique-id:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }

        /* Modal styling */
        .addContainer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .subAddContainer {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            animation: slideUp 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .titleContainer i {
            font-size: 20px;
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
            color: var(--text);
            font-weight: 500;
            font-size: 15px;
        }

        .inputEmail {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
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
            cursor: not-allowed;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
            }

            th,
            td {
                padding: 10px 12px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .searchContainer {
                flex-direction: column;
                align-items: flex-start;
            }

            .searchBar {
                max-width: 100%;
                width: 100%;
            }

            .printButton {
                width: 100%;
                justify-content: flex-end;
            }

            th,
            td {
                padding: 8px 10px;
                font-size: 14px;
            }

            .page-title {
                font-size: 1.5rem;
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
                display: flex !important;
            }

            .header {
                padding-left: 60px;
            }

            .page-title span {
                display: none;
            }

            /* Make header text smaller on mobile */
            h1 {
                font-size: 1rem !important;
            }

            h2 {
                font-size: 1.3rem !important;
            }

            .subAddContainer {
                transform: scale(1) !important;
                width: 95%;
            }

            .printButton {
                justify-content: space-between;
            }
        }

        @media (max-width: 576px) {
            .searchBar {
                padding: 10px 15px 10px 40px;
                font-size: 14px;
            }

            .addButton,
            .addButton1 {
                padding: 8px 12px;
                font-size: 13px;
            }

            .titleContainer {
                padding: 15px;
                font-size: 18px;
            }

            .subLoginContainer {
                padding: 15px;
            }

            .inputEmail {
                padding: 10px;
                font-size: 14px;
            }

            .inputContainer label {
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
            body.dark-mode-supported .inputEmail,
            body.dark-mode-supported select.addButton {
                background-color: #222;
                border-color: #444;
                color: #eee;
            }

            body.dark-mode-supported th {
                background-color: #333;
            }

            body.dark-mode-supported tr:hover {
                background-color: #272727;
            }

            body.dark-mode-supported .hover-unique-id .tooltip {
                background-color: #333;
                border-color: #555;
            }

            body.dark-mode-supported .hover-unique-id .tooltip::after {
                border-color: transparent transparent #555 transparent;
            }
        }

        /* Print styles */
        @media print {

            .sidebar,
            .searchContainer,
            .toggle-btn,
            .btnAction {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
            }

            body {
                background-color: white;
                font-size: 12pt;
            }

            .tableContainer {
                box-shadow: none;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #ddd;
            }

            th {
                background-color: #f9f9f9 !important;
                color: black !important;
            }
        }
    </style>
</head>

<body>
    <!-- Toggle Sidebar Button -->
    <button class="toggle-btn">
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
            <a href="../homePage/" class="sidebar-link">
                <i class="fas fa-home"></i> Home
            </a>

            <a href="../inventoryAdmin/index.php" class="sidebar-link">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>

            <a href="../inventoryAdmin/inventory.php" class="sidebar-link">
                <i class="fas fa-boxes"></i> Inventories
            </a>

            <a href="../inventoryAdmin/borrowing.php" class="sidebar-link">
                <i class="fas fa-clipboard-list"></i> Borrow Request
            </a>

            <a href="../inventoryAdmin/borrowItem.php" class="sidebar-link active">
                <i class="fas fa-hand-holding"></i> Borrowed Item
            </a>

            <a href="../inventoryAdmin/notification.php" class="sidebar-link">
                <i class="fas fa-bell"></i> Notification
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
            <img src="../assets/img/CSSPE.png" alt="Logo">
            <h1>CSSPE Inventory & Information System</h1>
        </div>

        <!-- Content -->
        <div class="page-content">
            <div class="content-container">
                <h2 class="page-title">
                    <i class="fas fa-hand-holding"></i>
                    Borrowed Items
                    <span>Manage items currently borrowed</span>
                </h2>

                <!-- Search & Action Buttons -->
                <div style="position: relative;" class="searchContainer">
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search..." oninput="filterTable()">

                    <div class="printButton">
                        <button onclick="printTable()" class="addButton size">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <select name="" class="addButton size" id="filterDropdown">
                            <option value="">Filter</option>
                            <option value="all">All</option>
                            <option value="day">This day</option>
                            <option value="week">This week</option>
                            <option value="month">This month</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Borrow Date</th>
                                <th>Return Date</th>
                                <th>Fullname</th>
                                <th>Assigned Student</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows will be dynamically inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Item Modal -->
    <div class="addContainer">
        <div class="subAddContainer">
            <div class="titleContainer">
                <i class="fas fa-undo-alt"></i>
                <p>Return Item</p>
            </div>

            <div class="subLoginContainer">
                <div class="inputContainer">
                    <label for="itemName">Item Name</label>
                    <input id="itemName" class="inputEmail" type="text" readonly>
                </div>

                <div class="inputContainer">
                    <label for="itemBrand">Brand</label>
                    <input id="itemBrand" class="inputEmail" type="text" readonly>
                </div>

                <div class="inputContainer">
                    <label for="quantityBorrowed">Borrowed Quantity</label>
                    <input id="quantityBorrowed" class="inputEmail" type="number" readonly>
                </div>

                <div class="inputContainer">
                    <label for="returnQuantity">Return Quantity</label>
                    <input id="returnQuantity" class="inputEmail" type="number">
                </div>

                <div class="inputContainer">
                    <label for="damaged">Damaged Quantity</label>
                    <input id="damaged" class="inputEmail" type="number" value="0">
                </div>

                <div class="inputContainer">
                    <label for="lost">Lost Quantity</label>
                    <input id="lost" class="inputEmail" type="number" value="0">
                </div>

                <div class="inputContainer">
                    <label for="replaced">Replaced Quantity</label>
                    <input id="replaced" class="inputEmail" type="number" value="0">
                </div>

                <div class="inputContainer"
                    style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button onclick="closeReturnModal()" class="addButton1" style="width: 100px;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="addButton" style="width: 100px;" onclick="confirmReturn()">
                        <i class="fas fa-check"></i> Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 JavaScript -->
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
                toggleBtn.style.display = 'flex';
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

        // Fetch transactions
        function fetchTransactions() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', './endpoints/get_item_transactions_approved.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const transactions = response.data;
                            const tbody = document.querySelector('.tableContainer tbody');
                            tbody.innerHTML = '';

                            function formatTime24To12(timeString) {
                                if (!timeString) return 'N/A';
                                const [hour, minute] = timeString.split(':');
                                const hourInt = parseInt(hour, 10);
                                const isPM = hourInt >= 12;
                                const formattedHour = hourInt % 12 || 12;
                                const suffix = isPM ? 'PM' : 'AM';
                                return `${formattedHour}:${minute} ${suffix}`;
                            }

                            function formatDateTimeWithNewline(datetimeString) {
                                if (!datetimeString) return 'N/A';
                                const [date, time] = datetimeString.split(' ');
                                const formattedTime = formatTime24To12(time);
                                return `${date}<br>${formattedTime}`;
                            }

                            transactions.forEach(transaction => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${transaction.transaction_id}</td>
                                    <td>${transaction.item_id}</td>
                                    <td>${transaction.item_name}</td>
                                    <td>${transaction.item_brand}</td>
                                    <td>
                                       <span class="hover-unique-id">
                                       ${transaction.quantity_borrowed}
                                       <div class="tooltip">${transaction.unique_ids}</div>
                                       </span>
                                    </td>
                                    <td>${transaction.borrowed_at ? formatDateTimeWithNewline(transaction.borrowed_at) : 'N/A'}</td>
                                    <td>${transaction.return_date}</td>
                                    <td>${transaction.first_name} ${transaction.last_name}</td>
                                    <td>${transaction.assigned_student}</td>
                                    <td>${transaction.contact_no}</td>
                                    <td>${transaction.email}</td>
                                    <td>
                                        ${transaction.status_remark}
                                        <button class="addButton" style="height: 2rem; padding: 0 8px; margin-left: 5px;" onclick="editStatusRemark(${transaction.transaction_id}, '${transaction.status_remark}')">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                    </td>
                                    <td class="button">
                                        <button class="addButton" style="width: 7rem;" onclick="openReturnModal(${transaction.transaction_id})">
                                            <i class="fas fa-undo-alt"></i> Return
                                        </button>
                                    </td>
                                `;
                                tbody.appendChild(row);
                            });
                        } else {
                            // Show empty state if no data
                            const tbody = document.querySelector('.tableContainer tbody');
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="12" style="text-align: center; padding: 40px 20px;">
                                        <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 15px; display: block;"></i>
                                        <p style="font-size: 16px; color: #888; margin: 0;">No borrowed items found</p>
                                        <p style="font-size: 14px; color: #aaa; margin-top: 5px;">Items that have been borrowed will appear here</p>
                                    </td>
                                </tr>
                            `;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        // Swal.fire({
                        //     icon: 'error',
                        //     title: 'An error occurred',
                        //     text: 'Could not load transaction data',
                        //     confirmButtonColor: '#6B0D0D'
                        // });
                    }
                }
            };
            xhr.send();
        }

        function openReturnModal(transactionId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./endpoints/return_transaction_details.php?transaction_id=${transactionId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const transaction = response.data;

                            // Populate modal fields
                            document.getElementById('itemName').value = transaction.item_name || '';
                            document.getElementById('itemBrand').value = transaction.item_brand || '';
                            document.getElementById('quantityBorrowed').value = transaction.quantity_borrowed || '';
                            document.getElementById('returnQuantity').value = transaction.quantity_borrowed || '';
                            document.getElementById('damaged').value = 0;
                            document.getElementById('lost').value = 0;
                            document.getElementById('replaced').value = 0;

                            // Set the transaction ID as a data attribute
                            const modal = document.querySelector('.addContainer');
                            modal.setAttribute('data-transaction-id', transactionId);

                            // Show the modal
                            modal.style.display = 'flex';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Could not load transaction details',
                                confirmButtonColor: '#6B0D0D'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred',
                            confirmButtonColor: '#6B0D0D'
                        });
                    }
                }
            };
            xhr.send();
        }

        function confirmReturn() {
            const itemName = document.getElementById('itemName').value.trim();
            const itemBrand = document.getElementById('itemBrand').value.trim();
            const quantityBorrowed = document.getElementById('quantityBorrowed').value.trim();
            const returnQuantity = document.getElementById('returnQuantity').value.trim();
            const damaged = parseInt(document.getElementById('damaged').value.trim(), 10);
            const lost = parseInt(document.getElementById('lost').value.trim(), 10);
            const replaced = parseInt(document.getElementById('replaced').value.trim(), 10);

            // Validate input
            if (!returnQuantity || returnQuantity <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Return Quantity',
                    text: 'Please enter a valid return quantity.',
                    confirmButtonColor: '#6B0D0D',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Check if the sum of damaged, lost, replaced exceeds the return quantity
            const totalSpecial = damaged + lost + replaced;
            if (totalSpecial > returnQuantity) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Quantities',
                    text: 'The sum of damaged, lost, and replaced items cannot exceed the return quantity.',
                    confirmButtonColor: '#6B0D0D',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const transactionId = document.querySelector('.addContainer').getAttribute('data-transaction-id');

            // Track selected items globally to exclude from subsequent modals
            let selectedIds = new Set();

            function showUniqueIdAlert(type, quantity, uniqueIds) {
                return new Promise((resolve) => {
                    // Filter out already selected IDs
                    const availableIds = uniqueIds.filter((idPair) => {
                        const [id] = idPair.split(':');
                        return !selectedIds.has(id);
                    });

                    // Generate checkboxes for remaining IDs
                    const checkboxes = availableIds.map((idPair) => {
                        const [id, uniqueId] = idPair.split(':');
                        return `<div style="margin: 8px 0; text-align: left;">
                            <input type="checkbox" value="${id}" id="checkbox-${id}" onclick="limitSelections('${type}', ${quantity})" style="margin-right: 8px;">
                            <label for="checkbox-${id}" style="font-weight: normal;">${uniqueId}</label>
                        </div>`;
                    }).join('');

                    if (availableIds.length === 0) {
                        Swal.fire({
                            title: `No ${type} Items Available`,
                            text: `All items have already been selected.`,
                            icon: 'info',
                            confirmButtonColor: '#6B0D0D',
                            confirmButtonText: 'OK'
                        });
                        resolve([]);
                        return;
                    }

                    Swal.fire({
                        title: `Select ${type} Items`,
                        html: `
                            <p style="margin-bottom: 15px; font-weight: bold;">${type} Quantity: ${quantity}</p>
                            <div style="max-height: 300px; overflow-y: auto; padding: 10px; border: 1px solid #eee; border-radius: 8px;">
                                ${checkboxes}
                            </div>
                            <p style="margin-top: 15px; font-size: 13px; color: #666;">Please select exactly ${quantity} items.</p>
                        `,
                        showCancelButton: true,
                        confirmButtonColor: '#6B0D0D',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Confirm',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            const selected = [];
                            availableIds.forEach((idPair) => {
                                const [id] = idPair.split(':');
                                const checkbox = document.getElementById(`checkbox-${id}`);
                                if (checkbox && checkbox.checked) {
                                    selected.push(id);
                                }
                            });

                            // Validate correct number of items selected
                            if (selected.length !== quantity) {
                                Swal.showValidationMessage(`Please select exactly ${quantity} items`);
                                return false;
                            }

                            return selected;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Add newly selected IDs to the global set
                            result.value.forEach((id) => selectedIds.add(id));
                            resolve(result.value);
                        } else {
                            resolve([]);
                        }
                    });
                });
            }

            // Limit selections in modals based on quantity
            window.limitSelections = function (type, maxSelections) {
                const checkboxes = document.querySelectorAll(`input[type="checkbox"]`);
                const selectedCount = Array.from(checkboxes).filter((cb) => cb.checked).length;

                // Disable unchecked checkboxes if the limit is reached
                checkboxes.forEach((checkbox) => {
                    if (!checkbox.checked) {
                        checkbox.disabled = selectedCount >= maxSelections;
                    }
                });
            };

            // Fetch unique IDs and display SweetAlerts if required
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./endpoints/return_transaction_details.php?transaction_id=${transactionId}`, true);
            xhr.onreadystatechange = async function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const transaction = response.data;
                            const uniqueIds = transaction.unique_ids ? transaction.unique_ids.split(',') : [];

                            let damagedIds = [],
                                lostIds = [],
                                replacedIds = [];

                            // Only show ID selection dialogs if we have unique IDs and quantities > 0
                            if (uniqueIds.length > 0) {
                                if (damaged > 0) {
                                    damagedIds = await showUniqueIdAlert('Damaged', damaged, uniqueIds);
                                    if (damagedIds.length === 0 && damaged > 0) {
                                        return; // User cancelled
                                    }
                                }

                                if (lost > 0) {
                                    lostIds = await showUniqueIdAlert('Lost', lost, uniqueIds);
                                    if (lostIds.length === 0 && lost > 0) {
                                        return; // User cancelled
                                    }
                                }

                                if (replaced > 0) {
                                    replacedIds = await showUniqueIdAlert('Replaced', replaced, uniqueIds);
                                    if (replacedIds.length === 0 && replaced > 0) {
                                        return; // User cancelled
                                    }
                                }
                            }

                            // Final confirmation
                            Swal.fire({
                                title: 'Confirm Return',
                                text: 'Do you want to confirm the return of these items?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#6B0D0D',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Yes, confirm it!',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    processReturn(transactionId, returnQuantity, damaged, lost, replaced, damagedIds, lostIds, replacedIds);
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to fetch transaction details',
                                confirmButtonColor: '#6B0D0D'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred',
                            confirmButtonColor: '#6B0D0D'
                        });
                    }
                }
            };
            xhr.send();
        }

        function processReturn(transactionId, returnQuantity, damaged, lost, replaced, damagedIds, lostIds, replacedIds) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', './endpoints/return_items.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Items have been returned successfully',
                                confirmButtonColor: '#6B0D0D',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            closeReturnModal();
                            fetchTransactions();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to process return',
                                confirmButtonColor: '#6B0D0D'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred while processing the return',
                            confirmButtonColor: '#6B0D0D'
                        });
                    }
                }
            };

            const requestData = {
                transaction_id: transactionId,
                return_quantity: returnQuantity,
                damaged: damaged,
                lost: lost,
                replaced: replaced,
                damaged_ids: damagedIds,
                lost_ids: lostIds,
                replaced_ids: replacedIds
            };

            xhr.send(JSON.stringify(requestData));
        }

        function editStatusRemark(transactionId, currentRemark) {
            Swal.fire({
                title: 'Edit Status Remark',
                input: 'text',
                inputValue: currentRemark,
                inputPlaceholder: 'Enter status remark',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                preConfirm: (newRemark) => {
                    return new Promise((resolve) => {
                        // Send the update request
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', './endpoints/update_status_remark.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/json');
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4) {
                                if (xhr.status === 200) {
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        if (response.status === 'success') {
                                            resolve(response.message);
                                        } else {
                                            Swal.showValidationMessage(response.message || 'Failed to update remark');
                                        }
                                    } catch (e) {
                                        Swal.showValidationMessage('Invalid response from server');
                                    }
                                } else {
                                    Swal.showValidationMessage('An error occurred. Please try again.');
                                }
                            }
                        };
                        xhr.send(JSON.stringify({
                            transaction_id: transactionId,
                            status_remark: newRemark
                        }));
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Status remark has been updated successfully',
                        confirmButtonColor: '#6B0D0D',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(() => {
                        fetchTransactions(); // Refresh the table
                    });
                }
            });
        }

        function closeReturnModal() {
            document.querySelector('.addContainer').style.display = 'none';
            document.querySelector('.addContainer').removeAttribute('data-transaction-id');
        }

        // Search function
        function filterTable() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const tableRows = document.querySelectorAll('.tableContainer tbody tr');

            tableRows.forEach(row => {
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let i = 0; i < cells.length; i++) {
                    const cellText = cells[i].textContent.toLowerCase();
                    if (cellText.includes(searchValue)) {
                        found = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            });
        }

        // Filter dropdown function
        document.getElementById('filterDropdown').addEventListener('change', function () {
            const filterValue = this.value;
            const tableRows = document.querySelectorAll('.tableContainer tbody tr');
            const currentDate = new Date();

            tableRows.forEach(row => {
                const dateCell = row.getElementsByTagName('td')[5]; // Borrow Date column
                if (!dateCell) return;

                const dateText = dateCell.textContent.split('<br>')[0]; // Get just the date part
                if (!dateText || dateText === 'N/A') return;

                const rowDate = new Date(dateText);
                let show = true;

                if (filterValue === 'day') {
                    // Check if same day
                    show = rowDate.toDateString() === currentDate.toDateString();
                } else if (filterValue === 'week') {
                    // Check if within last 7 days
                    const oneWeekAgo = new Date();
                    oneWeekAgo.setDate(currentDate.getDate() - 7);
                    show = rowDate >= oneWeekAgo;
                } else if (filterValue === 'month') {
                    // Check if same month
                    show = rowDate.getMonth() === currentDate.getMonth() &&
                        rowDate.getFullYear() === currentDate.getFullYear();
                }

                row.style.display = (filterValue === '' || filterValue === 'all' || show) ? '' : 'none';
            });
        });

        // Print function
        function printTable() {
            const tableContainer = document.querySelector('.tableContainer');
            const rows = tableContainer.querySelectorAll('tr');

            // Hide action column before printing
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    const lastCell = cells[cells.length - 1];
                    lastCell.style.display = 'none';

                    // Also hide the edit button in the note column
                    if (cells.length > 10) {
                        const noteCell = cells[cells.length - 2];
                        const editButton = noteCell.querySelector('button');
                        if (editButton) editButton.style.display = 'none';
                    }
                }
            });

            // Generate print content
            const printContent = tableContainer.outerHTML;
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Borrowed Items Report</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 20px;
                            color: #333;
                        }
                        h1 {
                            color: #6B0D0D;
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                        }
                        th, td {
                            border: 1px solid black;
                            padding: 8px;
                            text-align: left;
                        }
                        th {
                            background-color: #f4f4f4;
                            font-weight: bold;
                        }
                        tr:nth-child(even) {
                            background-color: #f9f9f9;
                        }
                        .print-header {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin-bottom: 20px;
                        }
                        .print-header img {
                            width: 50px;
                            margin-right: 15px;
                        }
                        .date-printed {
                            text-align: right;
                            margin-bottom: 20px;
                            font-style: italic;
                            font-size: 12px;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <img src="../assets/img/CSSPE.png" alt="Logo">
                        <h1>CSSPE Inventory & Information System - Borrowed Items</h1>
                    </div>
                    <div class="date-printed">
                        Printed on: ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}
                    </div>
                    ${printContent}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();

            // Restore visibility
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = '';

                    // Restore edit button visibility
                    if (cells.length > 10) {
                        const noteCell = cells[cells.length - 2];
                        const editButton = noteCell.querySelector('button');
                        if (editButton) editButton.style.display = '';
                    }
                }
            });
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', fetchTransactions);
    </script>
</body>

</html>