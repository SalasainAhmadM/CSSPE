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

$query = "SELECT * FROM departments";
$result = mysqli_query($conn, $query);

// Add Department
if (isset($_POST['add_department'])) {
    $department_name = mysqli_real_escape_string($conn, $_POST['department_name']);
    $department_description = mysqli_real_escape_string($conn, $_POST['department_description']);

    if (isset($_FILES['department_image']) && $_FILES['department_image']['error'] == 0) {
        $image_name = $_FILES['department_image']['name'];
        $image_tmp = $_FILES['department_image']['tmp_name'];
        $image_size = $_FILES['department_image']['size'];

        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $image_new_name = uniqid() . '.' . $image_ext;
        $image_path = "../assets/img/" . $image_new_name;

        if (in_array(strtolower($image_ext), ['jpg', 'jpeg', 'png', 'gif']) && $image_size < 5000000) {
            move_uploaded_file($image_tmp, $image_path);
        } else {
            $_SESSION['message'] = "Invalid image format or size!";
            $_SESSION['message_type'] = "error";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $image_path = "../assets/img/CSSPE.png";
    }

    $query = "INSERT INTO departments (department_name, description, image) 
              VALUES ('$department_name', '$department_description', '$image_path')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Department added successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Update Department
if (isset($_POST['update_department'])) {
    $department_id = $_POST['department_id'];
    $department_name = mysqli_real_escape_string($conn, $_POST['department_name']);
    $department_description = mysqli_real_escape_string($conn, $_POST['department_description']);

    if (isset($_FILES['department_image']) && $_FILES['department_image']['error'] == 0) {
        $image_name = $_FILES['department_image']['name'];
        $image_tmp = $_FILES['department_image']['tmp_name'];
        $image_size = $_FILES['department_image']['size'];

        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $image_new_name = uniqid() . '.' . $image_ext;
        $image_path = "../assets/img/" . $image_new_name;

        if (in_array(strtolower($image_ext), ['jpg', 'jpeg', 'png', 'gif']) && $image_size < 5000000) {
            move_uploaded_file($image_tmp, $image_path);
        } else {
            $_SESSION['message'] = "Invalid image format or size!";
            $_SESSION['message_type'] = "error";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $query_image = "SELECT image FROM departments WHERE id = $department_id";
        $result_image = mysqli_query($conn, $query_image);
        $row = mysqli_fetch_assoc($result_image);
        $image_path = $row['image'];
    }

    $update_query = "UPDATE departments 
                     SET department_name = '$department_name', description = '$department_description', image = '$image_path' 
                     WHERE id = $department_id";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Department updated successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Delete Department
if (isset($_GET['delete_id'])) {
    $department_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM departments WHERE id = $department_id";

    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Department deleted successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error deleting record: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments</title>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="../assets/css/output.css">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
            <a href="../informationAdmin/" class="text-white py-3 px-4 bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-white">
                <i class="fas fa-building w-5"></i> Departments
            </a>
            <a href="../informationAdmin/facultyMember.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
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
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Departments</h2>
                        <p class="text-sm text-gray-500 hidden md:block">Manage academic departments</p>
                    </div>
                </div>

                <!-- Search & Action Buttons -->
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <div class="relative flex-grow">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-red-900">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="search" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Search departments...">
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button onclick="printTable()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2 shadow-sm min-w-[120px] justify-center">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button onclick="addProgram()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2 shadow-sm min-w-[120px] justify-center">
                            <i class="fas fa-plus"></i> Add Department
                        </button>
                    </div>
                </div>

                <!-- Departments Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="max-h-[475px] overflow-y-auto">
                        <table id="departmentTable" class="w-full min-w-[640px]">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="p-4 text-left font-semibold text-red-900">Department Name</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Image</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Description</th>
                                    <th class="p-4 text-left font-semibold text-red-900">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-4"><?php echo htmlspecialchars($row['department_name']); ?></td>
                                        <td class="p-4">
                                            <img class="w-20 h-20 object-cover rounded-md border border-gray-200" src="<?php echo htmlspecialchars($row['image']); ?>" alt="Department Image">
                                        </td>
                                        <td class="p-4"><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td class="p-4">
                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <button onclick="editProgram(
                                                    <?php echo $row['id']; ?>, 
                                                    '<?php echo addslashes($row['image']); ?>', 
                                                    '<?php echo addslashes($row['department_name']); ?>', 
                                                    '<?php echo addslashes($row['description']); ?>')"
                                                    class="bg-red-900 hover:bg-red-800 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-24 flex items-center justify-center gap-1">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button onclick="deleteProgram(<?php echo $row['id']; ?>)"
                                                    class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-24 flex items-center justify-center gap-1">
                                                    <i class="fas fa-trash-alt"></i> Delete
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

    <!-- Edit Department Modal -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div id="editContainer" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto animate-fadeIn">
                <div class="bg-red-900 text-white px-6 py-4 flex items-center gap-3 rounded-t-lg">
                    <i class="fas fa-edit text-xl"></i>
                    <h3 class="text-xl font-bold">Edit Department</h3>
                </div>

                <div class="p-6">
                    <!-- Hidden input to store department id -->
                    <input type="hidden" name="department_id" id="department_id">

                    <!-- Image Upload -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center mb-4 bg-gray-50 overflow-hidden">
                            <img id="department_image" src="" alt="Image Preview" class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="w-full">
                            <label for="edit_image_upload" class="block text-sm font-medium text-gray-700 mb-1">Select New Image (Optional)</label>
                            <input type="file" name="department_image" id="edit_image_upload" accept="image/*" onchange="previewEditImage()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="mb-4">
                        <label for="department_name" class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                        <input type="text" id="department_name" name="department_name" placeholder="Enter department name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div class="mb-6">
                        <label for="department_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="department_description" name="department_description" placeholder="Enter department description" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 min-h-[120px] resize-y"></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="cancelContainer()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="update_department"
                            class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Add Department Modal -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div id="addContainer" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto animate-fadeIn">
                <div class="bg-red-900 text-white px-6 py-4 flex items-center gap-3 rounded-t-lg">
                    <i class="fas fa-plus-circle text-xl"></i>
                    <h3 class="text-xl font-bold">Add Department</h3>
                </div>

                <div class="p-6">
                    <!-- Image Upload -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center mb-4 bg-gray-50 overflow-hidden">
                            <img id="preview" src="" alt="Image Preview" class="max-w-full max-h-full object-contain hidden">
                        </div>
                        <div class="w-full">
                            <label for="imageUpload" class="block text-sm font-medium text-gray-700 mb-1">Department Image</label>
                            <input type="file" name="department_image" id="imageUpload" accept="image/*" onchange="previewImage()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="mb-4">
                        <label for="add_department_name" class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
                        <input type="text" id="add_department_name" name="department_name" placeholder="Enter department name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div class="mb-6">
                        <label for="add_department_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="add_department_description" name="department_description" placeholder="Enter department description" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 min-h-[120px] resize-y"></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="addProgram()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="add_department"
                            class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // For CSS animation
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .animate-fadeIn {
                    animation: fadeIn 0.3s ease forwards;
                }
            </style>
        `);

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

        // Toggle add department modal
        function addProgram() {
            const addContainer = document.getElementById('addContainer');
            addContainer.classList.toggle('hidden');
        }

        // Handle edit department modal
        function editProgram(id, image, name, description) {
            document.getElementById('department_id').value = id;

            const imgElement = document.getElementById('department_image');
            imgElement.src = image;
            imgElement.style.display = 'block';

            document.getElementById('department_name').value = name;
            document.getElementById('department_description').value = description;

            document.getElementById('editContainer').classList.remove('hidden');
        }

        // Close edit modal
        function cancelContainer() {
            document.getElementById('editContainer').classList.add('hidden');
        }

        // Preview image for add department
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

        // Preview image for edit department
        function previewEditImage() {
            const file = document.getElementById('edit_image_upload').files[0];
            const preview = document.getElementById('department_image');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Delete department with confirmation
        function deleteProgram(userId) {
            Swal.fire({
                title: 'Delete Department',
                text: 'Are you sure you want to delete this department?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?delete_id=" + userId;
                }
            });
        }

        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#departmentTable tbody tr');

            tableRows.forEach(row => {
                const departmentName = row.cells[0].textContent.toLowerCase();
                const description = row.cells[2].textContent.toLowerCase();

                if (departmentName.includes(searchText) || description.includes(searchText)) {
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
            const table = document.getElementById('departmentTable');

            // Create print content with Tailwind-inspired styling
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Departments - CSSPE Inventory & Information System</title>
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
                            max-width: 80px;
                            max-height: 80px;
                            border-radius: 4px;
                            border: 1px solid #e2e8f0;
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
                            <h2>Departments List</h2>
                        </div>
                    </div>
                    <div class="print-info">
                        <p>Printed on ${dateString} at ${timeString}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Department Name</th>
                                <th>Image</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
            `);

            // Add table rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.style.display !== 'none') { // Only print visible rows
                    const name = row.cells[0].textContent;
                    const imgSrc = row.querySelector('img').src;
                    const description = row.cells[2].textContent;

                    printWindow.document.write(`
                        <tr>
                            <td>${name}</td>
                            <td><img src="${imgSrc}" alt="Department Image"></td>
                            <td>${description}</td>
                        </tr>
                    `);
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