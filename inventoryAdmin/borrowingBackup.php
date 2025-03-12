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
// Fetch school year data
$schoolYearQuery = "SELECT school_year, semester, start_date, end_date FROM school_years ORDER BY start_date DESC";
$schoolYearResult = $conn->query($schoolYearQuery);

$schoolYearOptions = '';
if ($schoolYearResult->num_rows > 0) {
    while ($row = $schoolYearResult->fetch_assoc()) {
        $formattedOption = $row['school_year'] . ' ' . $row['semester'];
        $valueOption = $row['start_date'] . ' to ' . $row['end_date'];
        $schoolYearOptions .= "<option value=\"{$valueOption}\">{$formattedOption}</option>";
    }
}

// Fetch items 
$originQuery = "SELECT id, name, brand FROM items";
$originResult = $conn->query($originQuery);

// Fetch users with role 'Instructor'
$teacherQuery = "SELECT id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name FROM users WHERE role = 'Instructor' AND ban = 0";
$teacherResult = $conn->query($teacherQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Request</title>

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

        /* Search bar */
        .searchContainer {
            margin: 0 0 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }

        .searchBar {
            flex: 1;
            padding: 12px 20px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            max-width: 600px;
            position: relative;
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
        }

        .addButton1 {
            background-color: var(--secondary);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .addButton1:hover {
            background-color: var(--secondary-dark);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
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
        }

        tr:hover {
            background-color: rgba(249, 249, 249, 0.8);
        }

        /* Tooltip styles */
        .hover-unique-id {
            position: relative;
            cursor: pointer;
        }

        .hover-unique-id .tooltip {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: var(--white);
            color: var(--text);
            border: 1px solid #ddd;
            padding: 10px;
            white-space: pre-wrap;
            z-index: 10;
            font-family: Arial, sans-serif;
            font-size: 14px;
            text-align: center;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            min-width: 120px;
            max-width: 250px;
        }

        .hover-unique-id:hover .tooltip {
            display: block;
        }

        /* Modal styling */
        .addContainer,
        .editContainer {
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
            max-width: 550px;
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

            .tableContainer {
                overflow-x: auto;
            }

            table {
                min-width: 900px;
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
        }

        @media (max-width: 576px) {
            .searchBar {
                padding: 10px 15px 10px 40px;
                font-size: 14px;
            }

            .printButton {
                justify-content: space-between;
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

            label {
                font-size: 14px !important;
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

            body.dark-mode-supported th {
                background-color: #333;
            }

            body.dark-mode-supported tr:hover {
                background-color: #272727;
            }

            body.dark-mode-supported .tooltip {
                background-color: #333;
                border-color: #555;
                color: #eee;
            }
        }

        /* Print styles */
        @media print {

            .sidebar,
            .searchContainer,
            .toggle-btn,
            .addButton,
            .addButton1 {
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

            <?php if ($_SESSION['user_role'] === 'super_admin'): ?>
                <a href="../superAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Super Admin Panel
                </a>
            <?php endif; ?>

            <a href="../inventoryAdmin/index.php" class="sidebar-link">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>

            <a href="../inventoryAdmin/inventory.php" class="sidebar-link">
                <i class="fas fa-boxes"></i> Inventories
            </a>

            <a href="../inventoryAdmin/borrowing.php" class="sidebar-link active">
                <i class="fas fa-clipboard-list"></i> Borrow Request
            </a>

            <a href="../inventoryAdmin/borrowItem.php" class="sidebar-link">
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
                    <i class="fas fa-clipboard-list"></i>
                    Borrow Requests
                    <span>Manage pending borrow requests</span>
                </h2>

                <!-- Search & Action Buttons -->
                <div style="position: relative;" class="searchContainer">
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search..." oninput="filterTable()">

                    <div class="printButton">
                        <button onclick="printTable()" class="addButton size">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button onclick="addProgram()" class="addButton size">
                            <i class="fas fa-plus"></i> Borrow Item
                        </button>
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
                                <th>Contact Number</th>
                                <th>Email</th>
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

    <!-- Add Borrow Item Modal -->
    <div class="addContainer">
        <div class="subAddContainer">
            <div class="titleContainer">
                <i class="fas fa-hand-holding"></i>
                <p>Borrow Item</p>
            </div>

            <div class="subLoginContainer">
                <div class="inputContainer">
                    <label for="origin_item">Choose an Item</label>
                    <select name="origin_item" id="origin_item" class="inputEmail"
                        onchange="fetchItemDetails(this.value)">
                        <option value="">Select an item</option>
                        <?php while ($origin = $originResult->fetch_assoc()): ?>
                            <option value="<?= $origin['id'] ?>">
                                <?= htmlspecialchars($origin['name'] . ' (' . $origin['brand'] . ')') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="inputContainer">
                    <label for="item_brand">Brand</label>
                    <input id="item_brand" class="inputEmail" placeholder="Brand" type="text" readonly>
                </div>

                <div class="inputContainer">
                    <label for="item_quantity">Available Quantity</label>
                    <input id="item_quantity" class="inputEmail" placeholder="Available" type="text" readonly>
                </div>

                <div class="inputContainer">
                    <label for="teacher">Select Faculty Member</label>
                    <select name="teacher" id="teacher" class="inputEmail">
                        <option value="">Select a faculty member</option>
                        <?php while ($teacher = $teacherResult->fetch_assoc()): ?>
                            <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars(trim($teacher['full_name'])) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="inputContainer">
                    <label for="student">Assign Student</label>
                    <input id="student" class="inputEmail" placeholder="Enter Student Name (Optional)" type="text">
                </div>

                <div class="inputContainer">
                    <label for="quantity">Quantity</label>
                    <input id="quantity" class="inputEmail" placeholder="Enter Quantity" type="number" min="1">
                </div>

                <div class="inputContainer">
                    <label for="returnDate">Return Date</label>
                    <input id="returnDate" class="inputEmail" type="date" placeholder="Return Date">
                </div>

                <div class="inputContainer"
                    style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button class="addButton1" style="width: 100px;" onclick="addProgram()">Cancel</button>
                    <button class="addButton" style="width: 100px;" onclick="confirmBorrow()">Borrow</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="editContainer">
        <div class="subAddContainer">
            <div class="titleContainer">
                <i class="fas fa-edit"></i>
                <p>Edit Borrowed Item</p>
            </div>

            <div class="subLoginContainer">
                <input id="edit_origin_item" class="inputEmail" placeholder="Item ID" type="hidden" readonly>

                <div class="inputContainer">
                    <label for="edit_origin_item_name">Item Name</label>
                    <input id="edit_origin_item_name" class="inputEmail" placeholder="Item Name" type="text" readonly>
                </div>

                <div class="inputContainer">
                    <label for="edit_item_brand">Brand</label>
                    <input id="edit_item_brand" class="inputEmail" placeholder="Brand" type="text" readonly>
                </div>

                <div class="inputContainer">
                    <label for="edit_item_quantity">Available Quantity</label>
                    <input id="edit_item_quantity" class="inputEmail" placeholder="Available Quantity" type="text"
                        readonly>
                </div>

                <div class="inputContainer">
                    <label for="edit_teacher">Faculty Member</label>
                    <select name="teacher" id="edit_teacher" class="inputEmail">
                        <option value="">Select a faculty member</option>
                        <!-- Dynamically populated via JavaScript -->
                    </select>
                </div>

                <div class="inputContainer">
                    <label for="edit_quantity">Quantity</label>
                    <input id="edit_quantity" class="inputEmail" type="number" placeholder="Quantity" min="1">
                </div>

                <div class="inputContainer">
                    <label for="edit_borrow_date">Borrow Date</label>
                    <input id="edit_borrow_date" class="inputEmail" type="date" placeholder="Borrow Date">
                </div>

                <div class="inputContainer">
                    <label for="edit_return_date">Return Date</label>
                    <input id="edit_return_date" class="inputEmail" type="date" placeholder="Return Date">
                </div>

                <div class="inputContainer">
                    <label for="edit_class_date">Class Date</label>
                    <input id="edit_class_date" class="inputEmail" type="date" placeholder="Class Date">
                </div>

                <div class="inputContainer">
                    <label for="edit_schedule_from">Class Time From</label>
                    <input id="edit_schedule_from" class="inputEmail" type="time" placeholder="From">
                </div>

                <div class="inputContainer">
                    <label for="edit_schedule_to">Class Time To</label>
                    <input id="edit_schedule_to" class="inputEmail" type="time" placeholder="To">
                </div>

                <div class="inputContainer"
                    style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button class="addButton1" style="width: 100px;" onclick="cancelEdit()">Cancel</button>
                    <button class="addButton" style="width: 100px;" onclick="saveEditTransaction()">Save</button>
                </div>
            </div>
        </div>
    </div>

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

        // Set minimum date on page load
        setMinDate();

        function resetPage() {
            window.location.href = 'borrowing.php';
        }

        function addProgram() {
            const addContainer = document.querySelector('.addContainer');

            if (addContainer.style.display === 'none' || addContainer.style.display === '') {
                addContainer.style.display = 'flex';
            } else {
                addContainer.style.display = 'none';
            }
        }

        function cancelEdit() {
            document.querySelector('.editContainer').style.display = 'none';
        }

        function approveTransaction(transactionId) {
            Swal.fire({
                title: 'Approve Transaction',
                text: 'Are you sure you want to approve this transaction?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, add a note!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Add a Note',
                        input: 'textarea',
                        inputLabel: 'Status Remark',
                        inputPlaceholder: 'Enter any remarks for this approval...',
                        inputAttributes: {
                            maxlength: 255
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#6B0D0D',
                        cancelButtonColor: '#6c757d'
                    }).then((noteResult) => {
                        if (noteResult.isConfirmed) {
                            const statusRemark = noteResult.value;

                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', './endpoints/update_transaction_status.php', true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            xhr.onreadystatechange = function () {
                                if (xhr.readyState === 4) {
                                    if (xhr.status === 200) {
                                        try {
                                            const response = JSON.parse(xhr.responseText);
                                            if (response.status === 'success') {
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: response.message,
                                                    showConfirmButton: false,
                                                    timer: 3000,
                                                    confirmButtonColor: '#6B0D0D'
                                                });
                                                fetchTransactions(); // Refresh the transaction list
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: response.message,
                                                    showConfirmButton: false,
                                                    timer: 3000,
                                                    confirmButtonColor: '#6B0D0D'
                                                });
                                            }
                                        } catch (e) {
                                            console.error('Error parsing response:', e);
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'An unexpected error occurred.',
                                                showConfirmButton: false,
                                                timer: 3000,
                                                confirmButtonColor: '#6B0D0D'
                                            });
                                        }
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Failed to communicate with the server.',
                                            showConfirmButton: false,
                                            timer: 3000,
                                            confirmButtonColor: '#6B0D0D'
                                        });
                                    }
                                }
                            };
                            xhr.send(`transaction_id=${transactionId}&status=Approved&status_remark=${encodeURIComponent(statusRemark)}`);
                        }
                    });
                }
            });
        }

        function declineTransaction(transactionId, itemId, quantityBorrowed) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will return the borrowed quantity to the item.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, decline it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', './endpoints/decline_transaction.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.message,
                                            showConfirmButton: false,
                                            timer: 3000,
                                            confirmButtonColor: '#6B0D0D'
                                        });
                                        fetchTransactions(); // Refresh the transaction list
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: response.message,
                                            showConfirmButton: false,
                                            timer: 3000,
                                            confirmButtonColor: '#6B0D0D'
                                        });
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'An unexpected error occurred.',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        confirmButtonColor: '#6B0D0D'
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'An error occurred while declining the transaction.',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    confirmButtonColor: '#6B0D0D'
                                });
                            }
                        }
                    };

                    xhr.send(`transaction_id=${transactionId}&item_id=${itemId}&quantity_borrowed=${quantityBorrowed}`);
                }
            });
        }

        function fetchTransactions() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', './endpoints/get_item_transactions.php', true);
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
                                    <td>${transaction.contact_no}</td>
                                    <td>${transaction.email}</td>
                                    <td class="button">
                                        <button class="addButton" style="width: 7rem;" onclick="approveTransaction(${transaction.transaction_id})">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="addButton1" style="width: 7rem;" 
                                            onclick="declineTransaction(${transaction.transaction_id}, '${transaction.item_id}', ${transaction.quantity_borrowed})">
                                            <i class="fas fa-times"></i> Decline
                                        </button>
                                    </td>
                                `;
                                tbody.appendChild(row);
                            });
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: response.message || 'No transactions found',
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

        // Fetch transactions on page load
        document.addEventListener('DOMContentLoaded', fetchTransactions);

        function fetchItemDetails(itemId) {
            if (!itemId) {
                document.getElementById('item_brand').value = '';
                document.getElementById('item_quantity').value = '';
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./endpoints/fetch_item_details.php?item_id=${itemId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            document.getElementById('item_brand').value = response.data.brand;
                            document.getElementById('item_quantity').value = response.data.quantity;
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

        function confirmBorrow() {
            const itemId = document.getElementById('origin_item').value;
            const teacherId = document.getElementById('teacher').value;
            const quantity = parseInt(document.getElementById('quantity').value, 10);
            const student = document.getElementById('student').value;
            const returnDate = document.getElementById('returnDate').value;
            const availableQuantity = parseInt(document.getElementById('item_quantity').value, 10);

            if (!itemId || !teacherId || isNaN(quantity) || !returnDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Fields',
                    text: 'Please fill out all fields.',
                    confirmButtonColor: '#6B0D0D',
                });
                return;
            }

            if (quantity > availableQuantity || quantity <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Quantity',
                    text: `Invalid quantity. Available: ${availableQuantity}`,
                    confirmButtonColor: '#6B0D0D',
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Borrow',
                text: "Are you sure you want to proceed with this transaction?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, confirm!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', './endpoints/borrow_item.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    Swal.fire({
                                        icon: response.status === 'success' ? 'success' : 'error',
                                        title: response.message,
                                        confirmButtonColor: '#6B0D0D',
                                    });

                                    if (response.status === 'success') {
                                        // Reset form
                                        document.getElementById('origin_item').value = '';
                                        document.getElementById('item_brand').value = '';
                                        document.getElementById('item_quantity').value = '';
                                        document.getElementById('teacher').value = '';
                                        document.getElementById('quantity').value = '';
                                        document.getElementById('student').value = '';
                                        document.getElementById('returnDate').value = '';

                                        // Close modal
                                        document.querySelector('.addContainer').style.display = 'none';

                                        // Refresh transactions
                                        setTimeout(fetchTransactions, 1000);
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'An unexpected error occurred.',
                                        confirmButtonColor: '#6B0D0D',
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

                    const params = `item_id=${itemId}&teacher=${teacherId}&quantity=${quantity}&student=${student}&return_date=${returnDate}`;
                    xhr.send(params);
                }
            });
        }

        function editTransaction(transactionId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./endpoints/get_transaction_details.php?id=${transactionId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const transaction = response.data;

                            const editContainer = document.querySelector('.editContainer');
                            editContainer.style.display = 'flex';
                            editContainer.dataset.transactionId = transactionId;

                            // Populate fields
                            document.getElementById('edit_origin_item').value = transaction.item_id;
                            document.getElementById('edit_origin_item_name').value = transaction.item_name;
                            document.getElementById('edit_item_brand').value = transaction.brand;
                            document.getElementById('edit_item_quantity').value = transaction.available_quantity;
                            document.getElementById('edit_teacher').value = transaction.teacher_id;
                            document.getElementById('edit_quantity').value = transaction.quantity_borrowed;
                            document.getElementById('edit_borrow_date').value = transaction.borrow_date;
                            document.getElementById('edit_return_date').value = transaction.return_date;
                            document.getElementById('edit_class_date').value = transaction.class_date;
                            document.getElementById('edit_schedule_from').value = transaction.schedule_from;
                            document.getElementById('edit_schedule_to').value = transaction.schedule_to;

                            fetchItems(transaction.item_id);
                            fetchTeachers(transaction.users_id);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: response.message,
                                confirmButtonColor: '#6B0D0D',
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            xhr.send();
        }

        function saveEditTransaction() {
            const transactionId = document.querySelector('.editContainer').dataset.transactionId;
            const itemId = document.getElementById('edit_origin_item').value;
            const teacherId = document.getElementById('edit_teacher').value;
            const quantity = document.getElementById('edit_quantity').value;
            const borrowDate = document.getElementById('edit_borrow_date').value;
            const returnDate = document.getElementById('edit_return_date').value;
            const classDate = document.getElementById('edit_class_date').value;
            const scheduleFrom = document.getElementById('edit_schedule_from').value;
            const scheduleTo = document.getElementById('edit_schedule_to').value;

            if (!transactionId || !itemId || !teacherId || !quantity || !borrowDate || !returnDate || !classDate || !scheduleFrom || !scheduleTo) {
                Swal.fire({
                    icon: 'error',
                    title: 'All fields are required.',
                    confirmButtonColor: '#6B0D0D',
                });
                return;
            }

            const data = new FormData();
            data.append('transaction_id', transactionId);
            data.append('item_id', itemId);
            data.append('teacher', teacherId);
            data.append('quantity', quantity);
            data.append('borrow_date', borrowDate);
            data.append('return_date', returnDate);
            data.append('class_date', classDate);
            data.append('schedule_from', scheduleFrom);
            data.append('schedule_to', scheduleTo);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', './endpoints/edit_borrowed_item.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        Swal.fire({
                            icon: response.status === 'success' ? 'success' : 'error',
                            title: response.message,
                            confirmButtonColor: '#6B0D0D',
                        }).then(() => {
                            if (response.status === 'success') {
                                document.querySelector('.editContainer').style.display = 'none';
                                fetchTransactions();
                            }
                        });
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            xhr.send(data);
        }

        function fetchItems(selectedItemId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', './endpoints/get_items.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const itemDropdown = document.getElementById('edit_origin_item');
                            itemDropdown.innerHTML = '<option value="">Choose an Item</option>';

                            response.data.forEach((item) => {
                                const option = document.createElement('option');
                                option.value = item.id;
                                option.textContent = `${item.name} (${item.brand})`;
                                if (item.id == selectedItemId) {
                                    option.selected = true;
                                    document.getElementById('edit_item_quantity').value = item.quantity;
                                }
                                itemDropdown.appendChild(option);
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            xhr.send();
        }

        function fetchTeachers(selectedTeacherId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', './endpoints/get_teachers.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const teacherDropdown = document.getElementById('edit_teacher');
                            teacherDropdown.innerHTML = '<option value="">Choose a Faculty Member</option>';

                            response.data.forEach((teacher) => {
                                const option = document.createElement('option');
                                option.value = teacher.id;
                                option.textContent = `${teacher.first_name} ${teacher.last_name}`;

                                if (teacher.id == selectedTeacherId) {
                                    option.selected = true;
                                }

                                teacherDropdown.appendChild(option);
                            });
                        } else {
                            console.error('Failed to fetch teachers:', response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            xhr.send();
        }

        // Function to filter the table based on the search input
        function filterTable() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const tableRows = document.querySelectorAll('.tableContainer tbody tr');

            tableRows.forEach(row => {
                const itemName = row.children[2].textContent.toLowerCase(); // Column 3: Item Name
                const fullName = row.children[7].textContent.toLowerCase(); // Column 8: Fullname
                const email = row.children[9].textContent.toLowerCase(); // Column 10: Email

                if (itemName.includes(searchValue) || fullName.includes(searchValue) || email.includes(searchValue)) {
                    row.style.display = ''; // Show row
                } else {
                    row.style.display = 'none'; // Hide row
                }
            });
        }

        function printTable() {
            const tableContainer = document.querySelector('.tableContainer');
            const rows = tableContainer.querySelectorAll('tr');

            // Temporarily hide the "Action" column (last column)
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = 'none'; // Hide last cell
                }
            });

            // Get HTML for printing
            const printContent = tableContainer.outerHTML;
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Borrow Requests</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 20px;
                            color: #333;
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
                        h1 {
                            text-align: center;
                            color: #6B0D0D;
                            margin-bottom: 20px;
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
                        <h1>CSSPE Inventory & Information System - Borrow Requests</h1>
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

            // Restore visibility after print
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = ''; // Restore visibility
                }
            });
        }
    </script>
</body>

</html>