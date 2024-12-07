<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="./assets/css/login.css">
</head>

<body>
    <div class="container">
        <div class="headerContainer">
            <div class="subHeaderContainer">
                <div class="logoContainer">
                    <img class="logo" src="./assets/img/CSSPE.png" alt="">
                </div>

                <div class="collegeNameContainer">
                    <p>CSSPE Yes Inventory & Information System</p>
                </div>
            </div>

            <div class="subHeaderContainer">
                <a href="#about"><button class="aboutButton" id="#about">About</button></a>
            </div>
        </div>

        <div class="subContainer">
            <div class="backgroundColor">
                <div class="loginContainer">
                    <div class="titleContainer">
                        <p>Login</p>
                    </div>

                    <div class="subLoginContainer">
                        <div class="inputContainer">
                            <input class="inputEmail" type="email" placeholder="Email:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" type="password" placeholder="Password:">
                        </div>

                        <div class="inputContainer">
                            <button class="login">Login</button>
                        </div>

                        <div class="registerLinkContainer">
                            <p>Don't have an account? <span onclick="login()">Register</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aboutContainer" id="about">
        <div class="subAboutContainer">
            <div class="wmsuContainer1">
                <div class="wmsuLogo1">
                    <img class="logo" src="../assets/img/freepik-untitled-project-20241018143133NtJY.png" alt="">
                </div>

                <div class="wmsuLogo">
                    <p>Western Mindanao State University</p>
                </div>
            </div>

            <div class="wmsuContainer1">
                <div class="wmsuLogo1">
                    <img class="logo" src="../assets/img/CSSPE.png" alt="">
                </div>

                <div class="wmsuLogo">
                    <p>College of Sport Science and Physical Education</p>
                </div>
            </div>
        </div>

        <div class="subAboutContainer1">
            <div class="wmsuContainer">
                <div class="address">
                    <p style="text-align: center;">Normal Road, Baliwasan, Zamboanga City, Philippines</p>
                    <p>Wmsu CSSPE</p>
                    <p>wmsu@wmsu.edu.ph</p>
                    <p>991-1771</p>
                </div>
            </div>
        </div>

        <div class="subAboutContainer">
            <div class="wmsuContainer" style="display: flex; flex-direction: row;">
                <div class="address">
                    <div style="text-align: left;">
                        <p>CSSPE Goals</p>
                        <p>Quality Policy</p>
                        <p>Events</p>
                        <p>Articles</p>
                        <p>Memorandums</p>
                        <p>Departments</p>
                        <p>Organization</p>
                    </div>
                </div>

                <div class="address">
                    <div style="text-align: left;">
                        <p>Inventory</p>
                        <p>Teachers</p>
                        <p>Privacy Policy</p>
                        <p>Terms of Services</p>
                        <p>About</p>
                        <p>Contact</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="registerContainer" style="background-color: none; display: none;">
        <div class="registerContainer">
            <div class="loginContainer">
                <div class="titleContainer">
                    <p>Register</p>
                </div>

                <div class="subLoginContainer">
                    <div class="inputContainer">
                        <input class="inputEmail" type="text" placeholder="First Name:">
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="text" placeholder="Last Name:">
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="text" placeholder="Middle Name (Optional):">
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="email" placeholder="Email:">
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="text" placeholder="Address:">
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="text" placeholder="Contact No.:">
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

                    <div class="inputContainer">
                        <input class="inputEmail" type="password" placeholder="Password:">
                    </div>

                    <div class="inputContainer">
                        <input class="inputEmail" type="password" placeholder="Confirm Password:">
                    </div>

                    <div class="inputContainer">
                        <button class="login">Register</button>
                    </div>

                    <div class="registerLinkContainer">
                        <p>Already have an account? <span onclick="login()">Login</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/login.js"></script>
</body>

</html>