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

// Validate and fetch the brand ID from the URL
$brandId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$brandId) {
    die("Invalid brand ID.");
}

// Fetch the brand name based on the brand ID
$brandQuery = "SELECT name, item_id FROM brands WHERE id = ?";
$stmt = $conn->prepare($brandQuery);
$stmt->bind_param("i", $brandId);
$stmt->execute();
$brandResult = $stmt->get_result();

if ($brandResult->num_rows > 0) {
    $brandRow = $brandResult->fetch_assoc();
    $brandName = $brandRow['name'];
    $itemId = $brandRow['item_id']; // Fetch the item_id
} else {
    die("Brand not found.");
}

$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of records for pagination specific to the brand
$totalQuery = "SELECT COUNT(*) AS total FROM item_quantities WHERE brand_id = ?";
$stmt = $conn->prepare($totalQuery);
$stmt->bind_param("i", $brandId);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated records with status
$sql = "
    SELECT 
        iq.id, 
        iq.unique_id, 
        COALESCE(it.status, 'Available') AS status
    FROM item_quantities iq
    LEFT JOIN transaction_item_quantities tiq ON iq.id = tiq.item_quantity_id
    LEFT JOIN item_transactions it ON tiq.transaction_id = it.transaction_id
    WHERE iq.brand_id = ?
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $brandId, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all item quantities and their unique IDs
$itemQuantities = [];
while ($row = $result->fetch_assoc()) {
    $itemQuantities[$row['unique_id']] = [
        'id' => $row['id'],
        'unique_id' => $row['unique_id'],
        'status' => $row['status'] // Default status from item_transactions
    ];
}

// Fetch lost or damaged statuses from returned_items
$uniqueIds = array_keys($itemQuantities);
if (!empty($uniqueIds)) {
    $placeholders = implode(',', array_fill(0, count($uniqueIds), '?'));
    $statusQuery = "
        SELECT unique_id_remark, status 
        FROM returned_items 
        WHERE unique_id_remark IN ($placeholders) AND status IN ('Lost', 'Damaged')
    ";

    $stmt = $conn->prepare($statusQuery);
    $stmt->bind_param(str_repeat('s', count($uniqueIds)), ...$uniqueIds);
    $stmt->execute();
    $statusResult = $stmt->get_result();

    while ($statusRow = $statusResult->fetch_assoc()) {
        $uniqueId = $statusRow['unique_id_remark'];
        if (isset($itemQuantities[$uniqueId])) {
            $itemQuantities[$uniqueId]['status'] = $statusRow['status']; // Override with Lost or Damaged
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Quantities</title>

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

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-available {
            background-color: #e6f7ee;
            color: #0d8a45;
            border: 1px solid #b7e6c9;
        }

        .status-borrowed {
            background-color: #fff8e6;
            color: #b86e00;
            border: 1px solid #ffe0b2;
        }

        .status-maintenance {
            background-color: #e6f4ff;
            color: #0062cc;
            border: 1px solid #b6d4fe;
        }

        .status-unavailable {
            background-color: #feebeb;
            color: #d9534f;
            border: 1px solid #f5c2c2;
        }

        /* Action Buttons */
        .addButton,
        .addButton1 {
            padding: 7px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
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
                min-width: 600px;
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
        }

        @media (max-width: 576px) {
            .pagination a {
                padding: 6px 10px;
                font-size: 13px;
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

            body.dark-mode-supported th {
                background-color: #333;
            }

            body.dark-mode-supported tr:hover {
                background-color: #272727;
            }

            body.dark-mode-supported .status-available {
                background-color: #133a24;
                border-color: #0d8a45;
            }

            body.dark-mode-supported .status-borrowed {
                background-color: #3a2e13;
                border-color: #b86e00;
            }

            body.dark-mode-supported .status-maintenance {
                background-color: #132a3a;
                border-color: #0062cc;
            }

            body.dark-mode-supported .status-unavailable {
                background-color: #3a1313;
                border-color: #d9534f;
            }
        }

        /* Print styles */
        @media print {

            .sidebar,
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

            .addButton {
                display: none;
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
                    <i class="fas fa-cubes"></i>
                    Item Quantities
                    <span>for <?php echo htmlspecialchars($brandName); ?></span>
                </h2>

                <!-- Back Button -->
                <div style="margin-bottom: 20px;">
                    <a href="inventory_brands.php?id=<?php echo $itemId; ?>" class="addButton1"
                        style="text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Brands
                    </a>
                </div>

                <!-- Table -->
                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Unique ID</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (!empty($itemQuantities)): ?>
                                <?php foreach ($itemQuantities as $row): ?>
                                    <tr data-id="<?php echo $row['id']; ?>">
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['unique_id']); ?></td>
                                        <td>
                                            <?php
                                            $status = strtolower(htmlspecialchars($row['status']));
                                            $statusClass = 'status-unavailable';

                                            if ($status === 'available') {
                                                $statusClass = 'status-available';
                                            } else if ($status === 'borrowed') {
                                                $statusClass = 'status-borrowed';
                                            } else if ($status === 'maintenance') {
                                                $statusClass = 'status-maintenance';
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="addButton1" onclick="deleteQuantity(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 30px;">
                                        <i class="fas fa-info-circle"
                                            style="font-size: 24px; color: #6c757d; margin-bottom: 10px; display: block;"></i>
                                        <p style="margin: 0; color: #6c757d;">No records found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?id=<?php echo $brandId; ?>&page=<?php echo $page - 1; ?>" class="prev">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?id=<?php echo $brandId; ?>&page=<?php echo $i; ?>"
                            class="<?php echo ($i === $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?id=<?php echo $brandId; ?>&page=<?php echo $page + 1; ?>" class="next">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

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

        // Delete quantity function
        function deleteQuantity(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Make an AJAX request to delete the record
                    fetch(`./endpoints/delete_quantity.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    confirmButtonColor: '#6B0D0D',
                                    timer: 3000,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Find and remove the row from the table
                                    const row = document.querySelector(`tr[data-id="${id}"]`);
                                    if (row) {
                                        row.remove();
                                    } else {
                                        location.reload(); // Reload if row can't be found
                                    }
                                });
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
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An unexpected error occurred. Please try again.',
                                confirmButtonColor: '#6B0D0D'
                            });
                        });
                }
            });
        }
    </script>
</body>

</html>