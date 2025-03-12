<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('information_admin');

$informationAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $informationAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $image = $row['image'];
} else {
    $fullName = "User Not Found";
}

$query = "SELECT id, first_name, last_name, middle_name, email, address, contact_no, rank, password, created_at, role, department, image 
          FROM users 
          WHERE role != 'super_admin'";
$result = mysqli_query($conn, $query);


// delete request
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM users WHERE id = $user_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Faculty member deleted successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error deleting faculty member: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
}

// ban request
if (isset($_GET['ban_id'])) {
    $user_id = $_GET['ban_id'];
    $ban_query = "UPDATE users SET ban = 1 WHERE id = ?";
    $stmt = $conn->prepare($ban_query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "User has been banned successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error banning user: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "Error preparing ban query: " . $conn->error;
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
    $insert_query = "INSERT INTO users (first_name, last_name, middle_name, email, address, contact_no, department, rank, password, image)
                     VALUES ('$first_name', '$last_name', '$middle_name', '$email', '$address', '$contact_no', '$department', '$rank', '$hashedPassword', '$image_path')";

    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['message'] = "New faculty member added successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: facultyMember.php");
        exit();
    } else {
        $_SESSION['message'] = "Error adding faculty member: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
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

    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $hashedPassword = $_POST['current_password'];
    }

    // Handle image upload for update
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
        // If no new image is uploaded, retain the current image
        $query_image = "SELECT image FROM users WHERE id = $faculty_id";
        $result_image = mysqli_query($conn, $query_image);
        $row = mysqli_fetch_assoc($result_image);
        $image_path = $row['image'];
    }

    $update_query = "UPDATE users 
                     SET first_name = '$first_name', last_name = '$last_name', middle_name = '$middle_name', email = '$email', address = '$address', contact_no = '$contact_no', department = '$department', rank = '$rank', password = '$hashedPassword', image = '$image_path' 
                     WHERE id = $faculty_id";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Faculty member updated successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error updating faculty member: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Members</title>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="../assets/css/output.css">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease forwards;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100 min-h-screen">
    <!-- Toggle Sidebar Button (mobile only) -->
    <button id="toggleSidebar" class="fixed top-4 left-4 z-50 lg:hidden bg-red-900 text-white p-2 rounded-md shadow-md flex items-center justify-center w-10 h-10 hover:bg-red-800 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden backdrop-blur-sm transition-opacity duration-300"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-red-900 text-white shadow-lg overflow-y-auto transition-transform duration-300 ease-in-out lg:translate-x-0 -translate-x-full z-40">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-red-800 flex items-center gap-3">
            <img src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover">
            <span class="font-bold truncate"><?php echo $fullName; ?></span>
        </div>

        <!-- Navigation Links -->
        <nav class="flex flex-col py-2">
            <a href="../homePage/" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-home w-5"></i> Home
            </a>
            <a href="../informationAdmin/" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-building w-5"></i> Departments
            </a>
            <a href="../informationAdmin/facultyMember.php" class="text-white py-3 px-4 bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-white">
                <i class="fas fa-user-tie w-5"></i> Faculty Members
            </a>
            <a href="../informationAdmin/organization.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-sitemap w-5"></i> Organizations
            </a>
            <a href="../informationAdmin/memorandum.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-file-alt w-5"></i> Memorandums
            </a>
            <a href="../informationAdmin/announcement.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-bullhorn w-5"></i> Announcements
            </a>
            <a href="../informationAdmin/events.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-calendar-alt w-5"></i> Events
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
            <div class="max-w-6xl mx-auto">
                <!-- Page Title -->
                <div class="flex items-center pb-4 mb-6 border-b border-gray-200">
                    <div class="flex items-center justify-center bg-gray-100 rounded-full p-3 mr-4 text-red-900">
                        <i class="fas fa-user-tie text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Faculty Members</h2>
                        <p class="text-sm text-gray-500 hidden md:block">Manage faculty information</p>
                    </div>
                </div>

                <!-- Search & Action Buttons -->
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <div class="relative flex-grow">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-red-900">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="search" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Search faculty members...">
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button onclick="printTable()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2 shadow-sm min-w-[120px] justify-center">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button onclick="addProgram()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2 shadow-sm min-w-[120px] justify-center">
                            <i class="fas fa-plus"></i> Add Faculty
                        </button>
                    </div>
                </div>

                <!-- Faculty Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="max-h-[475px] overflow-y-auto">
                        <table class="w-full min-w-[900px]">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="p-4 text-left font-semibold text-red-900">Fullname</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Image</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Email</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Address</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Contact Number</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Department</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Position</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-4"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                        <td class="p-4">
                                            <img class="w-14 h-14 rounded-full object-cover border border-gray-200"
                                                src="<?= '../assets/img/' . (empty($row['image']) ? 'CSSPE.png' : htmlspecialchars($row['image'])) ?>"
                                                alt="Faculty Image">
                                        </td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['address']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['rank']); ?></td>
                                        <td class="p-4">
                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <button onclick="editProgram(
                                                    <?php echo $row['id']; ?>,
                                                    '<?php echo addslashes('../assets/img/' . $row['image']); ?>',
                                                    '<?php echo addslashes($row['first_name']); ?>',
                                                    '<?php echo addslashes($row['middle_name']); ?>',
                                                    '<?php echo addslashes($row['last_name']); ?>',
                                                    '<?php echo addslashes($row['email']); ?>',
                                                    '<?php echo addslashes($row['password']); ?>',
                                                    '<?php echo addslashes($row['address']); ?>',
                                                    '<?php echo addslashes($row['contact_no']); ?>',
                                                    '<?php echo addslashes($row['department']); ?>',
                                                    '<?php echo addslashes($row['rank']); ?>')"
                                                    class="bg-red-900 hover:bg-red-800 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-24 flex items-center justify-center gap-1">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button onclick="deleteUser(<?php echo $row['id']; ?>)"
                                                    class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-24 flex items-center justify-center gap-1">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                                <button onclick="banUser(<?php echo $row['id']; ?>)"
                                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-24 flex items-center justify-center gap-1">
                                                    <i class="fas fa-ban"></i> Ban
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Faculty Modal -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div id="addContainer" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto animate-fadeIn">
                <div class="bg-red-900 text-white px-6 py-4 flex items-center gap-3 rounded-t-lg">
                    <i class="fas fa-user-plus text-xl"></i>
                    <h3 class="text-xl font-bold">Add Faculty Member</h3>
                </div>

                <div class="p-6">
                    <!-- Image Upload -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-full flex items-center justify-center mb-4 bg-gray-50 overflow-hidden">
                            <img id="preview" src="" alt="Image Preview" class="w-full h-full object-cover hidden">
                        </div>
                        <div class="w-full flex flex-col items-center">
                            <input type="file" id="imageUpload" name="profile_image" accept="image/*" class="hidden" onchange="previewImage()">
                            <button type="button" onclick="triggerImageUpload()"
                                class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                                <i class="fas fa-upload"></i> Upload Image
                            </button>
                        </div>
                    </div>

                    <!-- Form Fields - First Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="add_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="add_first_name" name="first_name" placeholder="Enter first name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        <div>
                            <label for="add_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="add_last_name" name="last_name" placeholder="Enter last name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>

                    <!-- Middle Name -->
                    <div class="mb-4">
                        <label for="add_middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name (Optional)</label>
                        <input type="text" id="add_middle_name" name="middle_name" placeholder="Enter middle name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Password -->
                    <div class="mb-4 relative">
                        <label for="add_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input type="password" id="add_password" name="password" placeholder="Enter password" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 pr-10">
                            <button type="button" id="toggleAddPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="add_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="add_email" name="email" placeholder="Enter email address" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label for="add_address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" id="add_address" name="address" placeholder="Enter address" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Contact Number -->
                    <div class="mb-4">
                        <label for="add_contact_no" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="text" id="add_contact_no" name="contact_no" placeholder="Enter contact number" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Department and Position -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="add_department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select id="add_department" name="department" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                                <option value="">Select Department</option>
                                <?php
                                $departments = fetchDepartments();
                                foreach ($departments as $department) {
                                    echo "<option value='" . htmlspecialchars($department['department_name']) . "'>" . htmlspecialchars($department['department_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="add_rank" class="block text-sm font-medium text-gray-700 mb-1">Position/Rank</label>
                            <select id="add_rank" name="rank" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                                <option value="">Select Position</option>
                                <option value="Instructor">Instructor</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Professor">Professor</option>
                            </select>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="cancelEdit()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="add_faculty"
                            class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-user-plus"></i> Add Faculty
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Edit Faculty Modal -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div id="editContainer" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto animate-fadeIn">
                <div class="bg-red-900 text-white px-6 py-4 flex items-center gap-3 rounded-t-lg">
                    <i class="fas fa-user-edit text-xl"></i>
                    <h3 class="text-xl font-bold">Edit Faculty Member</h3>
                </div>

                <div class="p-6">
                    <!-- Hidden input to store faculty id -->
                    <input type="hidden" name="faculty_id" id="faculty_id">

                    <!-- Image Upload -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-full flex items-center justify-center mb-4 bg-gray-50 overflow-hidden">
                            <img id="faculty_image" src="" alt="Image Preview" class="w-full h-full object-cover">
                        </div>
                        <div class="w-full">
                            <label for="edit_image_upload" class="block text-sm font-medium text-gray-700 mb-1">Update Profile Image</label>
                            <input type="file" id="edit_image_upload" name="faculty_image" accept="image/*" onchange="previewEditImage()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                        </div>
                    </div>

                    <!-- Form Fields - First Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="first_name" name="first_name" placeholder="Enter first name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="last_name" name="last_name" placeholder="Enter last name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>

                    <!-- Middle Name -->
                    <div class="mb-4">
                        <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name (Optional)</label>
                        <input type="text" id="middle_name" name="middle_name" placeholder="Enter middle name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Password -->
                    <div class="mb-4 relative">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 pr-10">
                            <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter email address" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" id="address" name="address" placeholder="Enter address" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Contact Number -->
                    <div class="mb-4">
                        <label for="contact_no" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="text" id="contact_no" name="contact_no" placeholder="Enter contact number" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Department and Position -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select id="department" name="department" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                                <option value="">Select Department</option>
                                <?php
                                $departments = fetchDepartments();
                                foreach ($departments as $department) {
                                    echo "<option value='" . htmlspecialchars($department['department_name']) . "'>" . htmlspecialchars($department['department_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="rank" class="block text-sm font-medium text-gray-700 mb-1">Position/Rank</label>
                            <select id="rank" name="rank" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                                <option value="">Select Position</option>
                                <option value="Instructor">Instructor</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Professor">Professor</option>
                            </select>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="cancelContainer()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="update_faculty"
                            class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- SweetAlert2 JavaScript -->
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

        // Toggle password visibility in edit form
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            // Toggle the password field type
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Toggle the icon class
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Toggle password visibility in add form
        const toggleAddPassword = document.getElementById('toggleAddPassword');
        const addPasswordField = document.getElementById('add_password');

        toggleAddPassword.addEventListener('click', function() {
            // Toggle the password field type
            const type = addPasswordField.type === 'password' ? 'text' : 'password';
            addPasswordField.type = type;

            // Toggle the icon class
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Toggle add faculty modal
        function addProgram() {
            document.getElementById('addContainer').classList.toggle('hidden');
        }

        // Cancel add faculty form
        function cancelEdit() {
            document.getElementById('addContainer').classList.add('hidden');
        }

        // Handle edit faculty modal
        function editProgram(id, image, first_name, middle_name, last_name, email, password, address, contact_no, department, rank) {
            document.getElementById('faculty_id').value = id;

            const imgElement = document.getElementById('faculty_image');
            imgElement.src = image;
            imgElement.style.display = 'block';

            document.getElementById('first_name').value = first_name;
            document.getElementById('last_name').value = last_name;
            document.getElementById('middle_name').value = middle_name;
            document.getElementById('password').value = password;
            document.getElementById('email').value = email;
            document.getElementById('address').value = address;
            document.getElementById('contact_no').value = contact_no;
            document.getElementById('department').value = department;
            document.getElementById('rank').value = rank;

            document.getElementById('editContainer').classList.remove('hidden');
        }

        // Close edit modal
        function cancelContainer() {
            document.getElementById('editContainer').classList.add('hidden');
        }

        // Trigger file upload dialog
        function triggerImageUpload() {
            document.getElementById('imageUpload').click();
        }

        // Preview image for add faculty
        function previewImage() {
            const file = document.getElementById('imageUpload').files[0];
            const preview = document.getElementById('preview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
            }
        }

        // Preview image for edit faculty
        function previewEditImage() {
            const file = document.getElementById('edit_image_upload').files[0];
            const preview = document.getElementById('faculty_image');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Delete faculty with confirmation
        function deleteUser(userId) {
            Swal.fire({
                title: 'Delete Faculty Member',
                text: 'Are you sure you want to delete this faculty member?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?delete_id=" + userId;
                }
            });
        }

        // Ban faculty from borrowing
        function banUser(userId) {
            Swal.fire({
                title: 'Ban from Borrowing',
                text: 'Are you sure you want to ban this faculty member from borrowing items?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ban!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?ban_id=" + userId;
                }
            });
        }

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                const fullname = row.cells[0].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                const department = row.cells[5].textContent.toLowerCase();
                const position = row.cells[6].textContent.toLowerCase();

                if (fullname.includes(searchText) || email.includes(searchText) ||
                    department.includes(searchText) || position.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Print table function
        function printTable() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');

            // Get the current date and time
            const now = new Date();
            const dateString = now.toLocaleDateString();
            const timeString = now.toLocaleTimeString();

            // Get table data
            const table = document.querySelector('table');

            // Create print content with Tailwind-inspired styling
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Faculty Members - CSSPE Inventory & Information System</title>
                    <style>
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            padding: 20px;
                            color: #333;
                        }
                        .print-header {
                            display: flex;
                            align-items: center;
                            margin-bottom: 20px;
                        }
                        .print-header img {
                            height: 60px;
                            margin-right: 20px;
                        }
                        .print-title {
                            flex: 1;
                        }
                        h1 {
                            color: #6B0D0D;
                            margin: 0;
                            font-size: 24px;
                        }
                        h2 {
                            color: #666;
                            margin: 5px 0 0;
                            font-size: 16px;
                            font-weight: normal;
                        }
                        .print-info {
                            margin-bottom: 20px;
                            text-align: right;
                            color: #666;
                            font-size: 14px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                        }
                        th, td {
                            border: 1px solid #e2e8f0;
                            padding: 12px;
                            text-align: left;
                        }
                        th {
                            background-color: #f8fafc;
                            color: #6B0D0D;
                            font-weight: 600;
                        }
                        tr:nth-child(even) {
                            background-color: #f9fafb;
                        }
                        img {
                            max-width: 40px;
                            max-height: 40px;
                            border-radius: 50%;
                            object-fit: cover;
                        }
                        .footer {
                            margin-top: 30px;
                            text-align: center;
                            font-size: 14px;
                            color: #6c757d;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <img src="../assets/img/CSSPE.png" alt="CSSPE Logo">
                        <div class="print-title">
                            <h1>CSSPE Inventory & Information System</h1>
                            <h2>Faculty Members List</h2>
                        </div>
                    </div>
                    <div class="print-info">
                        <p>Printed on ${dateString} at ${timeString}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Image</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Contact Number</th>
                                <th>Department</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody>
            `);

            // Add table rows
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.style.display !== 'none') { // Only print visible rows
                    printWindow.document.write('<tr>');

                    // Get all cells except the last one (action column)
                    for (let i = 0; i < row.cells.length - 1; i++) {
                        const cell = row.cells[i];

                        if (i === 1) { // Image cell
                            const img = cell.querySelector('img');
                            printWindow.document.write(`<td><img src="${img.src}" alt="Faculty Image"></td>`);
                        } else {
                            printWindow.document.write(`<td>${cell.textContent}</td>`);
                        }
                    }

                    printWindow.document.write('</tr>');
                }
            });

            // Close the HTML
            printWindow.document.write(`
                        </tbody>
                    </table>
                    <div class="footer">
                        &copy; ${new Date().getFullYear()} CSSPE Inventory & Information System
                    </div>
                </body>
                </html>
            `);

            // Trigger print
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                // printWindow.close();
            }, 500);
        }

        // Display SweetAlert messages (from PHP)
        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: "<?php echo $_SESSION['message_type']; ?>",
                title: "<?php echo $_SESSION['message']; ?>",
                confirmButtonColor: '#6B0D0D',
                showConfirmButton: false,
                timer: 3000
            });
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
    </script>
</body>

</html>