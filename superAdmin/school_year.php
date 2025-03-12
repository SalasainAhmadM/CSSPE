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

// Fetch school year values
$schoolYearQuery = "SELECT * FROM school_years ORDER BY start_date DESC";
$schoolYearResult = $conn->query($schoolYearQuery);

// Prepare school year data for display
$schoolYears = [];
if ($schoolYearResult->num_rows > 0) {
    while ($row = $schoolYearResult->fetch_assoc()) {
        $schoolYears[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Year</title>

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
            <a href="../superAdmin/createAdmin.php" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 flex items-center gap-3">
                <i class="fas fa-user-plus w-5"></i> Create Account
            </a>
            <a href="../superAdmin/school_year.php" class="text-white py-3 px-4 bg-red-800/40 transition-colors duration-200 flex items-center gap-3">
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6">School Year</h2>

            <!-- Search and Actions -->
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchBar" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Search by school year or semester..." oninput="filterTable()">
                </div>
                <div class="flex flex-wrap gap-3">
                    <button onclick="printTable()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button onclick="showAddSchoolYearModal()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                        <i class="fas fa-plus"></i> Add School Year
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table id="dataTable" class="w-full min-w-[640px]">
                    <thead class="bg-red-900 text-white sticky top-0">
                        <tr>
                            <th class="p-3 text-left">School Year</th>
                            <th class="p-3 text-left">Semester</th>
                            <th class="p-3 text-left hidden sm:table-cell">Start Date</th>
                            <th class="p-3 text-left hidden sm:table-cell">End Date</th>
                            <th class="p-3 text-left hidden md:table-cell">Created At</th>
                            <th class="p-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($schoolYears) > 0): ?>
                            <?php foreach ($schoolYears as $sy): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="p-3"><?php echo htmlspecialchars($sy['school_year']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($sy['semester']); ?></td>
                                    <td class="p-3 hidden sm:table-cell"><?php echo htmlspecialchars($sy['start_date']); ?></td>
                                    <td class="p-3 hidden sm:table-cell"><?php echo htmlspecialchars($sy['end_date']); ?></td>
                                    <td class="p-3 hidden md:table-cell"><?php echo htmlspecialchars($sy['created_at']); ?></td>
                                    <td class="p-3">
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <button onclick="editSchoolYear(<?php echo $sy['id']; ?>)"
                                                class="bg-red-900 hover:bg-red-800 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-auto flex items-center justify-center gap-1">
                                                <i class="fas fa-edit"></i> <span class="sm:inline">Edit</span>
                                            </button>
                                            <button onclick="deleteSchoolYear(<?php echo $sy['id']; ?>)"
                                                class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:w-auto flex items-center justify-center gap-1">
                                                <i class="fas fa-trash"></i> <span class="sm:inline">Delete</span>
                                            </button>
                                            <button onclick="viewDetails(<?php echo $sy['id']; ?>)"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition duration-200 text-sm w-full sm:hidden flex items-center justify-center gap-1">
                                                <i class="fas fa-eye"></i> <span>Details</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-3 text-center text-gray-500">No school year records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- School Year Details Modal (for mobile view) -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-red-900 text-white rounded-t-lg">
                <h3 class="text-xl font-bold">School Year Details</h3>
                <button type="button" onclick="closeDetailsModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4" id="schoolYearDetailsContent">
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

        // Filter table based on search query
        function filterTable() {
            const searchValue = document.getElementById("searchBar").value.toLowerCase();
            const tableRows = document.querySelectorAll("#dataTable tbody tr");

            tableRows.forEach(row => {
                const cells = row.querySelectorAll("td");
                let found = false;

                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchValue)) {
                        found = true;
                    }
                });

                if (found) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // View school year details on mobile
        function viewDetails(id) {
            // Find the row with this ID
            const row = document.querySelector(`button[onclick*="viewDetails(${id})"]`).closest('tr');

            if (row) {
                const schoolYear = row.cells[0].textContent;
                const semester = row.cells[1].textContent;
                const startDate = row.querySelector('td:nth-child(3)').textContent;
                const endDate = row.querySelector('td:nth-child(4)').textContent;
                const createdAt = row.querySelector('td:nth-child(5)').textContent;

                // Populate and show the modal
                document.getElementById('schoolYearDetailsContent').innerHTML = `
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">School Year</p>
                            <p class="font-medium">${schoolYear}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Semester</p>
                            <p class="font-medium">${semester}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Start Date</p>
                            <p class="font-medium">${startDate}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">End Date</p>
                            <p class="font-medium">${endDate}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Created At</p>
                            <p class="font-medium">${createdAt}</p>
                        </div>
                    </div>
                `;

                document.getElementById('detailsModal').classList.remove('hidden');
            }
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }

        function showAddSchoolYearModal() {
            Swal.fire({
                title: 'Add School Year',
                html: `
                <div class="flex flex-col gap-4 p-2">
                    <div class="flex flex-col">
                        <label for="schoolYear" class="text-sm text-left text-gray-600 mb-1">School Year</label>
                        <input type="text" id="schoolYear" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g., 2023-2024">
                    </div>
                    
                    <div class="flex flex-col">
                        <label for="semester" class="text-sm text-left text-gray-600 mb-1">Semester</label>
                        <select id="semester" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="startDate" class="text-sm text-left text-gray-600 mb-1">Start Date</label>
                        <input type="date" id="startDate" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div class="flex flex-col">
                        <label for="endDate" class="text-sm text-left text-gray-600 mb-1">End Date</label>
                        <input type="date" id="endDate" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                `,
                confirmButtonText: 'Save',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                focusConfirm: false,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                preConfirm: () => {
                    const schoolYear = document.getElementById('schoolYear').value.trim();
                    const semester = document.getElementById('semester').value;
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;

                    if (!schoolYear || !semester || !startDate || !endDate) {
                        Swal.showValidationMessage('Please fill out all fields');
                        return false;
                    }

                    return {
                        schoolYear,
                        semester,
                        startDate,
                        endDate
                    };
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    addSchoolYear(result.value);
                }
            });
        }

        function addSchoolYear(data) {
            fetch('add_school_year.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                })
                .then((response) => response.json())
                .then((result) => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: result.message,
                            showConfirmButton: false,
                            timer: 3000,
                            confirmButtonColor: '#6B0D0D'
                        }).then(() => {
                            location.reload(); // Refresh page
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                            showConfirmButton: true,
                            confirmButtonColor: '#6B0D0D'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to add school year.',
                        showConfirmButton: true,
                        confirmButtonColor: '#6B0D0D'
                    });
                });
        }

        function editSchoolYear(id) {
            fetch(`get_school_year.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const {
                            school_year,
                            semester,
                            start_date,
                            end_date
                        } = data.schoolYear;

                        Swal.fire({
                            title: 'Edit School Year',
                            html: `
                            <div class="flex flex-col gap-4 p-2">
                                <div class="flex flex-col">
                                    <label for="schoolYear" class="text-sm text-left text-gray-600 mb-1">School Year</label>
                                    <input type="text" id="schoolYear" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${school_year}">
                                </div>
                                
                                <div class="flex flex-col">
                                    <label for="semester" class="text-sm text-left text-gray-600 mb-1">Semester</label>
                                    <select id="semester" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                                        <option value="1st Semester" ${semester === "1st Semester" ? "selected" : ""}>1st Semester</option>
                                        <option value="2nd Semester" ${semester === "2nd Semester" ? "selected" : ""}>2nd Semester</option>
                                        <option value="Summer" ${semester === "Summer" ? "selected" : ""}>Summer</option>
                                    </select>
                                </div>

                                <div class="flex flex-col">
                                    <label for="startDate" class="text-sm text-left text-gray-600 mb-1">Start Date</label>
                                    <input type="date" id="startDate" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${start_date}">
                                </div>
                                
                                <div class="flex flex-col">
                                    <label for="endDate" class="text-sm text-left text-gray-600 mb-1">End Date</label>
                                    <input type="date" id="endDate" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${end_date}">
                                </div>
                            </div>
                            `,
                            confirmButtonText: 'Update',
                            showCancelButton: true,
                            cancelButtonText: 'Cancel',
                            focusConfirm: false,
                            confirmButtonColor: '#6B0D0D',
                            cancelButtonColor: '#6c757d',
                            preConfirm: () => {
                                const schoolYear = document.getElementById('schoolYear').value.trim();
                                const semester = document.getElementById('semester').value;
                                const startDate = document.getElementById('startDate').value;
                                const endDate = document.getElementById('endDate').value;

                                if (!schoolYear || !semester || !startDate || !endDate) {
                                    Swal.showValidationMessage('Please fill out all fields');
                                    return false;
                                }

                                return {
                                    id,
                                    schoolYear,
                                    semester,
                                    startDate,
                                    endDate
                                };
                            },
                        }).then((result) => {
                            if (result.isConfirmed) {
                                updateSchoolYear(result.value);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch school year details.',
                            showConfirmButton: true,
                            confirmButtonColor: '#6B0D0D'
                        });
                    }
                });
        }

        function updateSchoolYear(data) {
            fetch('update_school_year.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: result.message,
                            showConfirmButton: false,
                            timer: 3000,
                            confirmButtonColor: '#6B0D0D'
                        }).then(() => {
                            location.reload(); // Refresh page
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                            showConfirmButton: true,
                            confirmButtonColor: '#6B0D0D'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update school year.',
                        showConfirmButton: true,
                        confirmButtonColor: '#6B0D0D'
                    });
                });
        }

        function deleteSchoolYear(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action will permanently delete the school year record.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`delete_school_year.php?id=${id}`, {
                            method: 'DELETE',
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: result.message,
                                    showConfirmButton: false,
                                    timer: 3000,
                                    confirmButtonColor: '#6B0D0D'
                                }).then(() => {
                                    location.reload(); // Refresh page
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message,
                                    showConfirmButton: true,
                                    confirmButtonColor: '#6B0D0D'
                                });
                            }
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete school year.',
                                showConfirmButton: true,
                                confirmButtonColor: '#6B0D0D'
                            });
                        });
                }
            });
        }

        function printTable() {
            // Create a new window for printing
            const printWindow = window.open('', '', 'width=800, height=600');

            // Get table data
            const table = document.getElementById('dataTable');

            // Create printable content with Tailwind-inspired styling
            printWindow.document.write(`
            <html>
            <head>
                <title>School Year Records</title>
                <style>
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        margin: 20px;
                        color: #333;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    th, td {
                        border: 1px solid #e2e8f0;
                        padding: 12px;
                        text-align: left;
                    }
                    th {
                        background-color: #6B0D0D;
                        color: white;
                        font-weight: 600;
                    }
                    tr:nth-child(even) {
                        background-color: #f8fafc;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    .title {
                        color: #6B0D0D;
                        margin-bottom: 8px;
                        font-size: 24px;
                    }
                    .subtitle {
                        margin-top: 0;
                        font-size: 18px;
                        color: #4b5563;
                    }
                    .date {
                        text-align: right;
                        margin-bottom: 20px;
                        font-size: 14px;
                        color: #6b7280;
                    }
                </style>
            </head>
            <body>
                <div class="date">
                    Printed on: ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}
                </div>
                <div class="header">
                    <h2 class="title">CSSPE Inventory & Information System</h2>
                    <h3 class="subtitle">School Year Records</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
            `);

            // Add table data rows (excluding action column)
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.style.display !== 'none') { // Only print visible rows
                    printWindow.document.write('<tr>');

                    // Get cells content, including those that might be hidden on mobile
                    // We'll adjust to get all data, even from hidden columns
                    const schoolYear = row.cells[0]?.textContent || '';
                    const semester = row.cells[1]?.textContent || '';
                    const startDate = row.querySelector('td:nth-child(3)')?.textContent || '';
                    const endDate = row.querySelector('td:nth-child(4)')?.textContent || '';
                    const createdAt = row.querySelector('td:nth-child(5)')?.textContent || '';

                    printWindow.document.write(`
                        <td>${schoolYear}</td>
                        <td>${semester}</td>
                        <td>${startDate}</td>
                        <td>${endDate}</td>
                        <td>${createdAt}</td>
                    `);

                    printWindow.document.write('</tr>');
                }
            });

            // Close the HTML
            printWindow.document.write(`
                    </tbody>
                </table>
                <div style="text-align: center; margin-top: 40px; font-size: 14px; color: #6b7280;">
                    © ${new Date().getFullYear()} CSSPE Inventory & Information System
                </div>
            </body>
            </html>
            `);

            // Finalize and print
            printWindow.document.close();
            printWindow.focus();

            // Print after a small delay to ensure content is loaded
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }
    </script>
</body>

</html>