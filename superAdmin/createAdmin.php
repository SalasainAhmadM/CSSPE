<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

// validateSessionRole('super_admin');

function registerUser($firstName, $lastName, $middleName, $email, $address, $contactNo, $rank, $password, $role)
{
    global $conn;

    // Helper function to handle empty values
    function checkEmpty($value)
    {
        return empty(trim($value)) ? "N/A" : $value;
    }

    // Apply checkEmpty to all fields
    $firstName = checkEmpty($firstName);
    $lastName = checkEmpty($lastName);
    $middleName = checkEmpty($middleName);
    $email = checkEmpty($email);
    $address = checkEmpty($address);
    $contactNo = checkEmpty($contactNo);
    $rank = checkEmpty($rank);

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
    $defaultImage = "CSSPE.png"; // Default image value

    $insertQuery = "
        INSERT INTO users (first_name, last_name, middle_name, email, address, contact_no, rank, password, role, image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param(
        "ssssssssss",
        $firstName,
        $lastName,
        $middleName,
        $email,
        $address,
        $contactNo,
        $rank,
        $hashedPassword,
        $role,
        $defaultImage
    );

    if ($stmt->execute()) {
        return "Registration successful!";
    } else {
        return "Error: " . $stmt->error;
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
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $role = $_POST['role'];

        if (in_array($rank, ["Instructor", "Assistant Professor", "Associate Professor"])) {
            $role = "instructor";
        } else {
            $role = $_POST['role'];
        }

        if ($role === "information_admin") {
            $rank = "Information Admin";
        } elseif ($role === "inventory_admin") {
            $rank = "Inventory Admin";
        } elseif ($role === "super_admin") {
            $rank = "Super Admin";
        } else {
            $rank = $_POST['rank'];  
        }
        

        if ($password !== $confirmPassword) {
            $message = "Error: Passwords do not match.";
        } else {
            $message = registerUser($firstName, $lastName, $middleName, $email, $address, $contactNo, $rank, $password, $role);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>

    <link rel="stylesheet" href="../assets/css/createAdmin.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
                    <p class="text">Create Account</p>
                </div>

                <div class="createContainer">
                    <div class="subAddContainer">

                        <form method="POST" action="">
                            <div class="subLoginContainer">
                                
                                <div class="inputContainer">
                                    <input class="inputEmail" name="first_name" type="text" placeholder="First Name:" required>
                                </div>

                                <div class="inputContainer">
                                    <input class="inputEmail" name="last_name" type="text" placeholder="Last Name:" required>
                                </div>

                                <div class="inputContainer">
                                    <input class="inputEmail" name="middle_name" type="text" placeholder="Middle Name (Optional):" required>
                                </div>

                                <div class="inputContainer">
                                    <input class="inputEmail" name="email" type="email" placeholder="Email:" required>
                                </div>

                                <div class="inputContainer">
                                    <input class="inputEmail" name="address" type="text" placeholder="Address:" required>
                                </div>

                                <div class="inputContainer">
                                    <input class="inputEmail" name="contact_no" type="text" placeholder="Contact No.:" required>
                                </div>

                                <div class="inputContainer" style="gap: 0.5rem;">
                                    <select class="inputEmail" name="position" id="positionSelect" required>
                                        <option value="">Choose a position</option>
                                        <option value="Instructor">Instructor</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                </div>

                                <!-- Admin-specific fields -->
                                <div id="adminFields" style="display: none;">
                                    <div class="inputContainer" style="gap: 0.5rem;">
                                        <select class="inputEmail" name="role" id="roleSelect">
                                            <option value="">Choose an admin position</option>
                                            <option value="information_admin">Information Admin</option>
                                            <option value="inventory_admin">Inventory Admin</option>
                                            <option value="super_admin">Super Admin</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Instructor-specific fields -->
                                <div id="instructorFields" style="display: none;">
                                    <div class="inputContainer" style="gap: 0.5rem;">
                                        <select class="inputEmail" name="rank" id="rankSelect">
                                            <option value="">Choose a rank</option>
                                            <option value="Assistant Professor">Assistant Professor</option>
                                            <option value="Associate Professor">Associate Professor</option>
                                            <option value="Instructor">Instructor</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="inputContainer">
                                    <input class="inputEmail" name="password" type="password" placeholder="Password:" required>
                                </div>

                                <div class="inputContainer">
                                    <input class="inputEmail" name="confirm_password" type="password" placeholder="Confirm Password:" required>
                                </div>

                                <div class="inputContainer" style="gap: 0.5rem; justify-content: center; padding-right: 0.9rem;">
                                    <button class="addButton" name="register" style="width: 6rem;">Add</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const positionSelect = document.getElementById("positionSelect");
            const adminFields = document.getElementById("adminFields");
            const instructorFields = document.getElementById("instructorFields");
            const roleSelect = document.getElementById("roleSelect");
            const rankSelect = document.getElementById("rankSelect");

            // Handle position selection
            positionSelect.addEventListener("change", (e) => {
                const selectedPosition = e.target.value;

                // Reset required attributes
                roleSelect.removeAttribute('required');
                rankSelect.removeAttribute('required');

                if (selectedPosition === "Admin") {
                    adminFields.style.display = "block";
                    instructorFields.style.display = "none";
                    roleSelect.setAttribute('required', 'true');
                } else if (selectedPosition === "Instructor") {
                    adminFields.style.display = "none";
                    instructorFields.style.display = "block";
                    rankSelect.setAttribute('required', 'true');
                } else {
                    adminFields.style.display = "none";
                    instructorFields.style.display = "none";
                }
            });

            // Initial check to handle any preselected value
            positionSelect.dispatchEvent(new Event("change"));
        });
    </script>

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
                if (message.includes("successful")) {
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