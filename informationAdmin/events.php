<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/events.css">
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
        
                        <a href="../informationAdmin/homePage/announcement.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Home</p>
                                </div>
                            </div>
                        </a>
        
                        <a href="../informationAdmin/program.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Departments</p>
                                </div>
                            </div>
                        </a>
        
                        <a href="../informationAdmin/facultyMember.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Faculty Members</p>
                                </div>
                            </div>
                        </a>
        
                        <a href="../informationAdmin/oraganization.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Organizations</p>
                                </div>
                            </div>
                        </a>
    
                        <a href="../informationAdmin/memorandum.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Memorandums</p>
                                </div>
                            </div>
                        </a>
    
                        <a href="../informationAdmin/announcement.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Announcements</p>
                                </div>
                            </div>
                        </a>
    
                        <a href="../informationAdmin/events.html">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Events</p>
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
                    <p class="text">Events</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Events</button>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>location</th>
                                <th>Uploaded At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
    
                        <tbody>
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>2021-10-05</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td class="button">
                                    <button onclick="editProgram()" class="addButton" style="width: 5rem;">Edit</button>
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
                    <p>Add Events</p>
                </div>
    
                <div class="subLoginContainer">
                    <div class="inputContainer">
                        <input class="inputEmail" type="text" placeholder="Title:">
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="text" placeholder="Location:">
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="date" placeholder="Date:">
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

    <div class="editContainer" style="display: none; background-color: none;">
        <div class="editContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Edit Events</p>
                </div>
    
                <div class="subLoginContainer">
                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Title:</label>
                        <input class="inputEmail" type="text" placeholder="Title:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Location:</label>
                        <input class="inputEmail" type="text" placeholder="Location:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Date:</label>
                        <input class="inputEmail" type="date" placeholder="Date:">
                    </div>
                    
                    <div class="inputContainer" style="flex-direction: column; height: 5rem; min-height: 12rem;">
                        <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Content:</label>
                        <textarea style="min-height: 10rem;" class="inputEmail" name="" id="" placeholder="Content"></textarea>
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
