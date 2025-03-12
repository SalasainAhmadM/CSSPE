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
// Fetch data from the pending_users table
$query = "SELECT id, first_name, last_name, middle_name, email, address, contact_no, rank, password, created_at, role, department FROM pending_users";
$result = mysqli_query($conn, $query);

// delete request
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM pending_users WHERE id = $user_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error deleting user: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
}

// approve request
if (isset($_GET['approve_id'])) {
    $user_id = $_GET['approve_id'];
    $select_query = "SELECT * FROM pending_users WHERE id = $user_id";
    $result_select = mysqli_query($conn, $select_query);
    $pending_user = mysqli_fetch_assoc($result_select);

    $insert_query = "INSERT INTO users (first_name, last_name, middle_name, email, address, contact_no, rank, password, role, department) 
                     VALUES ('" . mysqli_real_escape_string($conn, $pending_user['first_name']) . "', 
                             '" . mysqli_real_escape_string($conn, $pending_user['last_name']) . "', 
                             '" . mysqli_real_escape_string($conn, $pending_user['middle_name']) . "', 
                             '" . mysqli_real_escape_string($conn, $pending_user['email']) . "', 
                             '" . mysqli_real_escape_string($conn, $pending_user['address']) . "', 
                             '" . mysqli_real_escape_string($conn, $pending_user['contact_no']) . "', 
                             '" . mysqli_real_escape_string($conn, $pending_user['rank']) . "', 
                             '" . mysqli_real_escape_string($conn, $pending_user['password']) . "', 
                             '" . mysqli_real_escape_string($conn, $pending_user['role']) . "',
                             '" . mysqli_real_escape_string($conn, $pending_user['department']) . "')";

    if (mysqli_query($conn, $insert_query)) {
        $delete_query = "DELETE FROM pending_users WHERE id = $user_id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['message'] = "User approved successfully!";
            $_SESSION['message_type'] = "success";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['message'] = "Error deleting record after approval: " . mysqli_error($conn);
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Error inserting into users table: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Accounts</title>

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
            <a href="../superAdmin/pendingAccount.php" class="text-white py-3 px-4 bg-red-800/40 transition-colors duration-200 flex items-center gap-3">
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Pending Accounts</h2>

            <!-- Search Bar -->
            <div class="mb-6">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchBar" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Search by name or email...">
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table id="dataTable" class="w-full min-w-[640px]">
                    <thead class="bg-red-900 text-white sticky top-0">
                        <tr>
                            <th class="p-3 text-left">Fullname</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left hidden md:table-cell">Address</th>
                            <th class="p-3 text-left hidden sm:table-cell">Contact Number</th>
                            <th class="p-3 text-left hidden md:table-cell">Department</th>
                            <th class="p-3 text-left hidden sm:table-cell">Position</th>
                            <th class="p-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="p-3"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="p-3 hidden md:table-cell"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td class="p-3 hidden sm:table-cell"><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                <td class="p-3 hidden md:table-cell"><?php echo htmlspecialchars($row['department']); ?></td>
                                <td class="p-3 hidden sm:table-cell"><?php echo htmlspecialchars($row['rank']); ?></td>
                                <td class="p-3">
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <button onclick="approveUser(<?php echo $row['id']; ?>)"
                                            class="bg-red-900 hover:bg-red-800 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-auto">
                                            Approve
                                        </button>
                                        <button onclick="deleteUser(<?php echo $row['id']; ?>)"
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-auto">
                                            Delete
                                        </button>
                                        <button onclick="viewDetails(<?php echo $row['id']; ?>)"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:hidden">
                                            View Details
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

    <!-- User Details Modal (for mobile view) -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-red-900 text-white rounded-t-lg">
                <h3 class="text-xl font-bold">User Details</h3>
                <button type="button" onclick="closeDetailsModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4" id="userDetailsContent">
                <!-- Content will be populated dynamically -->
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end">
                <button onclick="closeDetailsModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

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

        function approveUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to approve this user?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve!',
                cancelButtonText: 'No, cancel',
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?approve_id=" + userId;
                }
            });
        }

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

        // Search functionality
        document.getElementById('searchBar').addEventListener('keyup', function() {
            const searchQuery = this.value.toLowerCase();
            const rows = document.querySelectorAll('#dataTable tbody tr');

            rows.forEach(row => {
                const fullName = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                if (fullName.includes(searchQuery) || email.includes(searchQuery)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // View user details on mobile (alternative to seeing hidden columns)
        function viewDetails(userId) {
            const detailsModal = document.getElementById('detailsModal');
            const content = document.getElementById('userDetailsContent');

            // Find the row with the matching user ID
            const row = document.querySelector(`tr[data-id="${userId}"]`) ||
                document.querySelector(`button[onclick*="viewDetails(${userId})"]`).closest('tr');

            if (row) {
                const name = row.cells[0].textContent;
                const email = row.cells[1].textContent;
                const address = row.querySelector('td:nth-child(3)').textContent;
                const contact = row.querySelector('td:nth-child(4)').textContent;
                const department = row.querySelector('td:nth-child(5)').textContent;
                const position = row.querySelector('td:nth-child(6)').textContent;

                // Populate modal with user details
                content.innerHTML = `
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="font-medium">${name}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium">${email}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Address</p>
                            <p class="font-medium">${address}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Contact Number</p>
                            <p class="font-medium">${contact}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Department</p>
                            <p class="font-medium">${department}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Position</p>
                            <p class="font-medium">${position}</p>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `<p class="text-center text-gray-500">User details not found</p>`;
            }

            detailsModal.classList.remove('hidden');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
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