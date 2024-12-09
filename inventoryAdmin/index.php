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
    <link rel="stylesheet" href="/dionSe/assets/css/dashboard.css">
</head>

<body>
    <div class="body" style="margin-bottom: 3rem;">
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
                        <p><?php echo $fullName; ?></php>
                        </p>
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
                    <p class="text">Dashboard</p>
                </div>

                <div class="dashboardContainer">
                    <div class="statusContainer">
                        <div onclick="borrowed()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Borrowed</p>
                            </div>

                            <div class="numberContainer">
                                <p>100</p>
                            </div>
                        </div>

                        <div onclick="return1()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Returned</p>
                            </div>

                            <div class="numberContainer">
                                <p>100</p>
                            </div>
                        </div>

                        <div onclick="available()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Available</p>
                            </div>

                            <div class="numberContainer">
                                <p>100</p>
                            </div>
                        </div>

                        <div onclick="lost()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Lost</p>
                            </div>

                            <div class="numberContainer">
                                <p>100</p>
                            </div>
                        </div>

                        <div onclick="damage()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Damaged</p>
                            </div>

                            <div class="numberContainer">
                                <p>100</p>
                            </div>
                        </div>

                        <div onclick="replace1()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Replaced Item</p>
                            </div>

                            <div class="numberContainer">
                                <p>100</p>
                            </div>
                        </div>

                        <div onclick="added()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Recently added</p>
                            </div>

                            <div class="numberContainer">
                                <p>100</p>
                            </div>
                        </div>

                        <div onclick="overdue()" class="subStatusContainer">
                            <div class="nameContainer" style="border-bottom: solid gray 1px;">
                                <p>Overdue</p>
                            </div>

                            <div class="numberContainer">
                                <p>100</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="searchContainer" style="margin-top: 2rem;">
                    <input class="searchBar" type="text" placeholder="Search...">
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

                        <tbody>
                            <tr>
                                <td>Hakdog</td>
                                <td>
                                    <img class="image" src="/dionSe/assets/img/CSSPE.png" alt="">
                                </td>
                                <td>Hakdog</td>
                                <td>3</td>
                            </tr>
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
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>3</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="buttonContainer">
                    <button class="addButton">Print</button>
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
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>3</td>
                            </tr>
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

    <div class="summaryContainer available" style="display: none; background-color: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">

                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Available</p>
                </div>

                <div class="tableContainer" style="margin: 0; padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>3</td>
                                <td>Hakdog</td>
                            </tr>
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
                                <th>Date Lostd</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>3</td>
                            </tr>
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
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>3</td>
                            </tr>
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
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>3</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                            </tr>
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

    <div class="summaryContainer added" style="display: none; background-color: none;">
        <div class="summaryContainer">
            <div class="subSummaryContainer">

                <div class="textContainer" style="color: white; justify-content: center; font-size: 3rem;">
                    <p>Replaced Added</p>
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
                            <tr>
                                <td>1</td>
                                <td>Hakdog</td>
                                <td>
                                    <img class="image" src="/dionSe/assets/img/CSSPE.png" alt="">
                                </td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                            </tr>
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
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                            </tr>
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
    <script src="/dionSe/assets/js/sidebar.js"></script>
    <script src="/dionSe/assets/js/dashboard.js"></script>
</body>

</html>