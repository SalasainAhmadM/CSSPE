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

$query = "SELECT * FROM organizations";
$result = mysqli_query($conn, $query);


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
    <title>Organizations</title>

    <link rel="stylesheet" href="../assets/css/oraganizationHome.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>

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
                            <img class="subUserPictureContainer"
                                src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>"
                                alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p><?php echo $fullName; ?></p>
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

                <div class="textContainer">
                    <p class="text">Organizations</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" id="search" type="text" placeholder="Search...">
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Image</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tbody>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['organization_name']); ?></td>
                                    <td><img class="image" src="<?php echo htmlspecialchars($row['image']); ?>" alt=""></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                            </tbody>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="popup" style="display: none;">
        <div class="popup">
            <div class="mainContainer" style="margin-left: 250px;">
                <div class="container">

                    <div class="textContainer">
                        <p class="text">Tech Club</p>
                    </div>

                    <div class="searchContainer">
                        <input class="searchBar" type="text" placeholder="Search...">
                        <button onclick="popup12()" class="addButton size">Close Table</button>
                    </div>

                    <div class="tableContainer">
                        <table>
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Image</th>
                                    <th>Description</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>Hakdog</td>
                                    <td>
                                        <img class="image" src="../assets/img/CSSPE.png" alt="">
                                    </td>
                                    <td>Hakdog</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/search.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>

</body>

</html>