<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('instructor');

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
    <title>Borrowing</title>

    <link rel="stylesheet" href="../assets/css/borrowingHome.css">
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
                            <img class="subUserPictureContainer" src="../assets/img/CSSPE.png" alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p>Khriz marr l. falcatan</p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">
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
                                    <p>
                                        Notifications
                                        <span style="background-color:#1a1a1a; padding:5px; border-radius:4px;">
                                            <?php echo $notificationCount; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="subUserContainer">
                    <a href="/dionSe/authentication/login.php">
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
                    <p class="text">Inventory</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar2" type="text" placeholder="Search..." oninput="searchCard2()">
                    <select name="" class="addButton size">
                        <option value="">Sort By Availability</option>
                        <option value="availabilityHighToLow">Highest to Lowest</option>
                        <option value="availabilityLowToHigh">Lowest to Highest</option>
                    </select>
                </div>

                <div class="inventoryContainer">

                    <div class="subInventoryContainer">
                        <div class="imageContainer" style="border-bottom: solid gray 1px;">
                            <img class="image" src="../assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="infoContainer">
                            <p>Item Name</p>
                        </div>

                        <div class="infoContainer1">
                            <p>Description</p>
                        </div>

                        <div class="infoContainer1">
                            <p>Available: 6</p>
                        </div>

                        <div class="buttonContainer">
                            <button onclick="editProgram()" class="addButton">Borrow</button>
                        </div>
                    </div>

                    <div class="subInventoryContainer">
                        <div class="imageContainer" style="border-bottom: solid gray 1px;">
                            <img class="image" src="../assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="infoContainer">
                            <p>Nzro</p>
                        </div>

                        <div class="infoContainer1">
                            <p>Description</p>
                        </div>

                        <div class="infoContainer1">
                            <p>Available: 10</p>
                        </div>

                        <div class="buttonContainer">
                            <button onclick="editProgram()" class="addButton">Borrow</button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="editContainer" style="display: none; background-color: none;">
        <div class="editContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Borrowed Item</p>
                </div>

                <div class="subLoginContainer">
                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Item Name:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer">
                        <select name="" id="" class="inputEmail">
                            <option value="">Choose a brand</option>
                        </select>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Quantity:</label>
                        <input class="inputEmail" type="Number" placeholder="Quantity:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Borrow Date:</label>
                        <input class="inputEmail" type="date" placeholder="Date:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Return Date:</label>
                        <input class="inputEmail" type="date" placeholder="Date:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class schedule time from:</label>
                        <input class="inputEmail" type="time" placeholder="From:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class schedule time to:</label>
                        <input class="inputEmail" type="time" placeholder="To">
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                        <button class="addButton" style="width: 6rem;">Borrow</button>
                        <button onclick="editProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/search_Card.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>

</body>

</html>