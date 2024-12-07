<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/oraganizationHome.css">
    <link rel="stylesheet" href="/dionSe/assets/css/sidebar.css">
</head>
<body>
    <div class="body">
        <div class="sidebar">
            <div  class="sidebarContent">
                <div class="arrowContainer" style="margin-left: 80rem;" id="toggleButton">
                    <div class="subArrowContainer">
                        <img class="hideIcon" src="/dionSe/assets/img/arrow.png" alt="">
                    </div>
                </div>
            </div>
            <div class="userContainer">
                <div class="subUserContainer">
                    <div class="userPictureContainer" >
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
                        <a href="../homePage/profile.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Profile</p>
                                </div>
                            </div>
                        </a>
        
                        <a href="../homePage/announcement.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Announcements</p>
                                </div>
                            </div>
                        </a>
        
                        <a href="../homePage/borrowing.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Inventories</p>
                                </div>
                            </div>
                        </a>
        
                        <a href="../homePage/memorandumHome.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Memorandums</p>
                                </div>
                            </div>
                        </a>
    
                        <a href="../homePage/events.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Events</p>
                                </div>
                            </div>
                        </a>
    
                        <a href="../homePage/members.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Faculty Members</p>
                                </div>
                            </div>
                        </a>
    
                        <a href="../homePage/organization.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Organizations</p>
                                </div>
                            </div>
                        </a>
    
                        <a href="../homePage/notification.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Notificaitons</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
        
                <div class="subUserContainer">
                    <a href="/dionSe/authentication/login.html">
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
                                    <button onclick="popup12()" class="addButton" style="width: 10rem;">Manage Project</button>
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
                                        <img class="image" src="/dionSe/assets/img/CSSPE.png" alt="">
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
    <script src="/dionSe/assets/js/sidebar.js"></script>
    <script src="/dionSe/assets/js/program.js"></script>
</body>
</html>
