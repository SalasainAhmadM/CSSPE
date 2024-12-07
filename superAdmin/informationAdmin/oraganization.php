<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/organization.css">
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
                        <a href="../account.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Back to Super Admin Panel</p>
                                </div>
                            </div>
                        </a>


                        <a href="../informationAdmin/program.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Departments</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/facultyMember.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Faculty Members</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/oraganization.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Organizations</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/memorandum.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Memorandums</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/announcement.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Announcements</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/events.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Events</p>
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
                    <p class="text">Organizations</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Organization</button>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Organization Name</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Hakdog</td>
                                <td>
                                    <img class="image" src="/dionSe/assets/img/CSSPE.png" alt="">
                                </td>
                                <td>Hakdog</td>
                                <td class="button">
                                    <button onclick="editProgram()" class="addButton" style="width: 5rem;">Edit</button>
                                    <button class="addButton1" style="width: 5rem;">Delete</button>
                                    <button onclick="popup12()" class="addButton" style="width: 10rem;">Manage
                                        Project</button>
                                </td>
                            </tr>
                        </tbody>
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
                        <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                            <button class="addButton size">Print</button>
                            <button onclick="addProgram()" class="addButton size">Add Organization</button>
                        </div>
                    </div>

                    <div class="tableContainer">
                        <table>
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Image</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>Hakdog</td>
                                    <td>
                                        <img class="image" src="/dionSe/assets/img/CSSPE.png" alt="">
                                    </td>
                                    <td>Hakdog</td>
                                    <td class="button">
                                        <button onclick="editProgram()" class="addButton"
                                            style="width: 5rem;">Edit</button>
                                        <button class="addButton1" style="width: 5rem;">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="addContainer" style="display: none; background-color: none;">
        <div class="addContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Add Organization</p>
                </div>

                <div class="subLoginContainer">
                    <div class="uploadContainer">
                        <div class="subUploadContainer">
                            <div class="displayImage">
                                <img class="image1" src="" alt="">
                            </div>
                        </div>

                        <div class="uploadButton">
                            <input id="imageUpload" type="file" accept="image/*" style="display: none;"
                                onchange="previewImage()">
                            <button onclick="triggerImageUpload()" class="addButton"
                                style="height: 2rem; width: 5rem;">Upload</button>
                        </div>
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="text" placeholder="Organization Name:">
                    </div>

                    <div class="inputContainer" style="height: 10rem;">
                        <textarea class="inputEmail" name="" id="" placeholder="Description"></textarea>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                        <button class="addButton" style="width: 6rem;">Add</button>
                        <button onclick="addProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="editContainer" style="display: none; background-color: none; position: fixed;">
        <div class="editContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Edit Organization</p>
                </div>

                <div class="subLoginContainer">
                    <div class="uploadContainer">
                        <div class="subUploadContainer">
                            <div class="displayImage">
                                <img class="image1" src="" alt="">
                            </div>
                        </div>

                        <div class="uploadButton">
                            <input id="imageUpload" type="file" accept="image/*" style="display: none;"
                                onchange="previewImage()">
                            <button onclick="triggerImageUpload()" class="addButton"
                                style="height: 2rem; width: 5rem;">Upload</button>
                        </div>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Organization
                            Name:</label>
                        <input class="inputEmail" type="text" placeholder="Program Name:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem; min-height: 12rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Description:</label>
                        <textarea style="min-height: 10rem;" class="inputEmail" name="" id=""
                            placeholder="Description"></textarea>
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
    <script src="/dionSe/assets/js/uploadImage.js"></script>
</body>

</html>