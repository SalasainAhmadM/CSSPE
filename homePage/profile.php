<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);

// Fetch user data
$userid = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, middle_name, email, address, contact_no, rank, role, image, department 
        FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

// Fetch borrowed history
$sql_borrow_history = "SELECT t.transaction_id, t.quantity_borrowed, t.borrowed_at, t.return_date, t.status, 
                              i.name AS item_name, i.brand AS item_brand
                       FROM item_transactions t
                       JOIN items i ON t.item_id = i.id
                       WHERE t.users_id = ?
                       ORDER BY t.borrowed_at DESC";
$stmt_borrow_history = $conn->prepare($sql_borrow_history);
$stmt_borrow_history->bind_param("i", $userid);
$stmt_borrow_history->execute();
$borrow_history_result = $stmt_borrow_history->get_result();

// Fetch lost, damaged, and replaced items
$sql_item_status = "SELECT 
                        status, COUNT(*) AS count 
                    FROM returned_items 
                    WHERE transaction_id IN (
                        SELECT transaction_id 
                        FROM item_transactions 
                        WHERE users_id = ?
                    ) 
                    GROUP BY status";
$stmt_item_status = $conn->prepare($sql_item_status);
$stmt_item_status->bind_param("i", $userid);
$stmt_item_status->execute();
$item_status_result = $stmt_item_status->get_result();

$statuses = [
    'Lost' => 0,
    'Damaged' => 0,
    'Replaced' => 0,
];

while ($row = $item_status_result->fetch_assoc()) {
    $statuses[$row['status']] = $row['count'];
}

$stmt_item_status->close();


// Fetch Lost Items for Logged-in User
$lostQuery = "
    SELECT 
        r.return_id,
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        CONCAT(u.first_name, ' ', u.last_name) AS fullname,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Lost' AND u.id = ?
";
$lostStmt = $conn->prepare($lostQuery);
$lostStmt->bind_param("i", $userid);
$lostStmt->execute();
$lostItems = $lostStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Damaged Items for Logged-in User
$damagedQuery = "
    SELECT 
        r.return_id,
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        CONCAT(u.first_name, ' ', u.last_name) AS fullname,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Damaged' AND u.id = ?
";
$damagedStmt = $conn->prepare($damagedQuery);
$damagedStmt->bind_param("i", $userid);
$damagedStmt->execute();
$damagedItems = $damagedStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Replaced Items for Logged-in User
$replacedQuery = "
    SELECT 
        r.return_id,
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        CONCAT(u.first_name, ' ', u.last_name) AS fullname,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Replaced' AND u.id = ?
";
$replacedStmt = $conn->prepare($replacedQuery);
$replacedStmt->bind_param("i", $userid);
$replacedStmt->execute();
$replacedItems = $replacedStmt->get_result()->fetch_all(MYSQLI_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_SESSION['user_id'];

    // Update profile details
    $firstName = $_POST['first_name'] ?? $user['first_name'];
    $lastName = $_POST['last_name'] ?? $user['last_name'];
    $middleName = $_POST['middle_name'] ?? $user['middle_name'];
    $email = $_POST['email'] ?? $user['email'];
    $address = $_POST['address'] ?? $user['address'];
    $contactNo = $_POST['contact_no'] ?? $user['contact_no'];
    $rank = $_POST['rank'] ?? $user['rank'];
    $department = $_POST['department'] ?? $user['department'];

    $sql = "UPDATE users SET first_name = ?, last_name = ?, middle_name = ?, email = ?, address = ?, contact_no = ?, rank = ?, department = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $firstName, $lastName, $middleName, $email, $address, $contactNo, $rank, $department, $userid);

    if ($stmt->execute()) {
        $stmt->close();

        // Handle password change
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userid);
            $stmt->execute();
            $stmt->close();
        }

        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_image']['tmp_name'];
            $fileName = $_FILES['profile_image']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = $userid . '_profile.' . $fileExtension;
                $uploadFileDir = '../assets/img/';
                $destPath = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $sql = "UPDATE users SET image = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $newFileName, $userid);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $_SESSION['message'] = "Profile updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to update profile!";
        $_SESSION['message_type'] = "error";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$query_notifications = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";
$result_notifications = mysqli_query($conn, $query_notifications);
$notificationCount = 0;

if ($result_notifications && $row_notifications = mysqli_fetch_assoc($result_notifications)) {
    $notificationCount = $row_notifications['notification_count'];
}

$conn->close();
?>



<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <link rel="stylesheet" href="../assets/css/organization.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">

</head>
<style>
    .dashboardContainer {
        margin-top: 30px;
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .statusContainer {
        width: 90%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 0 1rem 0;
        border: solid gray 1px;
        border-radius: 0.5rem;
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.699);
        background-color: rgb(223, 222, 222);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .subStatusContainer {
        height: 7rem;
        width: 9.5rem;
        border-radius: 0.5rem;
        background-color: white;
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.699);
        display: flex;
        flex-direction: column;
        cursor: pointer;
        transition: all 0.15s;
    }

    .subStatusContainer:hover {
        transform: scale(1.1);
    }

    .subStatusContainer:active {
        transform: scale(1);
        box-shadow: none;
    }

    .nameContainer {
        height: 2rem;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.2rem;
        font-weight: bold;
    }

    .numberContainer {
        height: 5rem;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 3rem;
    }

    .replacedButton {
        width: 92%;
        height: 3rem;
        border: none;
        border-radius: 0.5rem;
        background-color: rgb(109, 18, 10);
        color: white;
        font-size: min(1.2rem, 1.1rem);
        font-weight: bold;
        cursor: pointer;
        transition: all 0.15s;
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.699);
    }
</style>

<body>
    <div class="body">
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
                            <?php
                            $profileImage = !empty($user['image']) ? "../assets/img/" . htmlspecialchars($user['image']) : "../assets/img/CSSPE.png";
                            ?>
                            <img class="subUserPictureContainer" src="<?php echo $profileImage; ?>"
                                alt="Profile Picture">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p>
                            <?php
                            echo htmlspecialchars($user['first_name']) . ' ' .
                                (!empty($user['middle_name']) ? htmlspecialchars($user['middle_name']) . ' ' : '') .
                                htmlspecialchars($user['last_name']);
                            ?>
                        </p>
                    </div>
                </div>


                <div class="navContainer">
                    <div class="subNavContainer">
                        <?php if ($_SESSION['user_role'] === 'inventory_admin'): ?>
                            <a href="../inventoryAdmin/index.php">
                                <div class="buttonContainer1">
                                    <div class="nameOfIconContainer">
                                        <p>Back to Inventory Admin Panel</p>
                                    </div>
                                </div>
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'information_admin'): ?>
                            <a href="../informationAdmin/index.php">
                                <div class="buttonContainer1">
                                    <div class="nameOfIconContainer">
                                        <p>Back to Information Admin Panel</p>
                                    </div>
                                </div>
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'super_admin'): ?>
                            <a href="../superAdmin/index.php">
                                <div class="buttonContainer1">
                                    <div class="nameOfIconContainer">
                                        <p>Back to Super Admin Panel</p>
                                    </div>
                                </div>
                            </a>
                        <?php endif; ?>

                        <a href="../homePage/profile.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Profile</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Announcements</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/borrowing.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Inventories</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/memorandumHome.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Memorandums</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/events.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Events</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/members.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Faculty Members</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/organization.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Organizations</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/notification.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Notifications</p>
                                </div>
                            </div>
                        </a>
                        <!-- <a href="../homePage/notification.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>
                                        Notifications
                                        <span style="background-color:#1a1a1a; padding:5px; border-radius:4px;">
                                            <?php echo $notificationCount; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </a> -->
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

                <div class="dashboardContainer">
                    <div class="statusContainer">


                        <div onclick="lost()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Lost</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $statuses['Lost']; ?></p>
                            </div>
                        </div>

                        <div onclick="damage()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Damaged</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $statuses['Damaged']; ?></p>
                            </div>
                        </div>

                        <div onclick="replace1()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Replaced Item</p>
                            </div>
                            <div class="numberContainer">
                                <p><?php echo $statuses['Replaced']; ?></p>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="textContainer">
                    <p class="text">Profile</p>
                </div>

                <div class="profileContainer">
                    <div class="subProfileContainer">

                        <div class="infoContainer">
                            <div class="pictureContainer1" style="background-color: none;">

                                <div class="pictureContainer">
                                    <?php
                                    $profileImage = !empty($user['image']) ? "../assets/img/" . htmlspecialchars($user['image']) : "../assets/img/CSSPE.png";
                                    ?>
                                    <img src="<?php echo $profileImage; ?>" alt="Profile Picture" style="width: 100%">
                                </div>

                                <div style="margin-top: 1rem;">
                                    <button onclick="profile()" class="addButton">Edit Profile</button>
                                </div>
                            </div>

                            <div class="subLoginContainer">

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Full
                                        Name:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                                        No.:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['contact_no']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['address']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Position:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['rank']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Department:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['department']) ?>
                                    </h3>
                                </div>

                            </div>

                        </div>

                        <div class="borrowContainer">
                            <div class="titleContainer1">
                                <p>Borrow History</p>
                            </div>

                            <div class="searchContainer">
                                <input id="searchBar" class="searchBar" type="text" placeholder="Search...">
                            </div>

                            <div class="tableContainer">
                                <?php if ($borrow_history_result->num_rows > 0): ?>
                                    <table class="borrow-history-table" id="borrowHistoryTable">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Item Name</th>
                                                <th>Brand</th>
                                                <th>Quantity</th>
                                                <th>Borrow Date</th>
                                                <th>Expected Return Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php while ($row = $borrow_history_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                                                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['item_brand']) ?></td>
                                                    <td><?= htmlspecialchars($row['quantity_borrowed']) ?></td>
                                                    <td><?= htmlspecialchars($row['borrowed_at']) ?></td>
                                                    <td><?= htmlspecialchars($row['return_date'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>No borrow history available.</p>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lost Items Table -->
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
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Date Lost</th>
                                <!-- <th>Fullname</th> -->
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lostItems)): ?>
                                <?php foreach ($lostItems as $item): ?>
                                    <tr>
                                        <td><?php echo $item['return_id']; ?></td>
                                        <td><?php echo $item['item_name']; ?></td>
                                        <td><?php echo $item['brand']; ?></td>
                                        <td><?php echo $item['quantity_returned']; ?></td>
                                        <td><?php echo $item['returned_at']; ?></td>
                                        <!-- <td><?php echo $item['fullname']; ?></td> -->
                                        <td><?php echo $item['contact_no']; ?></td>
                                        <td><?php echo $item['email']; ?></td>
                                        <td><?php echo $item['remarks']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No lost items.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="buttonContainer">
                    <!-- <button class="addButton">Print</button> -->
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
                                <!-- <th>Fullname</th> -->
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Remark</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($damagedItems)): ?>
                                <?php foreach ($damagedItems as $item): ?>
                                    <tr>
                                        <td><?php echo $item['return_id']; ?></td>
                                        <td><?php echo $item['item_name']; ?></td>
                                        <td><?php echo $item['brand']; ?></td>
                                        <td><?php echo $item['quantity_returned']; ?></td>
                                        <td><?php echo $item['returned_at']; ?></td>
                                        <!-- <td><?php echo $item['fullname']; ?></td> -->
                                        <td><?php echo $item['contact_no']; ?></td>
                                        <td><?php echo $item['email']; ?></td>
                                        <td><?php echo $item['remarks']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No damaged items.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <div class="buttonContainer">
                    <!-- <button class="addButton">Print</button> -->
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
                                <!-- <th>Fullname</th> -->
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Remark</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($replacedItems)): ?>
                                <?php foreach ($replacedItems as $item): ?>
                                    <tr>
                                        <td><?php echo $item['return_id']; ?></td>
                                        <td><?php echo $item['item_name']; ?></td>
                                        <td><?php echo $item['brand']; ?></td>
                                        <td><?php echo $item['quantity_returned']; ?></td>
                                        <td><?php echo $item['returned_at']; ?></td>
                                        <!-- <td><?php echo $item['fullname']; ?></td> -->
                                        <td><?php echo $item['contact_no']; ?></td>
                                        <td><?php echo $item['email']; ?></td>
                                        <td><?php echo $item['remarks']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No lost items.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <div class="buttonContainer">
                    <!-- <button class="addButton">Print</button> -->
                    <button onclick="replace1()" class="addButton">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="editContainer3 size1" id="profileModal" style="display: none; background-color: none; width: 100%;">
        <div class="editContainer3 size1">
            <div class="subProfileContainer size6">
                <div class="infoContainer">
                    <div class="pictureContainer1" style="background-color: none;">
                        <div class="pictureContainer">
                            <img id="profileImagePreview"
                                src="<?= '../assets/img/' . (!empty($user['image']) && file_exists('../assets/img/' . $user['image']) ? htmlspecialchars($user['image']) : 'CSSPE.png') ?>"
                                alt="Profile Picture" style="width: 100%; border-radius: 50%; object-fit: cover;">
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="file" name="profile_image" id="profileImageInput" accept="image/*" required>
                            <button type="submit" class="addButton">Change Profile Image</button>
                        </form>
                    </div>

                    <div class="subLoginContainer">

                        <form action="" method="POST" enctype="multipart/form-data">

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">First
                                    Name:</label>
                                <input class="inputEmail" name="first_name"
                                    value="<?= htmlspecialchars($user['first_name']) ?>"="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Last
                                    Name:</label>
                                <input class="inputEmail" name="last_name"
                                    value="<?= htmlspecialchars($user['last_name']) ?>" type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Middle
                                    Name:</label>
                                <input class="inputEmail" name="middle_name"
                                    value="<?= htmlspecialchars($user['middle_name']) ?>" type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                                <input class="inputEmail" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                                    type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                                    No.:</label>
                                <input class="inputEmail" name="contact_no"
                                    value="<?= htmlspecialchars($user['contact_no']) ?>" type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                                <input class="inputEmail" name="address"
                                    value="<?= htmlspecialchars($user['address']) ?>" type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Position:</label>
                                <input class="inputEmail" name="rank" value="<?= htmlspecialchars($user['rank']) ?>"
                                    type="text">
                            </div>


                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Department:</label>
                                <input class="inputEmail" name="department"
                                    value="<?= htmlspecialchars($user['department']) ?>" type="text">
                            </div>



                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Password:</label>
                                <input class="inputEmail" name="password" id="password" type="password"
                                    placeholder="New Password (leave blank if unchanged)">
                                <i id="togglePassword" class="fas fa-eye toggle-password-icon"></i>
                            </div>
                            <div class="inputContainer"
                                style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                                <button type="submit" class="addButton" style="width: 6rem;">Save</button>
                                <button type="button" class="addButton1" style="width: 6rem;"
                                    onclick="closeModal()">Cancel</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('profileImageInput').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('profileImagePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('searchBar').addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#borrowHistoryTable tbody tr');

            rows.forEach(row => {
                const itemName = row.cells[1].textContent.toLowerCase();
                if (itemName.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: "<?php echo $_SESSION['message_type']; ?>",
                title: "<?php echo $_SESSION['message']; ?>",
                showConfirmButton: false,
                timer: 3000
            });
            <?php unset($_SESSION['message'], $_SESSION['message_type'], $_SESSION['message_text']); ?>
        <?php endif; ?>

        function closeModal() {
            document.getElementById('profileModal').style.display = 'none';
        }


        function lost() {
            const lostButton = document.querySelector('.lost');

            if (lostButton.style.display === 'none') {
                lostButton.style.display = 'block';
            } else {
                lostButton.style.display = 'none'
            }
        }

        function damage() {
            const damageButton = document.querySelector('.damage');

            if (damageButton.style.display === 'none') {
                damageButton.style.display = 'block';
            } else {
                damageButton.style.display = 'none'
            }
        }

        function replace1() {
            const replaceButton = document.querySelector('.replace');

            if (replaceButton.style.display === 'none') {
                replaceButton.style.display = 'block';
            } else {
                replaceButton.style.display = 'none'
            }
        }
    </script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/uploadImage.js"></script>
    <script src="../assets/js/profile.js"></script>
</body>

</html>