<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('super_admin');
$userid = $_SESSION['user_id'];
$query = "SELECT first_name, middle_name, last_name, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $image = $row['image'];
} else {
    $fullName = "User Not Found";
}
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

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="../assets/css/output.css">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="font-sans antialiased bg-gray-50 min-h-screen">
    <!-- Toggle Sidebar Button (mobile only) -->
    <button id="toggleSidebar" class="fixed top-4 left-4 z-50 lg:hidden bg-red-900 text-white p-2 rounded-md shadow-md">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-red-900 text-white shadow-lg overflow-y-auto transition-transform duration-300 ease-in-out lg:translate-x-0 -translate-x-full z-40">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-red-800 flex items-center gap-3">
            <img src="../assets/img/CSSPE.png" alt="Logo" class="w-8 h-8 object-contain">
            <span class="font-bold"><?php echo $fullName; ?></span>
        </div>

        <!-- Navigation Links -->
        <nav class="flex flex-col py-2">
            <a href="../homePage/" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-home w-5"></i> Home
            </a>
            <a href="../superAdmin/" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-users w-5"></i> Accounts
            </a>
            <a href="../superAdmin/pendingAccount.php" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-user-clock w-5"></i> Pending Accounts
            </a>
            <a href="../superAdmin/createAdmin.php" class="text-white py-3 px-4 bg-red-800/40 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-user-plus w-5"></i> Create Account
            </a>
            <a href="../superAdmin/school_year.php" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-calendar-alt w-5"></i> School Year
            </a>
            <a href="../informationAdmin/" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-info-circle w-5"></i> Information Admin
            </a>
            <a href="../inventoryAdmin/" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-boxes w-5"></i> Inventory Admin
            </a>
        </nav>

        <!-- Logout Link -->
        <a href="../logout.php" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 absolute bottom-0 w-full border-t border-red-800 flex items-center gap-3">
            <i class="fas fa-sign-out-alt w-5"></i> Logout
        </a>
    </aside>

    <!-- Main Content -->
    <main id="mainContent" class="lg:ml-64 transition-all duration-300 ease-in-out min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-red-900 text-white p-4 flex items-center gap-3 shadow-md">
            <img src="../assets/img/CSSPE.png" alt="Logo" class="w-8 h-8 object-contain">
            <h1 class="text-xl font-bold">CSSPE Inventory & Information System</h1>
        </header>

        <!-- Page Content -->
        <div class="p-4 md:p-6 flex-grow flex justify-center">
            <div class="w-full max-w-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Create Account</h2>

                <form method="POST" action="" class="bg-white shadow-lg rounded-lg p-6 mb-4">
                    <div class="mb-4">
                        <input
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            name="first_name"
                            type="text"
                            placeholder="First Name:"
                            required>
                    </div>

                    <div class="mb-4">
                        <input
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            name="last_name"
                            type="text"
                            placeholder="Last Name:"
                            required>
                    </div>

                    <div class="mb-4">
                        <input
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            name="middle_name"
                            type="text"
                            placeholder="Middle Name (Optional):">
                    </div>

                    <div class="mb-4">
                        <input
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            name="email"
                            type="email"
                            placeholder="Email:"
                            required>
                    </div>

                    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            type="text"
                            name="address1"
                            placeholder="House Number, Street Name, Barangay"
                            required>
                        <input
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            type="text"
                            name="address2"
                            placeholder="Address:"
                            value=", Zamboanga City"
                            required>
                    </div>

                    <div class="mb-4">
                        <input
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            name="contact_no"
                            type="text"
                            placeholder="Contact No.:"
                            required>
                    </div>

                    <div class="mb-4">
                        <select
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white"
                            name="position"
                            id="positionSelect"
                            required>
                            <option value="">Choose a position</option>
                            <option value="Instructor">Instructor</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <!-- Admin-specific fields -->
                    <div id="adminFields" class="mb-4 hidden">
                        <select
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white"
                            name="role"
                            id="roleSelect">
                            <option value="">Choose an admin position</option>
                            <option value="information_admin">Information Admin</option>
                            <option value="inventory_admin">Inventory Admin</option>
                        </select>
                    </div>

                    <!-- Instructor-specific fields -->
                    <div id="instructorFields" class="mb-4 hidden">
                        <select
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white"
                            name="rank"
                            id="rankSelect">
                            <option value="">Choose a rank</option>
                            <option value="Assistant Professor">Assistant Professor</option>
                            <option value="Associate Professor">Associate Professor</option>
                            <option value="Instructor">Instructor</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <div class="relative">
                            <input
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                name="password"
                                id="password"
                                type="password"
                                placeholder="Password:"
                                required>
                            <i id="togglePassword" class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="relative">
                            <input
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                name="confirm_password"
                                id="confirmPassword"
                                type="password"
                                placeholder="Confirm Password:"
                                required>
                            <i id="toggleConfirmPassword" class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button
                            class="bg-red-900 hover:bg-red-800 text-white font-medium px-6 py-2 rounded-lg transition duration-200"
                            name="register">
                            Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');

            sidebar.classList.add('-translate-x-full');
            this.classList.add('hidden');
        });

        // Toggle password visibility
        function setupPasswordToggle(toggleId, passwordFieldId) {
            const toggleBtn = document.getElementById(toggleId);
            const passwordField = document.getElementById(passwordFieldId);

            if (toggleBtn && passwordField) {
                toggleBtn.addEventListener('click', function() {
                    const type = passwordField.type === 'password' ? 'text' : 'password';
                    passwordField.type = type;

                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            // Setup password toggles
            setupPasswordToggle('togglePassword', 'password');
            setupPasswordToggle('toggleConfirmPassword', 'confirmPassword');

            // Position selection handler
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
                    adminFields.classList.remove('hidden');
                    instructorFields.classList.add('hidden');
                    roleSelect.setAttribute('required', 'true');
                } else if (selectedPosition === "Instructor") {
                    adminFields.classList.add('hidden');
                    instructorFields.classList.remove('hidden');
                    rankSelect.setAttribute('required', 'true');
                } else {
                    adminFields.classList.add('hidden');
                    instructorFields.classList.add('hidden');
                }
            });

            // Initial check to handle any preselected value
            positionSelect.dispatchEvent(new Event("change"));

            // Display messages
            <?php if (isset($message)): ?>
                const message = <?php echo json_encode($message); ?>;
                if (message) {
                    if (message.includes("successful")) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: message,
                            confirmButtonColor: '#6B0D0D'
                        });
                    } else if (message.includes("Email already exists") ||
                        message.includes("User with the same name already exists") ||
                        message.includes("Error")) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                            confirmButtonColor: '#6B0D0D'
                        });
                    }
                }
            <?php endif; ?>
        });
    </script>
</body>

</html>