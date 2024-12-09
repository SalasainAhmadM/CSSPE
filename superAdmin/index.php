<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php'; 

validateSessionRole('super_admin');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="../assets/css/account.css">
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

                        <a href="../superAdmin/homePage/announcement.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Home</p>
                                </div>
                            </div>
                        </a>

                        <a href="../superAdmin/account.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Accounts</p>
                                </div>
                            </div>
                        </a>

                        <a href="../superAdmin/pendingAccount.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Pending Accounts</p>
                                </div>
                            </div>
                        </a>

                        <a href="../superAdmin/createAdmin.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Create Account</p>
                                </div>
                            </div>
                        </a>

                        <a href="../superAdmin/informationAdmin/program.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Information Admin Panel</p>
                                </div>
                            </div>
                        </a>

                        <a href="../superAdmin/inventoryAdmin/dashboard.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Inventory Admin Panel</p>
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
                    <p class="text">Verified Accounts</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size">Print</button>
                        <select name="" class="addButton size" id="">
                            <option value="">Choose a role</option>
                        </select>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Contact Number</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>km falcatan</td>
                                <td>Teacher</td>
                                <td>Verified</td>
                                <td>Verified</td>
                                <td>Verified</td>
                                <td>Verified</td>
                                <td>Verified</td>
                                <td class="button">
                                    <button class="addButton1" style="width: 5rem;">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="addContainer" style="display: none; background-color: none;">
        <div class="addContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Create Borrowing</p>
                </div>

                <div class="subLoginContainer">
                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Item
                            Name:</label>
                        <input class="inputEmail" type="text" placeholder="Item Name:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Quantity:</label>
                        <input class="inputEmail" type="Number" placeholder="Quantity:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Borrow
                            Date:</label>
                        <input class="inputEmail" type="date" placeholder="Date:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class
                            schedule time from:</label>
                        <input class="inputEmail" type="time" placeholder="From:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class
                            schedule time to:</label>
                        <input class="inputEmail" type="time" placeholder="To">
                    </div>

                    <div class="inputContainer">
                        <select name="" id="" class="inputEmail">
                            <option value="">Choose a brand</option>
                        </select>
                    </div>

                    <div class="inputContainer">
                        <select name="" id="" class="inputEmail">
                            <option value="">Choose a teacher</option>
                        </select>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                        <button class="addButton" style="width: 6rem;">Add</button>
                        <button onclick="addProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="editContainer" style="display: none; background-color: none;">
        <div class="editContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Edit Borrowed Item</p>
                </div>

                <div class="subLoginContainer">
                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Item
                            Name:</label>
                        <input class="inputEmail" type="text" placeholder="Item Name:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Quantity:</label>
                        <input class="inputEmail" type="Number" placeholder="Quantity:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Borrow
                            Date:</label>
                        <input class="inputEmail" type="date" placeholder="Date:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class
                            schedule time from:</label>
                        <input class="inputEmail" type="time" placeholder="From:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class
                            schedule time to:</label>
                        <input class="inputEmail" type="time" placeholder="To">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Brand:</label>
                        <input class="inputEmail" type="text" placeholder="Brand:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Teacher:</label>
                        <input class="inputEmail" type="text" placeholder="Teacher:">
                    </div>

                    <div class="inputContainer">
                        <select name="" id="" class="inputEmail">
                            <option value="">Update a Status</option>
                        </select>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                        <button class="addButton" style="width: 6rem;">Save</button>
                        <button onclick="editProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
</body>

</html>