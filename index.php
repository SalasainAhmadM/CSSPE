<?php
session_start();
require_once './conn/conn.php';

function registerUser($firstName, $lastName, $middleName, $email, $address, $contactNo, $rank, $password, $department)
{
    global $conn;

    // Check for existing email
    $emailQuery = "SELECT * FROM users WHERE email = ?";
    $emailStmt = $conn->prepare($emailQuery);
    $emailStmt->bind_param("s", $email);
    $emailStmt->execute();
    $emailResult = $emailStmt->get_result();

    if ($emailResult->num_rows > 0) {
        return "Error: Email already exists.";
    }

    // Check for existing name (first name and last name combination)
    $nameQuery = "SELECT * FROM users WHERE first_name = ? AND last_name = ?";
    $nameStmt = $conn->prepare($nameQuery);
    $nameStmt->bind_param("ss", $firstName, $lastName);
    $nameStmt->execute();
    $nameResult = $nameStmt->get_result();

    if ($nameResult->num_rows > 0) {
        return "Error: User with the same name already exists.";
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $role = "instructor"; // Default role
    $defaultImage = "CSSPE.png"; // Default image value

    $insertQuery = "
    INSERT INTO pending_users (first_name, last_name, middle_name, email, address, contact_no, rank, password, role, image, department)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param(
        "sssssssssss",
        $firstName,
        $lastName,
        $middleName,
        $email,
        $address,
        $contactNo,
        $rank,
        $hashedPassword,
        $role,
        $defaultImage,
        $department
    );

    if ($stmt->execute()) {
        return "Registration successful. Wait for Approval!";
    } else {
        return "Error: " . $stmt->error;
    }
}


function loginUser($email, $password)
{
    global $conn;

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect based on the role
            if ($_SESSION['user_role'] == 'instructor') {
                header("Location: ./homePage/");
            } elseif ($_SESSION['user_role'] == 'super_admin') {
                header("Location: ./superAdmin/");
            } elseif ($_SESSION['user_role'] == 'inventory_admin') {
                header("Location: ./inventoryAdmin/");
            } elseif ($_SESSION['user_role'] == 'information_admin') {
                header("Location: ./informationAdmin/");
            } else {
                header("Location: ./homePage/"); // Default fallback
            }
            exit();
        } else {
            return "Error: Incorrect password.";
        }
    } else {
        return "Error: Email not found.";
    }
}



$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $middleName = $_POST['middle_name'] ?? null;
        $email = $_POST['email'];
        $address = $_POST['address'];
        $contactNo = $_POST['contact_no'];
        $rank = $_POST['rank'];
        $department = $_POST['department']; 
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($password !== $confirmPassword) {
            $message = "Error: Passwords do not match.";
        } else {
            $message = registerUser($firstName, $lastName, $middleName, $email, $address, $contactNo, $rank, $password, $department);
        }


    } elseif (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $message = loginUser($email, $password);
    }
}

function insertDepartment($departmentName)
{
    global $conn;

    $checkQuery = "SELECT * FROM departments WHERE department_name = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $departmentName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "Error: Department already exists.";
    }

    $insertQuery = "INSERT INTO departments (department_name) VALUES (?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("s", $departmentName);

    if ($stmt->execute()) {
        return "Department added successfully!";
    } else {
        return "Error: " . $stmt->error;
    }
}

// Fetch department list for the dropdown
function fetchDepartments()
{
    global $conn;
    $query = "SELECT id, department_name FROM departments";
    $result = $conn->query($query);

    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }

    return $departments;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="./assets/css/login.css">

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

</head>

<body>
    <div class="container">
        <div class="headerContainer">
            <div class="subHeaderContainer">
                <div class="logoContainer">
                    <img class="logo" src="./assets/img/CSSPE.png" alt="">
                </div>

                <div class="collegeNameContainer">
                    <p>CSSPE Inventory & Information System</p>
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
                    <form method="POST" action="">
                        <div class="subLoginContainer">
                            <div class="inputContainer">
                                <input class="inputEmail" type="email" name="email" placeholder="Email:">
                            </div>

                            <div class="inputContainer">
                                <input class="inputEmail" type="password" name="password" placeholder="Password:">
                            </div>

                            <div class="inputContainer">
                                <button type="submit" name="login" class="login">Login</button>
                            </div>
                        </div>
                    </form>

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

                <form method="POST" action="">

                    <div class="subLoginContainer">

                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="first_name" placeholder="First Name:" required>
                        </div>
                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="last_name" placeholder="Last Name:" required>
                        </div>
                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="middle_name" placeholder="Middle Name (Optional):">
                        </div>
                        <div class="inputContainer">
                            <input class="inputEmail" type="email" name="email" placeholder="Email:" required>
                        </div>
                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="address" placeholder="Address:" required>
                        </div>
                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="contact_no" placeholder="Contact No.:" required>
                        </div>
                        <div class="inputContainer" style="gap: 0.5rem;">
                        <select class="inputEmail" name="department" required>
                            <option value="">Choose a Department</option>
                            <?php
                            // Fetch and display departments
                            $departments = fetchDepartments();
                            foreach ($departments as $department) {
                                echo "<option value='" . $department['department_name'] . "'>" . $department['department_name'] . "</option>";
                            }
                            ?>
                        </select>

                        </div>
                        <div class="inputContainer" style="gap: 0.5rem;">
                            <select class="inputEmail" name="rank" required>
                                <option value="">Choose a rank</option>
                                <option value="Instructor">Instructor</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Professor">Professor</option>
                            </select>
                        </div>
                        <div class="inputContainer">
                            <input class="inputEmail" type="password" name="password" placeholder="Password:" required>
                        </div>
                        <div class="inputContainer">
                            <input class="inputEmail" type="password" name="confirm_password" placeholder="Confirm Password:" required>
                        </div>
                        <div class="inputContainer">
                            <button type="submit" name="register" class="login">Register</button>
                        </div>

                        <div class="registerLinkContainer">
                            <p>Already have an account? <span onclick="login()">Login</span></p>
                        </div>

                </form>

            </div>
        </div>
    </div>
    </div>

    <script src="./assets/js/login.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (!empty($message)) : ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: '<?= $message; ?>',
            });
        </script>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const message = <?php echo json_encode($message); ?>;
            if (message) {
                if (message.includes("Error: Please fill in all the fields.")) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message,
                    });
                } else if (message.includes("successful")) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: message,
                    });
                } else if (message.includes("Email already exists")) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Email',
                        text: message,
                    });
                } else if (message.includes("User with the same name already exists")) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Name',
                        text: message,
                    });
                } else if (message.includes("Error")) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message,
                    });
                }
            }
        });
    </script>



</body>

</html>