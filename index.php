<?php
session_start();
require_once './conn/conn.php';


function deactivateUsers()
{
    global $conn;

    $currentDate = new DateTime();
    $day = $currentDate->format('d');
    $month = $currentDate->format('m');
    $year = $currentDate->format('Y');
    $time = $currentDate->format('H:i:s');
    $logDate = $currentDate->format('Y-m-d');

    // Deactivation date
    if ($day == '24' && $month == '05') {

        $query = "SELECT deactivation_triggered FROM config WHERE year = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['deactivation_triggered'] == 1) {
                "Deactivation has already been triggered this year.";
                return;
            }
        }

        // Deactivate all users except those with 'super_admin', 'inventory_admin', 'information_admin' in their role
        $updateQuery = "UPDATE users SET status = 1 WHERE status != 1 AND role NOT LIKE '%super_admin%' AND role NOT LIKE '%inventory_admin%' AND role NOT LIKE '%information_admin%'";
        if ($conn->query($updateQuery) === TRUE) {

            $logMessage = "Deactivated all user accounts on $logDate at $time (Day: $day, Month: $month, Year: $year).";
            $logQuery = "INSERT INTO deactivation_logs (message, log_date) VALUES (?, ?)";
            $logStmt = $conn->prepare($logQuery);
            $logStmt->bind_param("ss", $logMessage, $logDate);
            $logStmt->execute();

            // Update the config table to mark the deactivation as triggered for this year
            $updateConfigQuery = "INSERT INTO config (year, deactivation_triggered) VALUES (?, 1) ON DUPLICATE KEY UPDATE deactivation_triggered = 1";
            $updateConfigStmt = $conn->prepare($updateConfigQuery);
            $updateConfigStmt->bind_param("i", $year);
            $updateConfigStmt->execute();

            echo "All user accounts have been deactivated for the year, except admins.";
        } else {
            echo "Error deactivating users: " . $conn->error;
        }
    } else {
        "Today is not the deactivation date, so no action has been taken.";
    }
}


deactivateUsers();



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

    // Check overdue items and create notifications
    $overdueQuery = "
        SELECT 
            it.transaction_id, 
            it.quantity_borrowed, 
            it.return_date, 
            i.name AS item_name 
        FROM 
            item_transactions it
        INNER JOIN 
            items i ON it.item_id = i.id
        WHERE 
            it.return_date < CURDATE() 
            AND it.status != 'Returned'
    ";
    $overdueResult = $conn->query($overdueQuery);

    if ($overdueResult && $overdueResult->num_rows > 0) {
        while ($overdue = $overdueResult->fetch_assoc()) {
            $description = "Item {$overdue['item_name']} with quantity {$overdue['quantity_borrowed']} is overdue.";
            $notifQuery = "INSERT INTO notif_items (description) VALUES (?)";
            $notifStmt = $conn->prepare($notifQuery);
            $notifStmt->bind_param("s", $description);
            $notifStmt->execute();
        }
    }


    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the account is deactivated
        if ($user['status'] == 1) {
            echo "<script>
                            alert('Your account is deactivated! Please contact support for more details.');
                          </script>";
            return;
        }

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
        $address1 = $_POST['address1'];
        $address2 = $_POST['address2'];
        $address = $address1 . '' . $address2;  // Concatenate addresses
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
    <title>CSSPE Inventory & Info System</title>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="./assets/css/output.css">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="min-h-screen">
    <!-- Header Bar -->
    <header class="bg-red-900 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex flex-wrap justify-between items-center">
            <div class="flex items-center space-x-3 mb-2 md:mb-0">
                <div class="flex items-center">
                    <img class="h-10 sm:h-12 w-auto" src="./assets/img/CSSPE.png" alt="CSSPE Logo">
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-bold">CSSPE Inventory & Info System</h1>
                    <p class="text-xs sm:text-sm">College of Sport Science and Physical Education</p>
                </div>
            </div>
            <div>
                <a href="#" id="registerLink" onclick="toggleRegisterForm()" class="text-white font-medium hover:underline">
                    REGISTER
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content with Background Image -->
    <main class="min-h-[calc(100vh-64px)] flex items-center justify-center py-10 px-4" style="background: url(./assets/img/gym.jpg); background-size: cover; background-position: center;">
        <!-- Login Form -->
        <div class="w-full max-w-md">
            <div class="bg-white bg-opacity-95 rounded-lg shadow-xl overflow-hidden">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Login</h2>

                    <form method="POST" action="" class="space-y-4">
                        <div>
                            <input
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                type="email"
                                name="email"
                                placeholder="Email"
                                required>
                        </div>

                        <div class="relative">
                            <input
                                id="passwordField"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                type="password"
                                name="password"
                                placeholder="Password"
                                required>
                            <i id="togglePassword" class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                        </div>

                        <div>
                            <button
                                type="submit"
                                name="login"
                                class="w-full bg-red-800 hover:bg-red-900 text-white font-medium py-3 px-4 rounded-lg transition duration-300">
                                Login
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-gray-600">
                            Don't have an account?
                            <a href="#" onclick="toggleRegisterForm()" class="text-red-800 hover:text-red-900 font-medium">
                                Register
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-red-900 text-white py-6 md:py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <!-- University Info -->
                <div class="flex flex-col">
                    <div class="flex items-center mb-3">
                        <img class="h-10 w-auto mr-2" src="./assets/img/CSSPE.png" alt="WMSU Logo">
                        <div>
                            <h3 class="text-sm md:text-base font-bold">Western Mindanao State University</h3>
                            <p class="text-xs md:text-sm">College Of Sport Science And Physical Education</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="text-xs md:text-sm">
                    <p>Normal Road, Baliwasan, Zamboanga City, Philippines</p>
                    <p>Wmsu CSSPE</p>
                    <p>wmsu@wmsu.edu.ph</p>
                    <p>991-1771</p>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <ul class="space-y-1 text-xs md:text-sm">
                            <li><a href="#" class="hover:underline">CSSPE Goals</a></li>
                            <li><a href="#" class="hover:underline">Quality Policy</a></li>
                            <li><a href="#" class="hover:underline">Events</a></li>
                            <li><a href="#" class="hover:underline">Articles</a></li>
                            <li><a href="#" class="hover:underline">Memorandums</a></li>
                            <li><a href="#" class="hover:underline">Departments</a></li>
                        </ul>
                    </div>
                    <div>
                        <ul class="space-y-1 text-xs md:text-sm">
                            <li><a href="#" class="hover:underline">Organizations</a></li>
                            <li><a href="#" class="hover:underline">Inventory</a></li>
                            <li><a href="#" class="hover:underline">Teachers</a></li>
                            <li><a href="#" class="hover:underline">Privacy Policy</a></li>
                            <li><a href="#" class="hover:underline">Terms of Service</a></li>
                            <li><a href="#" class="hover:underline">About</a></li>
                            <li><a href="#" class="hover:underline">Contact</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Registration Form (Hidden by Default) -->
    <div id="registerContainer" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-4 border-b">
                <h2 class="text-xl md:text-2xl font-bold text-red-800">Register</h2>
                <button onclick="toggleRegisterForm()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4">
                <form method="POST" action="" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                type="text" name="first_name" placeholder="First Name:" required>
                        </div>
                        <div>
                            <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                type="text" name="last_name" placeholder="Last Name:" required>
                        </div>
                    </div>

                    <div>
                        <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            type="text" name="middle_name" placeholder="Middle Name (Optional):">
                    </div>

                    <div>
                        <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            type="email" name="email" placeholder="Email:" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            type="text" name="address1" placeholder="House Number, Street Name, Barangay" required>
                        <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            type="text" name="address2" placeholder="Address:" value=", Zamboanga City" required>
                    </div>

                    <div>
                        <input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            type="text" name="contact_no" placeholder="Contact No.:" required>
                    </div>

                    <div>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            name="department" required>
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

                    <div>
                        <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            name="rank" required>
                            <option value="">Choose a rank</option>
                            <option value="Instructor">Instructor</option>
                            <option value="Assistant Professor">Assistant Professor</option>
                            <option value="Associate Professor">Associate Professor</option>
                            <option value="Professor">Professor</option>
                        </select>
                    </div>

                    <div class="relative">
                        <input id="registerPassword"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            type="password" name="password" placeholder="Password:" required>
                        <i id="toggleRegisterPassword" class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                    </div>

                    <div class="relative">
                        <input id="confirmPassword"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            type="password" name="confirm_password" placeholder="Confirm Password:" required>
                        <i id="toggleConfirmPassword" class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                    </div>

                    <div>
                        <button type="submit" name="register"
                            class="w-full bg-red-800 hover:bg-red-900 text-white font-medium py-3 px-4 rounded-lg transition duration-300">
                            Register
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="text-gray-600">Already have an account?
                        <span onclick="toggleRegisterForm()" class="text-red-800 hover:text-red-900 cursor-pointer font-medium">Login</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="./assets/js/login.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (!empty($message)): ?>
        <script>
            Swal.fire({
                icon: '<?php echo (strpos($message, "successful") !== false) ? "success" : "error"; ?>',
                title: '<?php echo (strpos($message, "successful") !== false) ? "Success" : "Error"; ?>',
                text: '<?= $message; ?>',
            });
        </script>
    <?php endif; ?>

    <script>
        // Function to toggle password visibility
        function setupPasswordToggle(toggleId, passwordFieldId) {
            const toggleIcon = document.getElementById(toggleId);
            const passwordField = document.getElementById(passwordFieldId);

            if (!toggleIcon || !passwordField) return;

            toggleIcon.addEventListener("click", function() {
                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    toggleIcon.classList.remove("fa-eye");
                    toggleIcon.classList.add("fa-eye-slash");
                } else {
                    passwordField.type = "password";
                    toggleIcon.classList.remove("fa-eye-slash");
                    toggleIcon.classList.add("fa-eye");
                }
            });
        }

        // Function to toggle registration form
        function toggleRegisterForm() {
            const registerContainer = document.getElementById('registerContainer');
            registerContainer.classList.toggle('hidden');
        }

        // Initialize password toggles when the DOM is loaded
        document.addEventListener("DOMContentLoaded", () => {
            // Apply to login and register fields
            setupPasswordToggle("togglePassword", "passwordField");
            setupPasswordToggle("toggleRegisterPassword", "registerPassword");
            setupPasswordToggle("toggleConfirmPassword", "confirmPassword");

            // Handle messages
            const message = <?php echo json_encode($message); ?>;
            if (message) {
                let icon = 'error';
                let title = 'Error';

                if (message.includes("successful")) {
                    icon = 'success';
                    title = 'Success';
                }

                Swal.fire({
                    icon: icon,
                    title: title,
                    text: message,
                });
            }
        });
    </script>
</body>

</html>