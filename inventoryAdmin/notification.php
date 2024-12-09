<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $inventoryAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
} else {
    $fullName = "User Not Found";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/organization.css">
    <link rel="stylesheet" href="/dionSe/assets/css/sidebar.css">
    <link rel="stylesheet" href="/dionSe/assets/css/notification.css">
</head>

<body>
    <div class="body">
        <div class="sidebar">
            <div class="sidebarContent">
                <div class="arrowContainer" style="margin-left: 80rem;" id="toggleButton">
                    <div class="subArrowContainer">
                        <img class="hideIcon" src="/dionSe/assets/img/arrow.png" alt="">
                    </div>
                </div>
            </div>
            <div class="userContainer">
                <div class="subUserContainer">
                    <div class="userPictureContainer">
                        <div class="subUserPictureContainer">
                            <img class="subUserPictureContainer" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p><?php echo $fullName; ?></p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">

                        <a href="../inventoryAdmin/homePage/announcement.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Home</p>
                                </div>
                            </div>
                        </a>

                        <!-- <a href="../inventoryAdmin/dashboard.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Dashboard</p>
                                </div>
                            </div>
                        </a> -->

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
                            <img class="logo" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="collegeNameContainer">
                            <p>CSSPE Inventory & Information System</p>
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">Notification</p>
                </div>

                <div class="dashboardContainer">
                    <div class="notificationContainer">
                        <div class="subNotificaitonContainer">
                            <div class="messageContainer">
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Excepturi eum quam id fuga!
                                    Dolore ex voluptates sint dignissimos ipsum molestias alias at quibusdam numquam,
                                    accusantium voluptatem minus! Aspernatur, blanditiis id!</p>
                            </div>

                            <div class="dateContainer">
                                <p style="margin-left: 0.5rem;">2024-03-28</p>
                            </div>
                        </div>

                        <div class="deleteContainer">
                            <button class="addButton">Delete</button>
                        </div>
                    </div>

                    <div class="notificationContainer">
                        <div class="subNotificaitonContainer">
                            <div class="messageContainer">
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Excepturi eum quam id fuga!
                                    Dolore ex voluptates sint dignissimos ipsum molestias alias at quibusdam numquam,
                                    accusantium voluptatem minus! Aspernatur, blanditiis id!</p>
                            </div>

                            <div class="dateContainer">
                                <p style="margin-left: 0.5rem;">2024-03-28</p>
                            </div>
                        </div>

                        <div class="deleteContainer">
                            <button class="addButton">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/dionSe/assets/js/sidebar.js"></script>
</body>

</html>