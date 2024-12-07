<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/borrowing.css">
    <link rel="stylesheet" href="/dionSe/assets/css/sidebar.css">
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
                        <p>Khriz marr l. falcatan</p>
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

                        <a href="../inventoryAdmin/dashboard.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Dashboard</p>
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
                            <img class="logo" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="collegeNameContainer">
                            <p>CSSPE Inventory & Information System</p>
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">Borrow request</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size">Print</button>
                        <button onclick="addProgram()" class="addButton size">Borrow Item</button>
                        <select name="" class="addButton size" id="">
                            <option value="">Filter</option>
                        </select>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Class Schedule</th>
                                <th>Return Date</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Action</th>
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
                                <td>Hakdog</td>
                                <td class="button">
                                    <button class="addButton" style="width: 7rem;">Approve</button>
                                    <button class="addButton1" style="width: 7rem;">Declined</button>
                                    <button onclick="editProgram()" class="addButton" style="width: 7rem;">Edit</button>
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
                    <p>Borrow Item</p>
                </div>

                <div class="subLoginContainer">
                    <div class="inputContainer">
                        <select name="" id="" class="inputEmail">
                            <option value="">Choose a Item</option>
                        </select>
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

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Quantity:</label>
                        <input class="inputEmail" type="Number">
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
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Borrowed
                            Item:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">First
                            Name:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Last
                            Name:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Middle
                            Name:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                        <input class="inputEmail" type="email">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                            No.:</label>
                        <input class="inputEmail" type="number">
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem;">
                        <select class="inputEmail" name="" id="">
                            <option value="">Choose a Departments</option>
                        </select>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem;">
                        <select class="inputEmail" name="" id="">
                            <option value="">Choose a rank</option>
                            <option value="Instructor">Instructor</option>
                            <option value="Assistant Professor">Assistant Professor</option>
                            <option value="Associate Professor">Associate Professor</option>
                            <option value="Professor">Professor</option>
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

    <script src="/dionSe/assets/js/sidebar.js"></script>
    <script src="/dionSe/assets/js/program.js"></script>
</body>

</html>