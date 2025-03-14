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

// Fetch the 10 latest items
$itemQuery = "
    SELECT 
        i.name, 
        i.image, 
        GROUP_CONCAT(CONCAT(b.name, ' - ', b.quantity) ORDER BY b.name SEPARATOR '<br>') AS brand_details
    FROM items i
    LEFT JOIN brands b ON i.id = b.item_id
    GROUP BY i.id
    ORDER BY i.created_at DESC
    LIMIT 10
";

$itemStmt = $conn->prepare($itemQuery);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

$items = [];
if ($itemResult->num_rows > 0) {
    while ($itemRow = $itemResult->fetch_assoc()) {
        $items[] = $itemRow;
    }
}


// Fetch borrowed, returned, and overdue counts
$transactionQuery = "
    SELECT 
        SUM(CASE WHEN status IN ('Pending', 'Approved') THEN quantity_borrowed ELSE 0 END) AS total_borrowed, 
        SUM(quantity_returned) AS total_returned,
        COUNT(CASE WHEN return_date < CURDATE() AND status != 'Returned' THEN 1 END) AS total_overdue
    FROM item_transactions";
$transactionStmt = $conn->prepare($transactionQuery);
$transactionStmt->execute();
$transactionResult = $transactionStmt->get_result();
$transactionData = $transactionResult->fetch_assoc();

$totalBorrowed = $transactionData['total_borrowed'] ?? 0;
$totalReturned = $transactionData['total_returned'] ?? 0;
$totalOverdue = $transactionData['total_overdue'] ?? 0;


// Calculate available and recently added items
$itemMetricsQuery = "
    SELECT 
        SUM(CAST(quantity AS UNSIGNED)) AS total_available,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) AS total_recently_added
    FROM items";
$itemMetricsStmt = $conn->prepare($itemMetricsQuery);
$itemMetricsStmt->execute();
$itemMetricsResult = $itemMetricsStmt->get_result();
$itemMetricsData = $itemMetricsResult->fetch_assoc();

$totalAvailable = $itemMetricsData['total_available'] ?? 0;
$totalRecentlyAdded = $itemMetricsData['total_recently_added'] ?? 0;

// Calculate lost, damaged, replaced items
$returnedMetricsQuery = "
    SELECT 
        SUM(CASE WHEN status = 'Lost' THEN CAST(quantity_returned AS UNSIGNED) ELSE 0 END) AS total_lost,
        SUM(CASE WHEN status = 'Damaged' THEN CAST(quantity_returned AS UNSIGNED) ELSE 0 END) AS total_damaged,
        SUM(CASE WHEN status = 'Replaced' THEN CAST(quantity_returned AS UNSIGNED) ELSE 0 END) AS total_replaced
    FROM returned_items";
$returnedMetricsStmt = $conn->prepare($returnedMetricsQuery);
$returnedMetricsStmt->execute();
$returnedMetricsResult = $returnedMetricsStmt->get_result();
$returnedMetricsData = $returnedMetricsResult->fetch_assoc();

$totalLost = $returnedMetricsData['total_lost'] ?? 0;
$totalDamaged = $returnedMetricsData['total_damaged'] ?? 0;
$totalReplaced = $returnedMetricsData['total_replaced'] ?? 0;

// Fetch Borrowed Items
$borrowedQuery = "
    SELECT 
        t.transaction_id, 
        i.name AS item_name, 
        i.id AS item_id,
        b.name AS brand_name,  
        t.quantity_borrowed, 
        t.return_date, 
        t.borrowed_at, 
        u.first_name, 
        u.last_name, 
        u.contact_no, 
        u.email,
        GROUP_CONCAT(iq.unique_id ORDER BY iq.id SEPARATOR ', ') AS unique_ids
    FROM item_transactions t
    JOIN items i ON t.item_id = i.id
    JOIN brands b ON t.brand_id = b.id  -- Joining brands table
    JOIN users u ON t.users_id = u.id
    JOIN transaction_item_quantities tiq ON t.transaction_id = tiq.transaction_id
    JOIN item_quantities iq ON tiq.item_quantity_id = iq.id
    WHERE t.status IN ('Pending', 'Approved')
    GROUP BY t.transaction_id
    HAVING COUNT(iq.unique_id) >= t.quantity_borrowed
";

$borrowedStmt = $conn->prepare($borrowedQuery);
$borrowedStmt->execute();
$borrowedResult = $borrowedStmt->get_result();
$borrowedItems = $borrowedResult->fetch_all(MYSQLI_ASSOC);


// Fetch Returned Items
$returnedQuery = "
    SELECT 
        t.transaction_id, 
        i.name AS item_name, 
        i.id AS item_id,
        b.name AS brand,  -- Fetch brand name from brands table
        t.quantity_returned, 
        t.returned_at, 
        u.first_name, 
        u.last_name, 
        u.contact_no, 
        u.email,
        GROUP_CONCAT(r.unique_id_remark SEPARATOR ', ') AS unique_ids
    FROM item_transactions t
    JOIN items i ON t.item_id = i.id
    JOIN users u ON t.users_id = u.id
    JOIN brands b ON t.brand_id = b.id  -- Join brands table to get brand name
    LEFT JOIN returned_items r ON t.transaction_id = r.transaction_id AND t.item_id = r.item_id
    WHERE t.status = 'Returned'
    GROUP BY t.transaction_id, i.id, b.name, t.quantity_returned, t.returned_at, u.first_name, u.last_name, u.contact_no, u.email
";

$returnedStmt = $conn->prepare($returnedQuery);
$returnedStmt->execute();
$returnedResult = $returnedStmt->get_result();
$returnedItems = $returnedResult->fetch_all(MYSQLI_ASSOC);

// Fetch Available Items
$availableQuery = "
    SELECT 
        id, 
        name, 
        brand, 
        quantity, 
        description 
    FROM items
    WHERE quantity > 0
";
$availableStmt = $conn->prepare($availableQuery);
$availableStmt->execute();
$availableResult = $availableStmt->get_result();
$availableItems = $availableResult->fetch_all(MYSQLI_ASSOC);

// Fetch Recently Added Items
$recentlyAddedQuery = "
    SELECT 
        i.id, 
        i.name, 
        i.image, 
        i.description, 
        i.quantity, 
        i.created_at,
        GROUP_CONCAT(CONCAT(b.name, ' - ', b.quantity) ORDER BY b.name SEPARATOR '<br>') AS brand_details
    FROM items i
    LEFT JOIN brands b ON i.id = b.item_id
    WHERE i.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY i.id
    ORDER BY i.created_at DESC
";

$recentlyAddedStmt = $conn->prepare($recentlyAddedQuery);
$recentlyAddedStmt->execute();
$recentlyAddedResult = $recentlyAddedStmt->get_result();
$recentlyAddedItems = $recentlyAddedResult->fetch_all(MYSQLI_ASSOC);

// Fetch Lost Items
$lostQuery = "
    SELECT 
        r.return_id,
        r.unique_id_remark,
        i.name AS item_name,
        i.id AS item_id,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        u.first_name,
        u.last_name,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Lost'
";
$lostStmt = $conn->prepare($lostQuery);
$lostStmt->execute();
$lostItems = $lostStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Damaged Items
$damagedQuery = "
    SELECT 
        r.return_id,
        r.unique_id_remark,
        i.name AS item_name,
        i.id AS item_id,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        u.first_name,
        u.last_name,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Damaged'
";
$damagedStmt = $conn->prepare($damagedQuery);
$damagedStmt->execute();
$damagedItems = $damagedStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Replaced Items
$replacedQuery = "
    SELECT 
        r.return_id,
        r.unique_id_remark,
        i.name AS item_name,
        i.id AS item_id,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        u.first_name,
        u.last_name,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Replaced'
";
$replacedStmt = $conn->prepare($replacedQuery);
$replacedStmt->execute();
$replacedItems = $replacedStmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Overdue
$currentDate = date('Y-m-d');
$query = "
SELECT 
    t.transaction_id,
    i.name AS item_name,
    i.id AS item_id,
    i.brand,
    t.quantity_borrowed - IFNULL(t.quantity_returned, 0) AS overdue_quantity,
    t.return_date,
    u.first_name,
    u.last_name,
    u.contact_no,
    u.email
FROM item_transactions t
JOIN items i ON t.item_id = i.id
JOIN users u ON t.users_id = u.id
WHERE t.return_date < ? AND (t.status = 'Pending' OR t.status = 'Approved')
";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $currentDate);
$stmt->execute();
$overdueItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSSPE Inventory Dashboard</title>

    <!-- FontAwesome -->
    <link href="../assets/css/output.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS - For production, replace with a proper build -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Custom styles that complement Tailwind */
        :root {
            --primary: #6B0D0D;
            --primary-dark: #540A0A;
            --primary-light: #8B1D1D;
        }

        .hover-tooltip {
            position: relative;
            cursor: pointer;
        }

        .hover-tooltip .tooltip {
            display: none;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #fff;
            color: #333;
            border: 1px solid #ddd;
            padding: 8px;
            white-space: pre-wrap;
            z-index: 10;
            font-size: 0.875rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            min-width: 120px;
            max-width: 250px;
        }

        .hover-tooltip:hover .tooltip {
            display: block;
        }

        /* Modal transitions */
        .modal-fade {
            transition: opacity 0.3s ease;
        }

        .modal-slide {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .bg-primary {
            background-color: var(--primary);
        }

        .bg-primary-dark {
            background-color: var(--primary-dark);
        }

        .bg-primary-light {
            background-color: var(--primary-light);
        }

        .hover\:bg-primary-dark:hover {
            background-color: var(--primary-dark);
        }

        .hover\:bg-primary-light:hover {
            background-color: var(--primary-light);
        }

        .text-primary {
            color: var(--primary);
        }

        .border-primary-light {
            border-color: var(--primary-light);
        }

        .ring-primary {
            --tw-ring-color: var(--primary);
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .table-print {
                width: 100%;
                border-collapse: collapse;
            }

            .table-print th,
            .table-print td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Toggle Sidebar Button (Mobile Only) -->
    <button id="toggleSidebar" class="lg:hidden fixed top-4 left-4 z-50 bg-primary p-2 rounded-md text-white shadow-lg">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebarOverlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed left-0 top-0 h-full w-64 bg-primary text-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50">
        <div class="p-4 border-b border-primary-light flex items-center space-x-3">
            <img src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>" alt="Profile"
                class="h-12 w-12 rounded-full object-cover bg-white">
            <div class="overflow-hidden">
                <h2 class="font-semibold truncate"><?php echo $fullName; ?></h2>
                <p class="text-xs opacity-75">Inventory Admin</p>
            </div>
        </div>

        <nav class="mt-6 px-2">
            <a href="../homePage/"
                class="flex items-center p-3 mb-2 rounded-lg hover:bg-primary-light transition-colors">
                <i class="fas fa-home w-6"></i>
                <span class="ml-3">Home</span>
            </a>

            <a href="../inventoryAdmin/index.php"
                class="flex items-center p-3 mb-2 rounded-lg bg-primary-light transition-colors">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>

            <a href="../inventoryAdmin/inventory.php"
                class="flex items-center p-3 mb-2 rounded-lg hover:bg-primary-light transition-colors">
                <i class="fas fa-boxes w-6"></i>
                <span class="ml-3">Inventories</span>
            </a>

            <a href="../inventoryAdmin/borrowing.php"
                class="flex items-center p-3 mb-2 rounded-lg hover:bg-primary-light transition-colors">
                <i class="fas fa-clipboard-list w-6"></i>
                <span class="ml-3">Borrow Requests</span>
            </a>

            <a href="../inventoryAdmin/borrowItem.php"
                class="flex items-center p-3 mb-2 rounded-lg hover:bg-primary-light transition-colors">
                <i class="fas fa-hand-holding w-6"></i>
                <span class="ml-3">Borrowed Items</span>
            </a>



            <a href="../inventoryAdmin/notification.php"
                class="flex items-center p-3 mb-2 rounded-lg hover:bg-primary-light transition-colors">
                <i class="fas fa-bell w-6"></i>
                <span class="ml-3">Notifications</span>
            </a>
        </nav>

        <a href="../logout.php"
            class="absolute bottom-0 w-full p-4 border-t border-primary-light flex items-center text-white hover:bg-primary-light transition-colors">
            <i class="fas fa-sign-out-alt w-6"></i>
            <span class="ml-3">Logout</span>
        </a>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen transition-all duration-300 ease-in-out">
        <!-- Header -->
        <header class="bg-primary text-white shadow-md p-4">
            <div class="container mx-auto flex items-center">
                <img src="../assets/img/CSSPE.png" alt="Logo" class="h-10 mr-3">
                <div>
                    <h1 class="text-xl font-bold">CSSPE Inventory & Information System</h1>
                    <p class="text-sm opacity-80">College of Sport Science and Physical Education</p>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="container mx-auto p-4 md:p-6">
            <div class="flex items-center mb-6 pb-3 border-b border-gray-200">
                <i class="fas fa-tachometer-alt p-3 bg-gray-100 text-primary rounded-full mr-3"></i>
                <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Borrowed Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-transform hover:-translate-y-1 hover:shadow-lg"
                    onclick="toggleBorrowedModal()">
                    <div class="bg-blue-600 h-2"></div>
                    <div class="p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Borrowed</h3>
                            <i class="fas fa-hand-holding text-blue-600"></i>
                        </div>
                        <p class="text-3xl font-bold mt-2"><?php echo $totalBorrowed; ?></p>
                    </div>
                </div>

                <!-- Returned Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-transform hover:-translate-y-1 hover:shadow-lg"
                    onclick="toggleReturnedModal()">
                    <div class="bg-green-600 h-2"></div>
                    <div class="p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Returned</h3>
                            <i class="fas fa-undo text-green-600"></i>
                        </div>
                        <p class="text-3xl font-bold mt-2"><?php echo $totalReturned; ?></p>
                    </div>
                </div>

                <!-- Available Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-transform hover:-translate-y-1 hover:shadow-lg"
                    onclick="toggleAvailableModal()">
                    <div class="bg-primary h-2"></div>
                    <div class="p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Available</h3>
                            <i class="fas fa-box-open text-primary"></i>
                        </div>
                        <p class="text-3xl font-bold mt-2"><?php echo $totalAvailable; ?></p>
                    </div>
                </div>

                <!-- Lost Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-transform hover:-translate-y-1 hover:shadow-lg"
                    onclick="toggleLostModal()">
                    <div class="bg-red-600 h-2"></div>
                    <div class="p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Lost</h3>
                            <i class="fas fa-question-circle text-red-600"></i>
                        </div>
                        <p class="text-3xl font-bold mt-2"><?php echo $totalLost; ?></p>
                    </div>
                </div>

                <!-- Damaged Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-transform hover:-translate-y-1 hover:shadow-lg"
                    onclick="toggleDamagedModal()">
                    <div class="bg-yellow-500 h-2"></div>
                    <div class="p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Damaged</h3>
                            <i class="fas fa-bolt text-yellow-500"></i>
                        </div>
                        <p class="text-3xl font-bold mt-2"><?php echo $totalDamaged; ?></p>
                    </div>
                </div>

                <!-- Replaced Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-transform hover:-translate-y-1 hover:shadow-lg"
                    onclick="toggleReplacedModal()">
                    <div class="bg-purple-600 h-2"></div>
                    <div class="p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Replaced Items</h3>
                            <i class="fas fa-exchange-alt text-purple-600"></i>
                        </div>
                        <p class="text-3xl font-bold mt-2"><?php echo $totalReplaced; ?></p>
                    </div>
                </div>

                <!-- Recently Added -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-transform hover:-translate-y-1 hover:shadow-lg"
                    onclick="toggleRecentlyAddedModal()">
                    <div class="bg-cyan-600 h-2"></div>
                    <div class="p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Recently Added</h3>
                            <i class="fas fa-plus-circle text-cyan-600"></i>
                        </div>
                        <p class="text-3xl font-bold mt-2"><?php echo $totalRecentlyAdded; ?></p>
                    </div>
                </div>

                <!-- Overdue Items -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-transform hover:-translate-y-1 hover:shadow-lg"
                    onclick="toggleOverdueModal()">
                    <div class="bg-red-500 h-2"></div>
                    <div class="p-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-700">Overdue</h3>
                            <i class="fas fa-clock text-red-500"></i>
                        </div>
                        <p class="text-3xl font-bold mt-2"><?php echo $totalOverdue; ?></p>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative mb-6">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input id="searchBar" type="text" placeholder="Search items..."
                    class="w-full p-3 pl-10 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition">
            </div>

            <!-- Recent Items Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-lg text-gray-800">Recently Added Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item Name
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Image
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Brands and Quantities
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($item['name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img class="h-12 w-12 object-cover rounded"
                                                src="<?php echo !empty($item['image']) ? '../assets/uploads/' . htmlspecialchars($item['image']) : '../assets/img/CSSPE.png'; ?>"
                                                alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo !empty($item['brand_details']) ? $item['brand_details'] : 'N/A'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No items found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </main>

    <!-- Modal: Borrowed Items -->
    <div id="borrowedModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto modal-fade">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden modal-slide transform translate-y-4 opacity-0">
                <div class="bg-blue-600 text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Borrowed Items</h2>
                    <button onclick="toggleBorrowedModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex flex-wrap items-center gap-4">
                        <select id="filterDropdown" onchange="filterByDate()"
                            class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Filter</option>
                            <option value="all">All</option>
                            <option value="month">This month</option>
                            <option value="year">This year</option>
                        </select>

                        <button onclick="printBorrowed()"
                            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="toggleBorrowedModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition ml-auto">
                            Close
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-left">Id</th>
                                    <th class="border px-4 py-2 text-left">Item ID</th>
                                    <th class="border px-4 py-2 text-left">Item Name</th>
                                    <th class="border px-4 py-2 text-left">Brands</th>
                                    <th class="border px-4 py-2 text-left">Quantity</th>
                                    <th class="border px-4 py-2 text-left">Borrow Date</th>
                                    <th class="border px-4 py-2 text-left">Return Date</th>
                                    <th class="border px-4 py-2 text-left">Fullname</th>
                                    <th class="border px-4 py-2 text-left">Contact Number</th>
                                    <th class="border px-4 py-2 text-left">Email</th>
                                </tr>
                            </thead>
                            <tbody id="borrowedTable">
                                <?php if (empty($borrowedItems)): ?>
                                    <tr>
                                        <td colspan="10" class="border px-4 py-2 text-center">No borrowed items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($borrowedItems as $item): ?>
                                        <tr class="borrowed-row hover:bg-gray-50"
                                            data-borrowed-at="<?= htmlspecialchars($item['borrowed_at']); ?>">
                                            <td class="border px-4 py-2"><?= $item['transaction_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_name']; ?></td>
                                            <td class="border px-4 py-2"><?= htmlspecialchars($item['brand_name']); ?></td>
                                            <td class="border px-4 py-2">
                                                <span class="hover-tooltip">
                                                    <?= $item['quantity_borrowed']; ?>
                                                    <div class="tooltip"><?= htmlspecialchars($item['unique_ids']); ?></div>
                                                </span>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['borrowed_at']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['return_date']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['first_name'] . ' ' . $item['last_name']; ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['contact_no']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['email']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Returned Items -->
    <div id="returnedModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto modal-fade">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden modal-slide transform translate-y-4 opacity-0">
                <div class="bg-green-600 text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Returned Items</h2>
                    <button onclick="toggleReturnedModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex flex-wrap items-center gap-4">
                        <select id="filterDropdown2" onchange="filterByDate2()"
                            class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Filter</option>
                            <option value="all">All</option>
                            <option value="month">This month</option>
                            <option value="year">This year</option>
                        </select>

                        <button onclick="printReturned()"
                            class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="toggleReturnedModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition ml-auto">
                            Close
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-left">Id</th>
                                    <th class="border px-4 py-2 text-left">Item ID</th>
                                    <th class="border px-4 py-2 text-left">Item Name</th>
                                    <th class="border px-4 py-2 text-left">Brands</th>
                                    <th class="border px-4 py-2 text-left">Quantity</th>
                                    <th class="border px-4 py-2 text-left">Date Returned</th>
                                    <th class="border px-4 py-2 text-left">Fullname</th>
                                    <th class="border px-4 py-2 text-left">Contact Number</th>
                                    <th class="border px-4 py-2 text-left">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($returnedItems)): ?>
                                    <tr>
                                        <td colspan="9" class="border px-4 py-2 text-center">No returned items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($returnedItems as $item): ?>
                                        <tr class="returned-row hover:bg-gray-50"
                                            data-returned_at="<?= htmlspecialchars($item['returned_at']); ?>">
                                            <td class="border px-4 py-2"><?= $item['transaction_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_name']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['brand']; ?></td>
                                            <td class="border px-4 py-2">
                                                <span class="hover-tooltip">
                                                    <?= $item['quantity_returned']; ?>
                                                    <div class="tooltip"><?= htmlspecialchars($item['unique_ids']); ?></div>
                                                </span>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['returned_at']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['first_name'] . ' ' . $item['last_name']; ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['contact_no']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['email']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Available Items -->
    <div id="availableModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto modal-fade">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden modal-slide transform translate-y-4 opacity-0">
                <div class="bg-primary text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Available Items</h2>
                    <button onclick="toggleAvailableModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex justify-end gap-4">
                        <button onclick="printAvailable()"
                            class="bg-primary hover:bg-primary-dark text-white py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="toggleAvailableModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition">
                            Close
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-left">ID</th>
                                    <th class="border px-4 py-2 text-left">Item Name</th>
                                    <th class="border px-4 py-2 text-left">Brand</th>
                                    <th class="border px-4 py-2 text-left">Quantity</th>
                                    <th class="border px-4 py-2 text-left">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($availableItems)): ?>
                                    <tr>
                                        <td colspan="5" class="border px-4 py-2 text-center">No available items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($availableItems as $item): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="border px-4 py-2"><?= $item['id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['name']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['brand']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['quantity']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['description']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Lost Items -->
    <div id="lostModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto modal-fade">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden modal-slide transform translate-y-4 opacity-0">
                <div class="bg-red-600 text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Lost Items</h2>
                    <button onclick="toggleLostModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex flex-wrap items-center gap-4">
                        <select id="filterDropdown3" onchange="filterByDate3()"
                            class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Filter</option>
                            <option value="all">All</option>
                            <option value="month">This month</option>
                            <option value="year">This year</option>
                        </select>

                        <button onclick="printLost()"
                            class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="toggleLostModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition ml-auto">
                            Close
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-left">Id</th>
                                    <th class="border px-4 py-2 text-left">Item ID</th>
                                    <th class="border px-4 py-2 text-left">Unique ID</th>
                                    <th class="border px-4 py-2 text-left">Item Name</th>
                                    <th class="border px-4 py-2 text-left">Brands</th>
                                    <th class="border px-4 py-2 text-left">Date Lost</th>
                                    <th class="border px-4 py-2 text-left">Fullname</th>
                                    <th class="border px-4 py-2 text-left">Contact Number</th>
                                    <th class="border px-4 py-2 text-left">Email</th>
                                    <th class="border px-4 py-2 text-left">Remark</th>
                                    <th class="border px-4 py-2 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lostItems)): ?>
                                    <tr>
                                        <td colspan="11" class="border px-4 py-2 text-center">No lost items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lostItems as $item): ?>
                                        <tr class="lost-row hover:bg-gray-50"
                                            data-lost_at="<?= htmlspecialchars($item['returned_at']); ?>">
                                            <td class="border px-4 py-2"><?= $item['return_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['unique_id_remark']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_name']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['brand']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['returned_at']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['first_name'] . ' ' . $item['last_name']; ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['contact_no']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['email']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['remarks']; ?></td>
                                            <td class="border px-4 py-2">
                                                <button
                                                    class="bg-primary hover:bg-primary-dark text-white py-1 px-3 rounded text-sm"
                                                    onclick="updateStatus('<?= $item['return_id']; ?>')">
                                                    Update
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Damaged Items -->
    <div id="damagedModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto modal-fade">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden modal-slide transform translate-y-4 opacity-0">
                <div class="bg-yellow-500 text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Damaged Items</h2>
                    <button onclick="toggleDamagedModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex flex-wrap items-center gap-4">
                        <select id="filterDropdown4" onchange="filterByDate4()"
                            class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <option value="">Filter</option>
                            <option value="all">All</option>
                            <option value="month">This month</option>
                            <option value="year">This year</option>
                        </select>

                        <button onclick="printDamaged()"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="toggleDamagedModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition ml-auto">
                            Close
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-left">Id</th>
                                    <th class="border px-4 py-2 text-left">Item ID</th>
                                    <th class="border px-4 py-2 text-left">Unique ID</th>
                                    <th class="border px-4 py-2 text-left">Item Name</th>
                                    <th class="border px-4 py-2 text-left">Brands</th>
                                    <th class="border px-4 py-2 text-left">Date Returned</th>
                                    <th class="border px-4 py-2 text-left">Fullname</th>
                                    <th class="border px-4 py-2 text-left">Contact Number</th>
                                    <th class="border px-4 py-2 text-left">Email</th>
                                    <th class="border px-4 py-2 text-left">Remark</th>
                                    <th class="border px-4 py-2 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($damagedItems)): ?>
                                    <tr>
                                        <td colspan="11" class="border px-4 py-2 text-center">No damaged items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($damagedItems as $item): ?>
                                        <tr class="damaged-row hover:bg-gray-50"
                                            data-damaged_at="<?= htmlspecialchars($item['returned_at']); ?>">
                                            <td class="border px-4 py-2"><?= $item['return_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['unique_id_remark']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_name']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['brand']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['returned_at']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['first_name'] . ' ' . $item['last_name']; ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['contact_no']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['email']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['remarks']; ?></td>
                                            <td class="border px-4 py-2">
                                                <button
                                                    class="bg-primary hover:bg-primary-dark text-white py-1 px-3 rounded text-sm"
                                                    onclick="updateStatusDamaged('<?= $item['return_id']; ?>')">
                                                    Update
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Modal: Replaced Items -->
    <div id="replacedModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto modal-fade">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden modal-slide transform translate-y-4 opacity-0">
                <div class="bg-purple-600 text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Replaced Items</h2>
                    <button onclick="toggleReplacedModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex flex-wrap items-center gap-4">
                        <select id="filterDropdown5" onchange="filterByDate5()"
                            class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Filter</option>
                            <option value="all">All</option>
                            <option value="month">This month</option>
                            <option value="year">This year</option>
                        </select>

                        <button onclick="printReplaced()"
                            class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="toggleReplacedModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition ml-auto">
                            Close
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-left">Id</th>
                                    <th class="border px-4 py-2 text-left">Item ID</th>
                                    <th class="border px-4 py-2 text-left">Unique ID</th>
                                    <th class="border px-4 py-2 text-left">Item Name</th>
                                    <th class="border px-4 py-2 text-left">Brand</th>
                                    <th class="border px-4 py-2 text-left">Date Replaced</th>
                                    <th class="border px-4 py-2 text-left">Fullname</th>
                                    <th class="border px-4 py-2 text-left">Contact Number</th>
                                    <th class="border px-4 py-2 text-left">Email</th>
                                    <th class="border px-4 py-2 text-left">Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($replacedItems)): ?>
                                    <tr>
                                        <td colspan="10" class="border px-4 py-2 text-center">No replaced items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($replacedItems as $item): ?>
                                        <tr class="replaced-row hover:bg-gray-50"
                                            data-replaced_at="<?= htmlspecialchars($item['returned_at']); ?>">
                                            <td class="border px-4 py-2"><?= $item['return_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['unique_id_remark']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_name']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['brand']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['returned_at']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['first_name'] . ' ' . $item['last_name']; ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['contact_no']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['email']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['remarks']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Recently Added Items -->
    <div id="recentlyAddedModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto modal-fade">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden modal-slide transform translate-y-4 opacity-0">
                <div class="bg-cyan-600 text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Recently Added Items</h2>
                    <button onclick="toggleRecentlyAddedModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex justify-end gap-4">
                        <button onclick="printRecentlyAdded()"
                            class="bg-cyan-600 hover:bg-cyan-700 text-white py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="toggleRecentlyAddedModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition">
                            Close
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-left">Id</th>
                                    <th class="border px-4 py-2 text-left">Item Name</th>
                                    <th class="border px-4 py-2 text-left">Image</th>
                                    <th class="border px-4 py-2 text-left">Description</th>
                                    <th class="border px-4 py-2 text-left">Brands & Quantities</th>
                                    <th class="border px-4 py-2 text-left">Date Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentlyAddedItems)): ?>
                                    <tr>
                                        <td colspan="7" class="border px-4 py-2 text-center">No recently added items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentlyAddedItems as $item): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="border px-4 py-2"><?= $item['id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['name']; ?></td>
                                            <td class="border px-4 py-2">
                                                <img class="h-16 w-16 object-cover rounded"
                                                    src="<?= !empty($item['image']) ? '../assets/uploads/' . $item['image'] : '../assets/img/CSSPE.png'; ?>"
                                                    alt="<?= htmlspecialchars($item['name']); ?>">
                                            </td>
                                            <td class="border px-4 py-2"><?= htmlspecialchars($item['description']); ?></td>
                                            <td class="border px-4 py-2">
                                                <?= !empty($item['brand_details']) ? $item['brand_details'] : 'N/A'; ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['created_at']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Overdue Items -->
    <div id="overdueModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto modal-fade">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden modal-slide transform translate-y-4 opacity-0">
                <div class="bg-red-500 text-white p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Overdue Items</h2>
                    <button onclick="toggleOverdueModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex flex-wrap items-center gap-4">
                        <select id="filterDropdown6" onchange="filterByDate6()"
                            class="bg-white border border-gray-300 text-gray-700 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Filter</option>
                            <option value="all">All</option>
                            <option value="month">This month</option>
                            <option value="year">This year</option>
                        </select>

                        <button onclick="printOverdue()"
                            class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>

                        <button onclick="toggleOverdueModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition ml-auto">
                            Close
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-4 py-2 text-left">Id</th>
                                    <th class="border px-4 py-2 text-left">Item ID</th>
                                    <th class="border px-4 py-2 text-left">Item Name</th>
                                    <th class="border px-4 py-2 text-left">Brand</th>
                                    <th class="border px-4 py-2 text-left">Quantity</th>
                                    <th class="border px-4 py-2 text-left">Return Date</th>
                                    <th class="border px-4 py-2 text-left">Fullname</th>
                                    <th class="border px-4 py-2 text-left">Contact Number</th>
                                    <th class="border px-4 py-2 text-left">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($overdueItems)): ?>
                                    <tr>
                                        <td colspan="9" class="border px-4 py-2 text-center">No overdue items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($overdueItems as $item): ?>
                                        <tr class="overdue-row hover:bg-gray-50"
                                            data-overdue_at="<?= htmlspecialchars($item['return_date']); ?>">
                                            <td class="border px-4 py-2"><?= $item['transaction_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_id']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['item_name']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['brand']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['overdue_quantity']; ?></td>
                                            <td class="border px-4 py-2 text-red-600 font-semibold"><?= $item['return_date']; ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['first_name'] . ' ' . $item['last_name']; ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $item['contact_no']; ?></td>
                                            <td class="border px-4 py-2"><?= $item['email']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle sidebar on mobile
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.querySelector('.main-content');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });

        // Check screen size on resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });

        // Search functionality
        document.getElementById('searchBar').addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tableBody tr');

            rows.forEach(row => {
                const itemName = row.querySelector('td:first-child')?.textContent.toLowerCase();
                if (itemName && itemName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Modal toggle functions
        function toggleBorrowedModal() {
            toggleModal('borrowedModal');
        }

        function toggleReturnedModal() {
            toggleModal('returnedModal');
        }

        function toggleAvailableModal() {
            toggleModal('availableModal');
        }

        function toggleLostModal() {
            toggleModal('lostModal');
        }

        function toggleDamagedModal() {
            toggleModal('damagedModal');
        }

        function toggleReplacedModal() {
            toggleModal('replacedModal');
        }

        function toggleRecentlyAddedModal() {
            toggleModal('recentlyAddedModal');
        }

        function toggleOverdueModal() {
            toggleModal('overdueModal');
        }

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            const modalContent = modal.querySelector('.modal-slide');

            if (modal.classList.contains('hidden')) {
                // Show modal
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.add('opacity-100');
                    modalContent.classList.remove('translate-y-4', 'opacity-0');
                }, 10);
            } else {
                // Hide modal
                modal.classList.remove('opacity-100');
                modalContent.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }

        // Filter functions
        function filterByDate() {
            let filterValue = document.getElementById('filterDropdown').value;
            let borrowedRows = document.querySelectorAll('.borrowed-row');
            let currentDate = new Date();

            borrowedRows.forEach(row => {
                let borrowedAt = new Date(row.getAttribute('data-borrowed-at'));
                let show = false;

                if (filterValue === "all") {
                    show = true;
                } else if (filterValue === "month") {
                    show = borrowedAt.getMonth() === currentDate.getMonth() && borrowedAt.getFullYear() === currentDate.getFullYear();
                } else if (filterValue === "year") {
                    show = borrowedAt.getFullYear() === currentDate.getFullYear();
                }

                row.style.display = show ? "table-row" : "none";
            });
        }

        function filterByDate2() {
            let filterValue = document.getElementById('filterDropdown2').value;
            let returnedRows = document.querySelectorAll('.returned-row');
            let currentDate = new Date();

            returnedRows.forEach(row => {
                let returnedAt = new Date(row.getAttribute('data-returned_at'));
                let show = false;

                if (filterValue === "all") {
                    show = true;
                } else if (filterValue === "month") {
                    show = returnedAt.getMonth() === currentDate.getMonth() && returnedAt.getFullYear() === currentDate.getFullYear();
                } else if (filterValue === "year") {
                    show = returnedAt.getFullYear() === currentDate.getFullYear();
                }

                row.style.display = show ? "table-row" : "none";
            });
        }

        function filterByDate3() {
            let filterValue = document.getElementById('filterDropdown3').value;
            let lostRows = document.querySelectorAll('.lost-row');
            let currentDate = new Date();

            lostRows.forEach(row => {
                let lostAt = new Date(row.getAttribute('data-lost_at'));
                let show = false;

                if (filterValue === "all") {
                    show = true;
                } else if (filterValue === "month") {
                    show = lostAt.getMonth() === currentDate.getMonth() && lostAt.getFullYear() === currentDate.getFullYear();
                } else if (filterValue === "year") {
                    show = lostAt.getFullYear() === currentDate.getFullYear();
                }

                row.style.display = show ? "table-row" : "none";
            });
        }

        function filterByDate4() {
            let filterValue = document.getElementById('filterDropdown4').value;
            let damagedRows = document.querySelectorAll('.damaged-row');
            let currentDate = new Date();

            damagedRows.forEach(row => {
                let damagedAt = new Date(row.getAttribute('data-damaged_at'));
                let show = false;

                if (filterValue === "all") {
                    show = true;
                } else if (filterValue === "month") {
                    show = damagedAt.getMonth() === currentDate.getMonth() && damagedAt.getFullYear() === currentDate.getFullYear();
                } else if (filterValue === "year") {
                    show = damagedAt.getFullYear() === currentDate.getFullYear();
                }

                row.style.display = show ? "table-row" : "none";
            });
        }

        function filterByDate5() {
            let filterValue = document.getElementById('filterDropdown5').value;
            let replacedRows = document.querySelectorAll('.replaced-row');
            let currentDate = new Date();

            replacedRows.forEach(row => {
                let replacedAt = new Date(row.getAttribute('data-replaced_at'));
                let show = false;

                if (filterValue === "all") {
                    show = true;
                } else if (filterValue === "month") {
                    show = replacedAt.getMonth() === currentDate.getMonth() && replacedAt.getFullYear() === currentDate.getFullYear();
                } else if (filterValue === "year") {
                    show = replacedAt.getFullYear() === currentDate.getFullYear();
                }

                row.style.display = show ? "table-row" : "none";
            });
        }

        function filterByDate6() {
            let filterValue = document.getElementById('filterDropdown6').value;
            let overdueRows = document.querySelectorAll('.overdue-row');
            let currentDate = new Date();

            overdueRows.forEach(row => {
                let overdueAt = new Date(row.getAttribute('data-overdue_at'));
                let show = false;

                if (filterValue === "all") {
                    show = true;
                } else if (filterValue === "month") {
                    show = overdueAt.getMonth() === currentDate.getMonth() && overdueAt.getFullYear() === currentDate.getFullYear();
                } else if (filterValue === "year") {
                    show = overdueAt.getFullYear() === currentDate.getFullYear();
                }

                row.style.display = show ? "table-row" : "none";
            });
        }

        // Update status functions
        function updateStatus(returnId) {
            Swal.fire({
                title: 'Replace Item',
                text: 'Please provide remarks',
                input: 'text',
                inputValue: 'Lost but now Replaced',
                inputAttributes: {
                    autocapitalize: 'off',
                },
                showCancelButton: true,
                confirmButtonText: 'Submit',
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                showLoaderOnConfirm: true,
                preConfirm: (newRemarks) => {
                    if (!newRemarks || newRemarks.trim() === "") {
                        Swal.showValidationMessage('Remarks cannot be empty.');
                        return false;
                    }

                    return Swal.fire({
                        title: 'Confirm Update',
                        text: 'Are you sure you want to update the status to "Replaced"?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, update it!',
                        cancelButtonText: 'No, cancel',
                        confirmButtonColor: '#6B0D0D',
                        cancelButtonColor: '#6c757d',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Send the AJAX request
                            return fetch('./endpoints/update_returned_item.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    return_id: returnId,
                                    new_remarks: newRemarks,
                                    new_status: 'Replaced',
                                }),
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Updated!',
                                            text: data.message,
                                            confirmButtonColor: '#6B0D0D'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error!',
                                            text: data.message,
                                            confirmButtonColor: '#6B0D0D'
                                        });
                                    }
                                })
                                .catch((error) => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: 'An error occurred while updating the status.',
                                        confirmButtonColor: '#6B0D0D'
                                    });
                                });
                        }
                    });
                },
            });
        }

        function updateStatusDamaged(returnId) {
            Swal.fire({
                title: 'Replace Item',
                text: 'Please provide remarks',
                input: 'text',
                inputValue: 'Damaged but now Replaced',
                inputAttributes: {
                    autocapitalize: 'off',
                },
                showCancelButton: true,
                confirmButtonText: 'Submit',
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                showLoaderOnConfirm: true,
                preConfirm: (newRemarks) => {
                    if (!newRemarks || newRemarks.trim() === "") {
                        Swal.showValidationMessage('Remarks cannot be empty.');
                        return false;
                    }

                    return Swal.fire({
                        title: 'Confirm Update',
                        text: 'Are you sure you want to update the status to "Replaced"?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, update it!',
                        cancelButtonText: 'No, cancel',
                        confirmButtonColor: '#6B0D0D',
                        cancelButtonColor: '#6c757d',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Send the AJAX request
                            return fetch('./endpoints/update_returned_item.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    return_id: returnId,
                                    new_remarks: newRemarks,
                                    new_status: 'Replaced',
                                }),
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    if (data.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Updated!',
                                            text: data.message,
                                            confirmButtonColor: '#6B0D0D'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error!',
                                            text: data.message,
                                            confirmButtonColor: '#6B0D0D'
                                        });
                                    }
                                })
                                .catch((error) => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: 'An error occurred while updating the status.',
                                        confirmButtonColor: '#6B0D0D'
                                    });
                                });
                        }
                    });
                },
            });
        }

        // Print functions
        function printBorrowed() {
            const table = document.querySelector('#borrowedModal table');
            printTable(table, 'Borrowed Items');
        }

        function printReturned() {
            const table = document.querySelector('#returnedModal table');
            printTable(table, 'Returned Items');
        }

        function printAvailable() {
            const table = document.querySelector('#availableModal table');
            printTable(table, 'Available Items');
        }

        function printLost() {
            const table = document.querySelector('#lostModal table');
            printTable(table, 'Lost Items');
        }

        function printDamaged() {
            const table = document.querySelector('#damagedModal table');
            printTable(table, 'Damaged Items');
        }

        function printReplaced() {
            const table = document.querySelector('#replacedModal table');
            printTable(table, 'Replaced Items');
        }

        function printRecentlyAdded() {
            const table = document.querySelector('#recentlyAddedModal table');
            printTable(table, 'Recently Added Items');
        }

        function printOverdue() {
            const table = document.querySelector('#overdueModal table');
            printTable(table, 'Overdue Items');
        }

        function printTable(table, title) {
            // Clone the table to avoid modifying the original
            const tableClone = table.cloneNode(true);

            // Remove action column if it exists
            const headerRow = tableClone.querySelector('thead tr');
            if (headerRow) {
                const actionColumnIndex = Array.from(headerRow.children)
                    .findIndex(th => th.textContent.trim() === 'Action');

                if (actionColumnIndex !== -1) {
                    // Remove action column header
                    headerRow.children[actionColumnIndex].remove();

                    // Remove action column from each row
                    tableClone.querySelectorAll('tbody tr').forEach(row => {
                        if (row.children[actionColumnIndex]) {
                            row.children[actionColumnIndex].remove();
                        }
                    });
                }
            }

            // Get admin name, if available (with fallback)
            let adminName = "Inventory Admin";
            try {
                const nameElement = document.querySelector('.sidebar-header span');
                if (nameElement && nameElement.textContent) {
                    adminName = nameElement.textContent.trim();
                }
            } catch (error) {
                console.log("Couldn't get admin name, using default");
            }

            // Create print window
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title}</title>
            <style>
                body {
                    font-family: 'Segoe UI', Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                }
                .header {
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                }
                .header img {
                    height: 50px;
                    margin-right: 15px;
                }
                h1 {
                    color: #6B0D0D;
                    font-size: 24px;
                    margin: 0;
                }
                .date-time {
                    font-size: 14px;
                    color: #666;
                    margin: 10px 0 20px;
                    text-align: right;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .footer {
                    margin-top: 30px;
                    text-align: right;
                    font-size: 12px;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="../assets/img/CSSPE.png" alt="Logo">
                <div>
                    <h1>CSSPE Inventory & Information System</h1>
                    <p>College of Sport Science and Physical Education</p>
                </div>
            </div>
            
            <h2>${title}</h2>
            
            <div class="date-time">
                Printed on: ${new Date().toLocaleString()}
            </div>
            
            ${tableClone.outerHTML}
            
            <div class="footer">
                <p>Generated by: ${adminName}</p>
            </div>
        </body>
        </html>
    `);

            printWindow.document.close();
            printWindow.focus();

            // Wait for content to load before printing
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 1000);
        }
    </script>
</body>

</html>