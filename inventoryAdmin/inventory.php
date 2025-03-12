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

// Fetch school years based on their values
$schoolYearQuery = "
    SELECT id, school_year, semester 
    FROM school_years 
    ORDER BY start_date DESC
";
$schoolYearStmt = $conn->prepare($schoolYearQuery);
$schoolYearStmt->execute();
$schoolYearResult = $schoolYearStmt->get_result();

// Prepare options for the dropdown
$schoolYearOptions = "";
while ($row = $schoolYearResult->fetch_assoc()) {
    $schoolYearOptions .= "<option value='{$row['id']}'>{$row['school_year']} - {$row['semester']}</option>";
}

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter by school year
$schoolYearFilter = isset($_GET['school_year']) && !empty($_GET['school_year']) ? $_GET['school_year'] : null;

// Fetch filtered items with brands and quantities
$itemQuery = "
    SELECT 
        i.id, 
        i.name, 
        i.description, 
        i.type, 
        i.note, 
        i.image, 
        GROUP_CONCAT(b.name SEPARATOR ', ') AS brands, 
        GROUP_CONCAT(b.quantity SEPARATOR ', ') AS quantities
    FROM items i
    LEFT JOIN brands b ON i.id = b.item_id
    WHERE (? IS NULL OR i.created_at BETWEEN 
        (SELECT start_date FROM school_years WHERE id = ?) AND 
        (SELECT end_date FROM school_years WHERE id = ?))
    GROUP BY i.id
    ORDER BY i.created_at DESC 
    LIMIT $limit OFFSET $offset
";
$itemStmt = $conn->prepare($itemQuery);
$itemStmt->bind_param("iii", $schoolYearFilter, $schoolYearFilter, $schoolYearFilter);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

// Get total items for pagination
$totalItemsQuery = "
    SELECT COUNT(*) AS total 
    FROM items 
    WHERE (? IS NULL OR created_at BETWEEN 
        (SELECT start_date FROM school_years WHERE id = ?) AND 
        (SELECT end_date FROM school_years WHERE id = ?))
";
$totalItemsStmt = $conn->prepare($totalItemsQuery);
$totalItemsStmt->bind_param("iii", $schoolYearFilter, $schoolYearFilter, $schoolYearFilter);
$totalItemsStmt->execute();
$totalItemsResult = $totalItemsStmt->get_result();
$totalRow = $totalItemsResult->fetch_assoc();
$totalItems = $totalRow['total'];

$totalPages = ceil($totalItems / $limit);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventories</title>

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
            padding: 12px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .searchBar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 2px 12px rgba(107, 13, 13, 0.1);
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

        td.button {
            white-space: nowrap;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .image {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            flex-wrap: wrap;
            gap: 5px;
        }

        .pagination a {
            color: var(--text);
            text-decoration: none;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: var(--light);
            border-color: var(--primary);
        }

        .pagination a.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
            font-weight: bold;
        }

        .pagination a.prev,
        .pagination a.next {
            background-color: var(--white);
            color: var(--primary);
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
            max-width: 600px;
            padding: 0;
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

        .subLoginContainer,
        .sublogoutContainer {
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

        .inputEmail,
        textarea.inputEmail {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: var(--white);
            resize: none;
            font-family: inherit;
        }

        textarea.inputEmail {
            min-height: 120px;
        }

        .inputEmail:focus,
        textarea.inputEmail:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.1);
            outline: none;
        }

        /* Image upload styles */
        .uploadContainer {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }

        .displayImage {
            width: 150px;
            height: 150px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .displayImage img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .uploadButton {
            display: flex;
            justify-content: center;
        }

        .brandQuantityContainer {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantityInput,
        .brandInput {
            flex: 1;
        }

        .quantityInput {
            margin-right: 10px;
        }

        .brandInput {
            margin-left: 10px;
        }

        .addBrandQuantityButton,
        .removeBrandQuantityButton {
            background-color: transparent;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }

        .addBrandQuantityButton:hover {
            color: var(--primary);
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
                flex-wrap: wrap;
            }

            .searchBar {
                flex-basis: 100%;
                order: -1;
                margin-bottom: 10px;
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

            .searchContainer {
                flex-direction: column;
                align-items: stretch;
            }

            .printButton {
                justify-content: space-between;
            }

            .subAddContainer {
                width: 95%;
                transform: scale(1) !important;
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
            .printButton {
                flex-direction: column;
                width: 100%;
            }

            .printButton .addButton,
            .printButton .addButton1 {
                width: 100%;
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

            .pagination a {
                padding: 6px 10px;
                font-size: 13px;
            }
        }

        /* Focus states for accessibility */
        button:focus,
        a:focus,
        input:focus,
        select:focus,
        textarea:focus {
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
        }

        /* Print styles */
        @media print {

            .sidebar,
            .searchContainer,
            .toggle-btn,
            .pagination {
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

            <a href="../inventoryAdmin/inventory.php" class="sidebar-link active">
                <i class="fas fa-boxes"></i> Inventories
            </a>

            <a href="../inventoryAdmin/borrowing.php" class="sidebar-link">
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
                    <i class="fas fa-boxes"></i>
                    Inventories
                    <span>Manage inventory items</span>
                </h2>

                <!-- Search & Action Buttons -->
                <div class="searchContainer">
                    <select name="" class="addButton size" id="typeFilter">
                        <option value="">Choose Type</option>
                        <option value="Sport">Sport</option>
                        <option value="Gadget">Gadget</option>
                    </select>

                    <input id="searchBar" class="searchBar" type="text" placeholder="Search items...">

                    <button style="width: 80px" class="addButton size" onclick="resetPage()">
                        <i class="fas fa-arrows-rotate"></i>
                    </button>

                    <div class="printButton">
                        <select name="school_year" class="addButton size" id="schoolYearFilter"
                            onchange="filterBySchoolYear(this.value)">
                            <option value="">Choose School Year</option>
                            <?php echo $schoolYearOptions; ?>
                        </select>

                        <button class="addButton size" onclick="printTable()">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="addProgram()" class="addButton size">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Brands</th>
                                <th>Type</th>
                                <th>Warning Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php while ($item = $itemResult->fetch_assoc()): ?>
                                <?php
                                // Parse brands and quantities into arrays
                                $brands = explode(', ', $item['brands']);
                                $quantities = explode(', ', $item['quantities']);
                                $brandsData = [];
                                for ($i = 0; $i < count($brands); $i++) {
                                    $brandsData[] = [
                                        'name' => $brands[$i],
                                        'quantity' => $quantities[$i]
                                    ];
                                }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['id']); ?></td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                        <?php if (!empty($item['image'])): ?>
                                            <img class="image"
                                                src="../assets/uploads/<?php echo htmlspecialchars($item['image']); ?>"
                                                alt="Item Image">
                                        <?php else: ?>
                                            <span>No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td>
                                        <button
                                            onclick="window.location.href='inventory_brands.php?id=<?php echo $item['id']; ?>'"
                                            class="addButton" style="width: 5rem;">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['type'] === 'sport' ? 'Sport' : ($item['type'] === 'gadgets' ? 'Gadget' : 'Unknown')); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['note']); ?></td>
                                    <td class="button">
                                        <button onclick="editItem(this)" class="addButton" style="width: 5rem;"
                                            data-id="<?php echo $item['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($item['description']); ?>"
                                            data-note="<?php echo htmlspecialchars($item['note']); ?>"
                                            data-type="<?php echo htmlspecialchars($item['type']); ?>"
                                            data-image="../assets/uploads/<?php echo htmlspecialchars($item['image']); ?>"
                                            data-brands="<?php echo htmlspecialchars(json_encode($brandsData)); ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <button onclick="deleteItem(<?php echo $item['id']; ?>)" class="addButton1"
                                            style="width: 5rem;">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="prev">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo ($i === $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="next">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <form id="addItemForm" method="POST" enctype="multipart/form-data">
        <div class="addContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <i class="fas fa-plus-circle"></i>
                    <p>Add Item</p>
                </div>

                <div class="sublogoutContainer">
                    <div class="uploadContainer">
                        <div class="displayImage">
                            <img id="addPreviewImage" src="../assets/img/CSSPE.png" alt="Preview Image"
                                style="display: none;">
                        </div>
                        <div class="uploadButton">
                            <input id="addImageUpload" type="file" accept="image/*" style="display: none;"
                                onchange="previewImage('addImageUpload', 'addPreviewImage')" />
                            <button type="button" onclick="triggerImageUpload('addImageUpload')" class="addButton">
                                <i class="fas fa-upload"></i> Upload Image
                            </button>
                        </div>
                    </div>

                    <div class="inputContainer">
                        <label for="itemName">Item Name</label>
                        <input id="itemName" name="name" class="inputEmail" type="text" placeholder="Enter item name"
                            required>
                    </div>

                    <!-- Dynamic Brands and Quantities Container -->
                    <div id="addBrandsContainer">
                        <div class="brandQuantityContainer">
                            <div class="brandInput">
                                <label for="itemBrand">Brand</label>
                                <input name="brands[]" class="inputEmail" type="text" placeholder="Enter brand name"
                                    required>
                            </div>
                            <div class="quantityInput">
                                <label for="itemQuantity">Quantity</label>
                                <input name="quantities[]" class="inputEmail" type="number" placeholder="Enter quantity"
                                    required min="0">
                            </div>
                            <button type="button" class="addBrandQuantityButton" onclick="addBrandQuantity()">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" class="removeBrandQuantityButton" onclick="removeBrandQuantity(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="inputContainer">
                        <label for="itemType">Type</label>
                        <select id="itemType" name="type" class="inputEmail" required>
                            <option value="" disabled selected>Select type</option>
                            <option value="sport">Sport</option>
                            <option value="gadgets">Gadget</option>
                        </select>
                    </div>

                    <div class="inputContainer">
                        <label for="itemNote">Warning Note (Optional)</label>
                        <input id="itemNote" name="note" class="inputEmail" type="text"
                            placeholder="Enter any warning notes">
                    </div>

                    <div class="inputContainer">
                        <label for="itemDescription">Description</label>
                        <textarea id="itemDescription" name="description" class="inputEmail"
                            placeholder="Enter item description" required></textarea>
                    </div>

                    <div class="inputContainer"
                        style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" onclick="addProgram()" class="addButton1"
                            style="width: 100px;">Cancel</button>
                        <button type="submit" class="addButton" style="width: 100px;">Add Item</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Edit Item Modal -->
    <div class="editContainer">
        <div class="subAddContainer">
            <div class="titleContainer">
                <i class="fas fa-edit"></i>
                <p>Edit Item</p>
            </div>

            <div class="subLoginContainer">
                <div class="uploadContainer">
                    <div class="displayImage">
                        <img id="previewImage" src="../assets/img/CSSPE.png" alt="Item Image">
                    </div>
                    <div class="uploadButton">
                        <input id="imageUpload" type="file" accept="image/*" style="display: none;"
                            onchange="previewImage('imageUpload', 'previewImage')" />
                        <button type="button" onclick="triggerImageUpload('imageUpload')" class="addButton">
                            <i class="fas fa-upload"></i> Change Image
                        </button>
                    </div>
                </div>

                <div class="inputContainer">
                    <label for="edit_item_name">Item Name</label>
                    <input id="edit_item_name" class="inputEmail" type="text" placeholder="Item Name">
                </div>

                <!-- Dynamic Brands and Quantities Container -->
                <div id="editBrandsContainer">
                    <!-- Brand and Quantity fields will be dynamically added here -->
                </div>

                <div class="inputContainer">
                    <label for="edit_type">Type</label>
                    <select id="edit_type" class="inputEmail">
                        <option value="sport">Sport</option>
                        <option value="gadgets">Gadget</option>
                    </select>
                </div>

                <div class="inputContainer">
                    <label for="edit_note">Warning Note</label>
                    <input id="edit_note" class="inputEmail" type="text" placeholder="Warning Note">
                </div>

                <div class="inputContainer">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" class="inputEmail" placeholder="Description"></textarea>
                </div>

                <div class="inputContainer"
                    style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button onclick="editProgram()" class="addButton1" style="width: 100px;">Cancel</button>
                    <button class="addButton" style="width: 100px;" onclick="saveItem()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        function addBrandQuantity() {
            const container = document.getElementById('addBrandsContainer');
            const newInputs = document.createElement('div');
            newInputs.className = 'brandQuantityContainer';
            newInputs.innerHTML = `
        <div class="brandInput">
            <label for="itemBrand">Brand</label>
            <input name="brands[]" class="inputEmail" type="text" placeholder="Enter brand name" required>
        </div>
        <div class="quantityInput">
            <label for="itemQuantity">Quantity</label>
            <input name="quantities[]" class="inputEmail" type="number" placeholder="Enter quantity" required min="0">
        </div>
        <button type="button" class="addBrandQuantityButton" onclick="addBrandQuantity()">
            <i class="fas fa-plus"></i>
        </button>
        <button type="button" class="removeBrandQuantityButton" onclick="removeBrandQuantity(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
            container.appendChild(newInputs);
        }
        function removeBrandQuantity(button) {
            button.parentElement.remove();
        }
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

        // Filter by school year
        function filterBySchoolYear(schoolYearId) {
            const url = new URL(window.location.href);
            if (schoolYearId) {
                url.searchParams.set('school_year', schoolYearId);
            } else {
                url.searchParams.delete('school_year');
            }
            window.location.href = url.toString();
        }

        // Reset page filters
        function resetPage() {
            window.location.href = 'inventory.php';
        }

        // Filter table by type
        document.getElementById('typeFilter').addEventListener('change', function () {
            filterTableByType();
        });

        function filterTableByType() {
            // Get the selected value from the dropdown
            var selectedType = document.getElementById('typeFilter').value.toLowerCase();

            // Get all table rows
            var rows = document.querySelectorAll('#tableBody tr');

            // Loop through all rows and show/hide based on type match
            rows.forEach(function (row) {
                var typeCell = row.cells[6]; // 'Type' column (index starts from 0)
                var type = typeCell ? typeCell.textContent.toLowerCase() : '';

                // Check if the selected type matches the type in the row, or if 'All' is selected
                if (selectedType === '' || type.includes(selectedType)) {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
            });
        }

        // Search functionality
        const searchBar = document.getElementById('searchBar');
        const tableBody = document.getElementById('tableBody');

        searchBar.addEventListener('input', function () {
            const searchTerm = searchBar.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');

            for (const row of rows) {
                const cells = row.getElementsByTagName('td');
                let match = false;

                for (const cell of cells) {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        match = true;
                        break;
                    }
                }

                row.style.display = match ? '' : 'none';
            }
        });

        // Image preview functionality
        function previewImage(inputId, previewId) {
            const fileInput = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result; // Update the image preview
                    preview.style.display = 'block'; // Make the preview visible
                };
                reader.readAsDataURL(fileInput.files[0]);
            } else {
                preview.style.display = 'none'; // Hide the preview if no file is selected
            }
        }

        // Trigger file input click
        function triggerImageUpload(inputId) {
            document.getElementById(inputId).click();
        }

        // Toggle add item modal
        function addProgram() {
            const addContainer = document.querySelector('.addContainer');

            if (addContainer.style.display === 'none' || addContainer.style.display === '') {
                addContainer.style.display = 'flex';
            } else {
                addContainer.style.display = 'none';
            }
        }

        // Toggle edit item modal
        function editProgram() {
            const editContainer = document.querySelector('.editContainer');

            if (editContainer.style.display === 'none' || editContainer.style.display === '') {
                editContainer.style.display = 'flex';
            } else {
                editContainer.style.display = 'none';
            }
        }

        // Add Item Form Submission
        document.getElementById('addItemForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('name', document.getElementById('itemName').value.trim());
            formData.append('description', document.getElementById('itemDescription').value.trim());
            formData.append('type', document.getElementById('itemType').value.trim());
            formData.append('note', document.getElementById('itemNote').value.trim());

            // Append image if uploaded
            const imageUpload = document.getElementById('addImageUpload');
            if (imageUpload.files.length > 0) {
                formData.append('image', imageUpload.files[0]);
            }

            // Append brands and quantities
            const brandInputs = document.querySelectorAll('input[name="brands[]"]');
            const quantityInputs = document.querySelectorAll('input[name="quantities[]"]');

            brandInputs.forEach((brandInput, index) => {
                formData.append('brands[]', brandInput.value.trim());
                formData.append('quantities[]', quantityInputs[index].value.trim());
            });

            fetch('./endpoints/add_item.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            confirmButtonColor: '#6B0D0D',
                            timer: 3000,
                            showConfirmButton: false,
                        }).then(() => {
                            location.reload(); // Reload the page after success
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to add item.',
                            confirmButtonColor: '#6B0D0D',
                            timer: 3000,
                            showConfirmButton: false,
                        });
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong!',
                        confirmButtonColor: '#6B0D0D',
                        timer: 3000,
                        showConfirmButton: false,
                    });
                });
        });

        function editItem(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const note = button.getAttribute('data-note');
            const type = button.getAttribute('data-type');
            const image = button.getAttribute('data-image');
            const brands = JSON.parse(button.getAttribute('data-brands')); // Array of brands and quantities

            document.querySelector('.editContainer').style.display = 'flex';

            // Set basic fields
            document.getElementById('edit_item_name').value = name;
            document.getElementById('edit_note').value = note;
            document.getElementById('edit_type').value = type || 'sport';
            document.getElementById('edit_description').value = description || '';

            // Handle the preview image
            const previewImage = document.getElementById('previewImage');
            if (image && image.trim()) {
                previewImage.src = image;
                previewImage.style.display = 'block';
            } else {
                previewImage.style.display = 'none';
            }
            previewImage.setAttribute('data-id', id);

            // Populate brands and quantities
            const brandsContainer = document.getElementById('editBrandsContainer');
            brandsContainer.innerHTML = ''; // Clear existing fields

            brands.forEach((brand, index) => {
                const brandDiv = document.createElement('div');
                brandDiv.className = 'brandQuantityContainer';
                brandDiv.innerHTML = `
            <div class="brandInput">
                <label for="edit_brand_${index}">Brand</label>
                <input id="edit_brand_${index}" name="edit_brands[]" class="inputEmail" type="text" placeholder="Enter brand name" value="${brand.name}" required>
                <input type="hidden" name="edit_brand_ids[]" value="${brand.brand_id}">
            </div>
            <div class="quantityInput">
                <label for="edit_quantity_${index}">Quantity</label>
                <input id="edit_quantity_${index}" name="edit_quantities[]" class="inputEmail" type="number" placeholder="Enter quantity" value="${brand.quantity}" required min="0">
            </div>
            <button type="button" class="addBrandQuantityButton" onclick="addEditBrandQuantity()">
                <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="removeBrandQuantityButton" onclick="removeEditBrandQuantity(this)">
                <i class="fas fa-trash"></i>
            </button>
        `;
                brandsContainer.appendChild(brandDiv);
            });
        }

        function addEditBrandQuantity() {
            const container = document.getElementById('editBrandsContainer'); // Correct container ID
            const index = container.children.length; // Get the current number of brand inputs to generate a unique index

            const newInputs = document.createElement('div');
            newInputs.className = 'brandQuantityContainer';
            newInputs.innerHTML = `
        <div class="brandInput">
            <label for="edit_brand_${index}">Brand</label>
            <input id="edit_brand_${index}" name="edit_brands[]" class="inputEmail" type="text" placeholder="Enter brand name" required>
            <input type="hidden" name="edit_brand_ids[]" value=""> <!-- Empty brand_id for new brands -->
        </div>
        <div class="quantityInput">
            <label for="edit_quantity_${index}">Quantity</label>
            <input id="edit_quantity_${index}" name="edit_quantities[]" class="inputEmail" type="number" placeholder="Enter quantity" required min="0">
        </div>
        <button type="button" class="addBrandQuantityButton" onclick="addEditBrandQuantity()">
            <i class="fas fa-plus"></i>
        </button>
        <button type="button" class="removeBrandQuantityButton" onclick="removeEditBrandQuantity(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
            container.appendChild(newInputs);
        }

        function removeEditBrandQuantity(button) {
            button.parentElement.remove(); // Remove the entire brand-quantity container
        }

        function saveItem() {
            const id = document.getElementById('previewImage').getAttribute('data-id');
            const name = document.getElementById('edit_item_name').value.trim();
            const note = document.getElementById('edit_note').value.trim();
            const type = document.getElementById('edit_type').value.trim();
            const description = document.getElementById('edit_description').value.trim();

            const brands = [];
            const brandInputs = document.querySelectorAll('input[name="edit_brands[]"]');
            const brandIdInputs = document.querySelectorAll('input[name="edit_brand_ids[]"]');
            const quantityInputs = document.querySelectorAll('input[name="edit_quantities[]"]');

            brandInputs.forEach((brandInput, index) => {
                const brand = brandInput.value.trim();
                const brandId = brandIdInputs[index].value.trim();
                const quantity = quantityInputs[index].value.trim();
                if (brand && quantity) {
                    brands.push({ brand_id: brandId, name: brand, quantity: quantity });
                }
            });

            if (!id || !name || !type || !description || brands.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill all required fields before saving!',
                    confirmButtonColor: '#6B0D0D',
                });
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('name', name);
            formData.append('note', note);
            formData.append('type', type);
            formData.append('description', description);
            formData.append('brands', JSON.stringify(brands));

            const imageInput = document.getElementById('imageUpload');
            if (imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }

            fetch('./endpoints/edit_item.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'The item has been updated successfully!',
                            confirmButtonColor: '#6B0D0D',
                            timer: 3000,
                            showConfirmButton: false,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: data.message || 'An error occurred while updating the item.',
                            confirmButtonColor: '#6B0D0D',
                            timer: 3000,
                            showConfirmButton: false,
                        });
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again later.',
                        confirmButtonColor: '#6B0D0D',
                        timer: 3000,
                        showConfirmButton: false,
                    });
                });
        }

        // Delete Item Function
        function deleteItem(itemId) {
            // Use SweetAlert to confirm deletion
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action will permanently delete the item!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with deletion
                    fetch('./endpoints/delete_item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: itemId
                        })
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    confirmButtonColor: '#6B0D0D',
                                    timer: 3000,
                                    showConfirmButton: false,
                                }).then(() => {
                                    // Reload the page or remove the row from the table
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message || 'Failed to delete item.',
                                    confirmButtonColor: '#6B0D0D',
                                    timer: 3000,
                                    showConfirmButton: false,
                                });
                            }
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An unexpected error occurred.',
                                confirmButtonColor: '#6B0D0D',
                                timer: 3000,
                                showConfirmButton: false,
                            });
                        });
                }
            });
        }

        // Print Table Function
        function printTable() {
            const tableContainer = document.querySelector('.tableContainer');
            const rows = tableContainer.querySelectorAll('tr');

            // Hide the last column (Actions)
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = 'none'; // Hide last column
                }
            });

            // Prepare the print content
            const printContent = tableContainer.outerHTML;
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Inventory Items</title>
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
                        img {
                            max-width: 50px;
                            max-height: 50px;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <img src="../assets/img/CSSPE.png" alt="Logo">
                        <h1>CSSPE Inventory & Information System - Inventory Items</h1>
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

            // Restore visibility of the last column
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = ''; // Restore last column visibility
                }
            });
        }
    </script>
</body>

</html>