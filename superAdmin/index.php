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
// Fetch data from the users table, excluding the super_admin role
$query = "SELECT id, first_name, last_name, middle_name, email, address, contact_no, rank, status, password, created_at, role, department, image 
          FROM users 
          WHERE role != 'super_admin'";
$result = mysqli_query($conn, $query);


// Delete request
if (isset($_GET['delete_id'])) {
    $user_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM users WHERE id = $user_id";

    if (mysqli_query($conn, $delete_query)) {

        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {

        $_SESSION['message'] = "Error deleting record: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

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


if (isset($_POST['add_faculty'])) {

    // Function to handle empty values
    function checkEmpty($value)
    {
        return empty(trim($value)) ? "N/A" : mysqli_real_escape_string($GLOBALS['conn'], $value);
    }

    // Process form data
    $first_name = checkEmpty($_POST['first_name']);
    $last_name = checkEmpty($_POST['last_name']);
    $middle_name = checkEmpty($_POST['middle_name']);
    $email = checkEmpty($_POST['email']);
    $address = checkEmpty($_POST['address']);
    $contact_no = checkEmpty($_POST['contact_no']);
    $department = checkEmpty($_POST['department']);
    $rank = checkEmpty($_POST['rank']);
    $status = 0;

    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $image = $_FILES['profile_image'];
        $image_name = basename($image['name']);
        $image_tmp_name = $image['tmp_name'];
        $image_size = $image['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($image_ext), $allowed_ext) && $image_size < 5000000) {
            $new_image_name = uniqid() . '.' . $image_ext;
            $image_path = '../assets/img/' . $new_image_name;

            // Move uploaded image to the directory
            if (move_uploaded_file($image_tmp_name, $image_path)) {
                $image_path = $new_image_name;
            } else {
                echo "Error uploading the image.";
                $image_path = 'CSSPE.png'; // default image
            }
        } else {
            $image_path = 'CSSPE.png';
        }
    } else {
        $image_path = 'CSSPE.png';
    }

    // Insert user data into the database
    $insert_query = "INSERT INTO users (first_name, last_name, middle_name, email, address, contact_no, department, rank, status, password, image)
                     VALUES ('$first_name', '$last_name', '$middle_name', '$email', '$address', '$contact_no', '$department', '$rank', '$status', '$hashedPassword', '$image_path')";

    if (mysqli_query($conn, $insert_query)) {
        echo "New user added successfully!";
        header("Location: facultyMember.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}



if (isset($_POST['update_faculty'])) {
    $faculty_id = $_POST['faculty_id'];

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $rank = mysqli_real_escape_string($conn, $_POST['rank']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $hashedPassword = $_POST['current_password'];
    }

    // Handle image upload
    if (isset($_FILES['faculty_image']) && $_FILES['faculty_image']['error'] == 0) {
        $image = $_FILES['faculty_image'];
        $image_name = basename($image['name']);
        $image_tmp_name = $image['tmp_name'];
        $image_size = $image['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($image_ext), $allowed_ext) && $image_size < 5000000) {
            $new_image_name = uniqid() . '.' . $image_ext;
            $image_path = '../assets/img/' . $new_image_name;

            if (move_uploaded_file($image_tmp_name, $image_path)) {
                $image_path = $new_image_name;
            } else {
                $image_path = 'CSSPE.png'; // Default image
            }
        } else {
            $image_path = 'CSSPE.png';
        }
    } else {
        $query_image = "SELECT image FROM users WHERE id = $faculty_id";
        $result_image = mysqli_query($conn, $query_image);
        $row = mysqli_fetch_assoc($result_image);
        $image_path = $row['image'];
    }

    $update_query = "UPDATE users 
                     SET first_name = '$first_name', last_name = '$last_name', middle_name = '$middle_name', 
                         email = '$email', address = '$address', contact_no = '$contact_no', 
                         department = '$department', rank = '$rank', status = '$status', 
                         password = '$hashedPassword', image = '$image_path' 
                     WHERE id = $faculty_id";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Faculty member updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Member</title>

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
            <a href="../superAdmin/" class="text-white py-3 px-4 bg-red-800/40 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-users w-5"></i> Accounts
            </a>
            <a href="../superAdmin/pendingAccount.php" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-user-clock w-5"></i> Pending Accounts
            </a>
            <a href="../superAdmin/createAdmin.php" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
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
        <div class="p-4 md:p-6 flex-grow">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Faculty Members</h2>

            <!-- Search and Actions -->
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="search" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Search faculty members...">
                </div>
                <div class="flex flex-wrap gap-3">
                    <button onclick="printTable()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <select id="roleFilter" onchange="filterByRole()" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                        <option value="">Choose Position</option>
                        <option value="instructor">Instructor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="w-full min-w-[640px]">
                    <thead class="bg-red-900 text-white sticky top-0">
                        <tr>
                            <th class="p-3 text-left">Fullname</th>
                            <th class="p-3 text-left">Image</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left">Address</th>
                            <th class="p-3 text-left">Contact Number</th>
                            <th class="p-3 text-left">Department</th>
                            <th class="p-3 text-left">Position</th>
                            <th class="p-3 text-left">Role</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="p-3"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                <td class="p-3">
                                    <img class="w-12 h-12 object-cover rounded-md"
                                        src="<?= '../assets/img/' . (!empty($row['image']) ? htmlspecialchars($row['image']) : 'CSSPE.png') ?>"
                                        alt="User Image">
                                </td>
                                <td class="p-3"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['department']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['rank']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['role']); ?></td>
                                <td class="p-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $row['status'] == 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo htmlspecialchars($row['status'] == 0 ? 'Activated' : 'Deactivated'); ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-wrap gap-2">
                                        <button onclick="editProgram(<?php echo $row['id']; ?>,
                                            '<?php echo addslashes('../assets/img/' . $row['image']); ?>',
                                            '<?php echo addslashes($row['first_name']); ?>',
                                            '<?php echo addslashes($row['middle_name']); ?>',
                                            '<?php echo addslashes($row['last_name']); ?>',
                                            '<?php echo addslashes($row['email']); ?>',
                                            '<?php echo addslashes($row['address']); ?>',
                                            '<?php echo addslashes($row['contact_no']); ?>',
                                            '<?php echo addslashes($row['department']); ?>',
                                            '<?php echo addslashes($row['rank']); ?>',
                                            '<?php echo addslashes($row['status']); ?>')"
                                            class="bg-red-900 hover:bg-red-800 text-white px-3 py-1 rounded transition duration-200 text-sm">
                                            Edit
                                        </button>
                                        <button onclick="deleteUser(<?php echo $row['id']; ?>)"
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded transition duration-200 text-sm">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit Faculty Modal -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-red-900 text-white rounded-t-lg">
                    <h3 class="text-xl font-bold">Edit Faculty Member Information</h3>
                    <button type="button" onclick="cancelContainer()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Hidden input to store faculty id -->
                    <input type="hidden" name="faculty_id" id="faculty_id">

                    <!-- Image Upload -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center mb-4 bg-gray-50 overflow-hidden">
                            <img id="faculty_image" src="../assets/img/CSSPE.png" class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="w-full max-w-xs">
                            <input type="file" name="faculty_image" id="imageUpload" accept="image/*" onchange="previewImage()"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-red-900 file:text-white hover:file:bg-red-800">
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <input name="first_name" id="first_name" type="text" placeholder="First Name:"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        <div>
                            <input name="last_name" id="last_name" type="text" placeholder="Last Name:"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>

                    <div class="mb-4">
                        <input name="middle_name" id="middle_name" type="text" placeholder="Middle Name (Optional):"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div class="mb-4 relative">
                        <input name="password" id="password" type="password" placeholder="Password:"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <i id="togglePassword" class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                    </div>

                    <div class="mb-4">
                        <input name="email" id="email" type="email" placeholder="Email:"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div class="mb-4">
                        <input name="address" id="address" type="text" placeholder="Address:"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div class="mb-4">
                        <input name="contact_no" id="contact_no" type="text" placeholder="Contact No.:"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div class="mb-4">
                        <select name="department" id="department"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                            <option value="">Choose a Department</option>
                            <?php
                            $departments = fetchDepartments();
                            foreach ($departments as $department) {
                                echo "<option value='" . $department['department_name'] . "'>" . $department['department_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <select name="rank" id="rank"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                            <option value="">Choose a Rank</option>
                            <option value="Instructor">Instructor</option>
                            <option value="Assistant Professor">Assistant Professor</option>
                            <option value="Associate Professor">Associate Professor</option>
                            <option value="Professor">Professor</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <select name="status" id="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                            <option value="0">Activated</option>
                            <option value="1">Deactivated</option>
                        </select>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="submit" name="update_faculty"
                            class="bg-red-900 hover:bg-red-800 text-white font-medium px-4 py-2 rounded-lg transition duration-200">
                            Save Changes
                        </button>
                        <button type="button" onclick="cancelContainer()"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-medium px-4 py-2 rounded-lg transition duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('lg:translate-x-0');
            mainContent.classList.toggle('lg:ml-64');
        });

        // JavaScript to toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            // Toggle the password field type
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Toggle the icon class
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        function filterByRole() {
            const selectedRole = document.getElementById('roleFilter').value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach((row) => {
                const role = row.querySelector('td:nth-child(8)').textContent.trim().toLowerCase();

                if (
                    selectedRole === "" || // Show all if no role is selected
                    (selectedRole === "instructor" && role.includes("instructor")) || // Match "instructor"
                    (selectedRole === "admin" &&
                        (role.includes("information_admin") ||
                            role.includes("super_admin") ||
                            role.includes("inventory_admin"))
                    ) // Match any admin type
                ) {
                    row.style.display = ""; // Show matching rows
                } else {
                    row.style.display = "none"; // Hide non-matching rows
                }
            });
        }

        function editProgram(id, image, first_name, middle_name, last_name, email, address, contact_no, department, rank, status) {
            const defaultImage = '../assets/img/CSSPE.png';
            document.getElementById('faculty_id').value = id;

            // Use default image if the provided image is empty
            document.getElementById('faculty_image').src = image && image.trim() !== '' ? image : defaultImage;

            document.getElementById('first_name').value = first_name;
            document.getElementById('last_name').value = last_name;
            document.getElementById('middle_name').value = middle_name;
            document.getElementById('email').value = email;
            document.getElementById('address').value = address;
            document.getElementById('contact_no').value = contact_no;
            document.getElementById('department').value = department;
            document.getElementById('rank').value = rank;
            document.getElementById('status').value = status;

            document.getElementById('editModal').style.display = 'flex';
        }

        function cancelContainer() {
            document.getElementById('editModal').style.display = 'none';
        }

        function previewImage() {
            const input = document.getElementById('imageUpload');
            const image = document.getElementById('faculty_image');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    image.src = e.target.result;
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        // Search functionality
        document.getElementById('search').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Function to confirm user deletion
        function deleteUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete this user?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'No, cancel',
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?delete_id=" + userId;
                }
            });
        }

        // Print table functionality
        function printTable() {
            const printContents = document.querySelector('table').outerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = `
                <div class="p-8">
                    <h1 class="text-3xl font-bold text-center mb-6">Faculty Members</h1>
                    ${printContents}
                </div>
            `;

            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        // Display messages if available
        document.addEventListener("DOMContentLoaded", function() {
            <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
                Swal.fire({
                    icon: "<?php echo $_SESSION['message_type']; ?>",
                    title: "<?php echo $_SESSION['message']; ?>",
                    showConfirmButton: false,
                    timer: 3000,
                    confirmButtonColor: '#6B0D0D'
                });
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>