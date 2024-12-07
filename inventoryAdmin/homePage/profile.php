<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/organization.css">
    <link rel="stylesheet" href="/dionSe/assets/css/sidebar.css">
    <link rel="stylesheet" href="/dionSe/assets/css/profile.css">
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
                        <a href="../dashboard.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Back to Inventory Admin Panel</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/profile.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Profile</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/announcement.php">
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
                                    <p>Notificaitons</p>
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
                    <p class="text">Profile</p>
                </div>

                <div class="profileContainer">
                    <div class="subProfileContainer">
                        <div class="infoContainer">
                            <div class="pictureContainer1" style="background-color: none;">
                                <div class="pictureContainer">
                                    <img class="picture" src="/dionSe/assets/img/CSSPE.png" alt="">
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
                                    <input class="inputEmail" type="text">
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                                    <input class="inputEmail" type="text">
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                                        No.:</label>
                                    <input class="inputEmail" type="text">
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                                    <input class="inputEmail" type="text">
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Position:</label>
                                    <input class="inputEmail" type="text">
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Department:</label>
                                    <input class="inputEmail" type="text">
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Role:</label>
                                    <input class="inputEmail" type="text">
                                </div>
                            </div>
                        </div>

                        <div class="borrowContainer">
                            <div class="titleContainer1">
                                <p>Borrow History</p>
                            </div>

                            <div class="searchContainer">
                                <input class="searchBar" type="text" placeholder="Search...">
                            </div>

                            <div class="tableContainer">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Item Name</th>
                                            <th>Brand</th>
                                            <th>Quantity</th>
                                            <th>Expected Return Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Hakdog</td>
                                            <td>Hakdog</td>
                                            <td>Hakdog</td>
                                            <td>Hakdog</td>
                                            <td>Hakdog</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="editContainer3 size1" style="display: none; background-color: none; width: 100%;">
        <div class="editContainer3 size1">
            <div class="subProfileContainer size6">
                <div class="infoContainer">
                    <div class="pictureContainer1" style="background-color: none;">
                        <div class="pictureContainer">
                            <img class="picture" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>

                        <div style="margin-top: 1rem; display: flex; justify-content: center; align-items: center;">
                            <button onclick="triggerImageUpload()" class="addButton" id="imageUpload"
                                style="width: 100%;">Change Profile</button>
                        </div>
                    </div>

                    <div class="subLoginContainer">
                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Full
                                Name:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                                No.:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Position:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Department:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Role:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button class="addButton" style="width: 6rem;">Save</button>
                            <button onclick="profile()" class="addButton1" style="width: 6rem;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/dionSe/assets/js/sidebar.js"></script>
    <script src="/dionSe/assets/js/uploadImage.js"></script>
    <script src="/dionSe/assets/js/profile.js"></script>
</body>

</html>