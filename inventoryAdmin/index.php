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
$itemQuery = "SELECT name, image, brand, quantity FROM items ORDER BY created_at DESC LIMIT 10";
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
        SUM(quantity_borrowed) AS total_borrowed, 
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
        i.brand, 
        t.quantity_borrowed, 
        t.return_date, 
        u.first_name, 
        u.last_name, 
        u.contact_no, 
        u.email
    FROM item_transactions t
    JOIN items i ON t.item_id = i.id
    JOIN users u ON t.users_id = u.id
    WHERE t.status IN ('Pending', 'Approved')
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
        i.brand, 
        t.quantity_returned, 
        t.returned_at, 
        u.first_name, 
        u.last_name, 
        u.contact_no, 
        u.email
    FROM item_transactions t
    JOIN items i ON t.item_id = i.id
    JOIN users u ON t.users_id = u.id
    WHERE t.status = 'Returned'
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
        id, 
        name, 
        image, 
        description, 
        brand, 
        quantity, 
        created_at 
    FROM items
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY created_at DESC
";
$recentlyAddedStmt = $conn->prepare($recentlyAddedQuery);
$recentlyAddedStmt->execute();
$recentlyAddedResult = $recentlyAddedStmt->get_result();
$recentlyAddedItems = $recentlyAddedResult->fetch_all(MYSQLI_ASSOC);

// Fetch Lost Items
$lostQuery = "
    SELECT 
        r.return_id,
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
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
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
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
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
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
    <title>Document</title>

    <link rel="stylesheet" href="../assets/css/organization.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>
    <div class="body" style="margin-bottom: 3rem;">
        <div class="sidebar">
            <div class="sidebarContent">
                <div class="arrowContainer" style="margin-left: 80rem;" id="toggleButton">
                    <div class="subArrowContainer">
                        <img class="hideIcon" src="../assets/img/arrow.png" alt="">
                    </div>
                </div>
            </div>
            <div class="userContainer">
                <div class="subUserContainer">
                    <div class="userPictureContainer">
                        <div class="subUserPictureContainer">
                            <img class="subUserPictureContainer"
                                src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>"
                                alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p><?php echo $fullName; ?></php>
                        </p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">

                        <a href="../homePage/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Home</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/inventory.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Inventories</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/borrowing.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Borrow request</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/borrowItem.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Borrowed Item</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/notification.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Notification</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="subUserContainer">
                    <a href="../logout.php">
                        <div style="margin-left: 1.5rem;" class="userPictureContainer1">
                            <p>Logout</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="mainContainer" style="margin-left: 250px;">
            <div class="container">
                <div class="headerContainer">
                    <div class="subHeaderContainer">
                        <div class="logoContainer">
                            <img class="logo" src="../assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="collegeNameContainer">
                            <p>CSSPE Inventory & Information System</p>
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">Dashboard</p>
                </div>

                <div class="dashboardContainer">
                    <div class="statusContainer">
                        <div onclick="borrowed()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Borrowed</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $totalBorrowed; ?></p>
                            </div>
                        </div>

                        <div onclick="return1()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Returned</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $totalReturned; ?></p>
                            </div>
                        </div>

                        <div onclick="available()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Available</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $totalAvailable; ?></p>
                            </div>
                        </div>

                        <div onclick="lost()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Lost</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $totalLost; ?></p>
                            </div>
                        </div>

                        <div onclick="damage()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Damaged</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $totalDamaged; ?></p>
                            </div>
                        </div>

                        <div onclick="replace1()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Replaced Item</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $totalReplaced; ?></p>
                            </div>
                        </div>

                        <div onclick="added()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Recently added</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $totalRecentlyAdded; ?></p>
                            </div>
                        </div>

                        <div onclick="overdue()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Overdue</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $totalOverdue; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="searchContainer" style="margin-top: 2rem;">
                    <input class="searchBar" id="searchBar" type="text" placeholder="Search...">
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Image</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td>
                                            <img class="image"
                                                src="<?php echo !empty($item['image']) ? '../assets/uploads/' . htmlspecialchars($item['image']) : '../assets/img/CSSPE.png'; ?>"
                                                alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($item['brand']); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No items found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>


    <div class="summaryContainer borrowed" style="display: none; background-color: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">

                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Borrowed</p>
                </div>

                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brands</th>
                                <th>Quantity</th>
                                <th>Expected Return Date</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($borrowedItems)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No borrowed items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($borrowedItems as $item): ?>
                                    <tr>
                                        <td><?= $item['transaction_id']; ?></td>
                                        <td><?= $item['item_name']; ?></td>
                                        <td><?= $item['brand']; ?></td>
                                        <td><?= $item['quantity_borrowed']; ?></td>
                                        <td><?= $item['return_date']; ?></td>
                                        <td><?= $item['first_name'] . ' ' . $item['last_name']; ?></td>
                                        <td><?= $item['contact_no']; ?></td>
                                        <td><?= $item['email']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="buttonContainer">
                    <button class="addButton" onclick="printBorrowed()">Print</button>
                    <button onclick="borrowed()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="summaryContainer return" style="display: none; background-color: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">

                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Returned</p>
                </div>

                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brands</th>
                                <th>Quantity</th>
                                <th>Date Returned</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($returnedItems)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No returned items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($returnedItems as $item): ?>
                                    <tr>
                                        <td><?= $item['transaction_id']; ?></td>
                                        <td><?= $item['item_name']; ?></td>
                                        <td><?= $item['brand']; ?></td>
                                        <td><?= $item['quantity_returned']; ?></td>
                                        <td><?= $item['returned_at']; ?></td>
                                        <td><?= $item['first_name'] . ' ' . $item['last_name']; ?></td>
                                        <td><?= $item['contact_no']; ?></td>
                                        <td><?= $item['email']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="buttonContainer">
                    <button class="addButton">Print</button>
                    <button onclick="return1()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="summaryContainer available" style="display: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">
                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Available</p>
                </div>

                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($availableItems)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No available items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($availableItems as $item): ?>
                                    <tr>
                                        <td><?= $item['id']; ?></td>
                                        <td><?= $item['name']; ?></td>
                                        <td><?= $item['brand']; ?></td>
                                        <td><?= $item['quantity']; ?></td>
                                        <td><?= $item['description']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="buttonContainer">
                    <button class="addButton">Print</button>
                    <button onclick="available()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="summaryContainer lost" style="display: none; background-color: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">
                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Lost</p>
                </div>
                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brands</th>
                                <th>Quantity</th>
                                <th>Date Lost</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lostItems)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No lost items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lostItems as $item): ?>
                                    <tr>
                                        <td><?= $item['return_id']; ?></td>
                                        <td><?= $item['item_name']; ?></td>
                                        <td><?= $item['brand']; ?></td>
                                        <td><?= $item['quantity_returned']; ?></td>
                                        <td><?= $item['returned_at']; ?></td>
                                        <td><?= $item['first_name'] . ' ' . $item['last_name']; ?></td>
                                        <td><?= $item['contact_no']; ?></td>
                                        <td><?= $item['email']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="buttonContainer">
                    <button class="addButton">Print</button>
                    <button onclick="lost()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>


    <div class="summaryContainer damage" style="display: none; background-color: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">

                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Damaged</p>
                </div>

                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brands</th>
                                <th>Quantity</th>
                                <th>Date Returned</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($damagedItems)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No damaged items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($damagedItems as $item): ?>
                                    <tr>
                                        <td><?= $item['return_id']; ?></td>
                                        <td><?= $item['item_name']; ?></td>
                                        <td><?= $item['brand']; ?></td>
                                        <td><?= $item['quantity_returned']; ?></td>
                                        <td><?= $item['returned_at']; ?></td>
                                        <td><?= $item['first_name'] . ' ' . $item['last_name']; ?></td>
                                        <td><?= $item['contact_no']; ?></td>
                                        <td><?= $item['email']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <div class="buttonContainer">
                    <button class="addButton">Print</button>
                    <button onclick="damage()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="summaryContainer replace" style="display: none; background-color: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">

                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Replaced Item</p>
                </div>

                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Date Replace</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($replacedItems)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No replaced items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($replacedItems as $item): ?>
                                    <tr>
                                        <td><?= $item['return_id']; ?></td>
                                        <td><?= $item['item_name']; ?></td>
                                        <td><?= $item['brand']; ?></td>
                                        <td><?= $item['quantity_returned']; ?></td>
                                        <td><?= $item['returned_at']; ?></td>
                                        <td><?= $item['first_name'] . ' ' . $item['last_name']; ?></td>
                                        <td><?= $item['contact_no']; ?></td>
                                        <td><?= $item['email']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <div class="buttonContainer">
                    <button class="addButton">Print</button>
                    <button onclick="replace1()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="summaryContainer added" style="display: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">
                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Recently Added</p>
                </div>

                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentlyAddedItems)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No recently added items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentlyAddedItems as $item): ?>
                                    <tr>
                                        <td><?= $item['id']; ?></td>
                                        <td><?= $item['name']; ?></td>
                                        <td>
                                            <img class="image"
                                                src="<?= !empty($item['image']) ? '../assets/uploads/' . $item['image'] : '../assets/img/CSSPE.png'; ?>"
                                                alt="<?= $item['name']; ?>" width="50">
                                        </td>
                                        </td>
                                        <td><?= $item['description']; ?></td>
                                        <td><?= $item['brand']; ?></td>
                                        <td><?= $item['quantity']; ?></td>
                                        <td><?= $item['created_at']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="buttonContainer">
                    <button class="addButton">Print</button>
                    <button onclick="added()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="summaryContainer overdue" style="display: none; background-color: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">
                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Overdue</p>
                </div>
                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Return Date</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($overdueItems)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No overdue items.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($overdueItems as $item): ?>
                                    <tr>
                                        <td><?= $item['transaction_id']; ?></td>
                                        <td><?= $item['item_name']; ?></td>
                                        <td><?= $item['brand']; ?></td>
                                        <td><?= $item['overdue_quantity']; ?></td>
                                        <td><?= $item['return_date']; ?></td>
                                        <td><?= $item['first_name'] . ' ' . $item['last_name']; ?></td>
                                        <td><?= $item['contact_no']; ?></td>
                                        <td><?= $item['email']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="buttonContainer">
                    <button class="addButton">Print</button>
                    <button onclick="overdue()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        function printBorrowed() {
            const container = document.querySelector('.borrowed');
            if (!container) return console.error("Borrowed container not found.");

            const tableContainer = container.querySelector('.tableContainer');
            const tableHeader = container.querySelector('.textContainer');

            // Get the HTML for printing
            const printContent = tableContainer.outerHTML;
            const printHeader = tableHeader.outerHTML;
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Print Borrowed Items</title>
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
            .table-header {
                text-align: center;
                font-size: 24px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        ${printHeader}
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();
        }

        function printReturned() {
            const container = document.querySelector('.return');
            if (!container) return console.error("Returned container not found.");

            const tableContainer = container.querySelector('.tableContainer');
            const tableHeader = container.querySelector('.textContainer');

            // Get the HTML for printing
            const printContent = tableContainer.outerHTML;
            const printHeader = tableHeader.outerHTML;
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Print Returned Items</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #555;
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
                background-color: #d4d4d4;
                font-weight: bold;
            }
            tr:nth-child(odd) {
                background-color: #e9e9e9;
            }
            .table-header {
                text-align: center;
                font-size: 24px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        ${printHeader}
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();
        }

        function printAvailable() {
            const container = document.querySelector('.available');
            if (!container) return console.error("Available container not found.");

            const tableContainer = container.querySelector('.tableContainer');
            const tableHeader = container.querySelector('.textContainer');

            // Get the HTML for printing
            const printContent = tableContainer.outerHTML;
            const printHeader = tableHeader.outerHTML;
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Print Available Items</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #444;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid black;
                padding: 8px;
                text-align: center; /* Center alignment for available items */
            }
            th {
                background-color: #ccc;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            .table-header {
                text-align: center;
                font-size: 24px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        ${printHeader}
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();
        }


        function printLost() {
            const container = document.querySelector('.summaryContainer.lost');
            if (!container) return console.error("Lost container not found.");

            const tableContainer = container.querySelector('.tableContainer');
            const tableHeader = container.querySelector('.textContainer');

            const printContent = tableContainer.outerHTML;
            const printHeader = tableHeader.outerHTML;

            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Print Lost Items</title>
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
            .table-header {
                text-align: center;
                font-size: 24px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        ${printHeader}
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();
        }

        function printDamaged() {
            const container = document.querySelector('.summaryContainer.damage');
            if (!container) return console.error("Damaged container not found.");

            const tableContainer = container.querySelector('.tableContainer');
            const tableHeader = container.querySelector('.textContainer');

            const printContent = tableContainer.outerHTML;
            const printHeader = tableHeader.outerHTML;

            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Print Damaged Items</title>
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
                background-color: #f0e68c;
                font-weight: bold;
            }
            tr:nth-child(odd) {
                background-color: #f9f9f9;
            }
            .table-header {
                text-align: center;
                font-size: 24px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        ${printHeader}
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();
        }

        function printReplaced() {
            const container = document.querySelector('.summaryContainer.replace');
            if (!container) return console.error("Replaced container not found.");

            const tableContainer = container.querySelector('.tableContainer');
            const tableHeader = container.querySelector('.textContainer');

            const printContent = tableContainer.outerHTML;
            const printHeader = tableHeader.outerHTML;

            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Print Replaced Items</title>
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
                text-align: center;
            }
            th {
                background-color: #add8e6;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .table-header {
                text-align: center;
                font-size: 24px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        ${printHeader}
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();
        }

        function printRecentlyAdded() {
            const container = document.querySelector('.summaryContainer.added');
            if (!container) return console.error("Recently Added container not found.");

            const tableContainer = container.querySelector('.tableContainer');
            const tableHeader = container.querySelector('.textContainer');

            const printContent = tableContainer.outerHTML;
            const printHeader = tableHeader.outerHTML;

            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
<html>
<head>
    <title>Print Recently Added Items</title>
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
            text-align: center;
        }
        th {
            background-color: #c8e6c9;
            font-weight: bold;
        }
        tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .table-header {
            text-align: center;
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    ${printHeader}
    ${printContent}
</body>
</html>
`);
            printWindow.document.close();
            printWindow.print();
        }

        function printOverdue() {
            const container = document.querySelector('.summaryContainer.overdue');
            if (!container) return console.error("Overdue container not found.");

            const tableContainer = container.querySelector('.tableContainer');
            const tableHeader = container.querySelector('.textContainer');

            const printContent = tableContainer.outerHTML;
            const printHeader = tableHeader.outerHTML;

            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
<html>
<head>
    <title>Print Overdue Items</title>
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
            text-align: center;
        }
        th {
            background-color: #ffcccb;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .table-header {
            text-align: center;
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    ${printHeader}
    ${printContent}
</body>
</html>
`);
            printWindow.document.close();
            printWindow.print();
        }


        // Add event listeners to the print buttons
        document.querySelector('.borrowed .addButton').addEventListener('click', printBorrowed);
        document.querySelector('.return .addButton').addEventListener('click', printReturned);
        document.querySelector('.available .addButton').addEventListener('click', printAvailable);
        document.querySelector('.summaryContainer.lost .addButton').addEventListener('click', printLost);
        document.querySelector('.summaryContainer.damage .addButton').addEventListener('click', printDamaged);
        document.querySelector('.summaryContainer.replace .addButton').addEventListener('click', printReplaced);
        document.querySelector('.added .addButton').addEventListener('click', printRecentlyAdded);
        document.querySelector('.overdue .addButton').addEventListener('click', printOverdue);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>

</html>