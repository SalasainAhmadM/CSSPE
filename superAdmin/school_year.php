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
    <title>Faculty Member</title>

    <link rel="stylesheet" href="../assets/css/facultyMember.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        .passwordContainer {
            position: relative;
            display: flex;
            align-items: center;
        }


        .toggle-password-icon {
            position: absolute;
            right: 35px;
            cursor: pointer;
            color: #aaa;
            font-size: 18px;
        }

        .toggle-password-icon:hover {
            color: #333;
        }
    </style>

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
                            <img class="subUserPictureContainer"
                                src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>"
                                alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p><?php echo $fullName; ?></p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">

                        <a href="../homePage/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Home</p>
                                </div>
                            </div>
                        </a>

                        <a href="../superAdmin/">
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
                        <a href="../superAdmin/school_year.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>School Year</p>
                                </div>
                            </div>
                        </a>
                        <a href="../informationAdmin/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Information Admin Panel</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/">
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
                            <!-- <p>School Year 1st Semester</p> -->
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">School Year</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" id="search" type="text" placeholder="Search..." oninput="filterTable()" />
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size" onclick="printTable()">Print</button>
                        <button onclick="showAddSchoolYearModal()" class="addButton size">Add School Year</button>
                    </div>
                </div>
                <div class="tableContainer" style="height:475px;">
                    <table>
                        <thead>
                            <tr>
                                <th>School Year</th>
                                <th>Semester</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($schoolYears) > 0): ?>
                                <?php foreach ($schoolYears as $sy): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sy['school_year']); ?></td>
                                        <td><?php echo htmlspecialchars($sy['semester']); ?></td>
                                        <td><?php echo htmlspecialchars($sy['start_date']); ?></td>
                                        <td><?php echo htmlspecialchars($sy['end_date']); ?></td>
                                        <td><?php echo htmlspecialchars($sy['created_at']); ?></td>
                                        <td>
                                            <a href="#" onclick="editSchoolYear(<?php echo $sy['id']; ?>)">
                                                <button class="addButton1" style="width: 6rem;">Edit</button>
                                            </a>
                                            <a href="#" onclick="deleteSchoolYear(<?php echo $sy['id']; ?>)">
                                                <button class="addButton1" style="width: 6rem;">Delete</button>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No school year records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    </div>
    </div>
    </div>




    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
    <script src="../assets/js/uploadImage.js"></script>
    <script src="../assets/js/printTable.js"></script>
    <script src="../assets/js/search.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function filterTable() {
            const searchValue = document.getElementById("search").value.toLowerCase();
            const tableRows = document.querySelectorAll("#schoolYearTable tr");

            tableRows.forEach(row => {
                const schoolYearCell = row.querySelector(".schoolYear");

                if (schoolYearCell) {
                    const schoolYearText = schoolYearCell.textContent.toLowerCase();
                    if (schoolYearText.includes(searchValue)) {
                        row.style.display = ""; // Show the row
                    } else {
                        row.style.display = "none"; // Hide the row
                    }
                }
            });
        }

        function showAddSchoolYearModal() {
            Swal.fire({
                title: 'Add School Year',
                html: `
        <div style="display: flex; flex-direction: column; gap: 1rem; padding: 1rem;">
            <div style="display: flex; flex-direction: column;">
                <label for="schoolYear" style="font-size: 14px; margin-bottom: 4px;">School Year</label>
                <input type="text" id="schoolYear" class="swal2-input" placeholder="e.g., 2023-2024" style="margin: 0; font-size: 14px;">
            </div>
            
            <div style="display: flex; flex-direction: column;">
                <label for="semester" style="font-size: 14px; margin-bottom: 4px;">Semester</label>
                <select id="semester" class="swal2-input" style="margin: 0; font-size: 14px;">
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>

            <div style="display: flex; flex-direction: column;">
                <label for="startDate" style="font-size: 14px; margin-bottom: 4px;">Start Date</label>
                <input type="date" id="startDate" class="swal2-input" style="margin: 0; font-size: 14px;">
            </div>
            
            <div style="display: flex; flex-direction: column;">
                <label for="endDate" style="font-size: 14px; margin-bottom: 4px;">End Date</label>
                <input type="date" id="endDate" class="swal2-input" style="margin: 0; font-size: 14px;">
            </div>
        </div>
        `,
                confirmButtonText: 'Save',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                focusConfirm: false,
                preConfirm: () => {
                    const schoolYear = document.getElementById('schoolYear').value.trim();
                    const semester = document.getElementById('semester').value;
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;

                    if (!schoolYear || !semester || !startDate || !endDate) {
                        Swal.showValidationMessage('Please fill out all fields');
                        return false;
                    }

                    return { schoolYear, semester, startDate, endDate };
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
                        }).then(() => {
                            location.reload(); // Refresh page
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                            showConfirmButton: true,
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to add school year.',
                        showConfirmButton: true,
                    });
                });
        }
        function editSchoolYear(id) {
            fetch(`get_school_year.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const { school_year, semester, start_date, end_date } = data.schoolYear;

                        Swal.fire({
                            title: 'Edit School Year',
                            html: `
            <div style="display: flex; flex-direction: column; gap: 1rem; padding: 1rem;">
                <div style="display: flex; flex-direction: column;">
                    <label for="schoolYear" style="font-size: 14px; margin-bottom: 4px;">School Year</label>
                    <input type="text" id="schoolYear" class="swal2-input" value="${school_year}" style="margin: 0; font-size: 14px;">
                </div>
                
                <div style="display: flex; flex-direction: column;">
                    <label for="semester" style="font-size: 14px; margin-bottom: 4px;">Semester</label>
                    <select id="semester" class="swal2-input" style="margin: 0; font-size: 14px;">
                        <option value="1st Semester" ${semester === "1st Semester" ? "selected" : ""}>1st Semester</option>
                        <option value="2nd Semester" ${semester === "2nd Semester" ? "selected" : ""}>2nd Semester</option>
                        <option value="Summer" ${semester === "Summer" ? "selected" : ""}>Summer</option>
                    </select>
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label for="startDate" style="font-size: 14px; margin-bottom: 4px;">Start Date</label>
                    <input type="date" id="startDate" class="swal2-input" value="${start_date}" style="margin: 0; font-size: 14px;">
                </div>
                
                <div style="display: flex; flex-direction: column;">
                    <label for="endDate" style="font-size: 14px; margin-bottom: 4px;">End Date</label>
                    <input type="date" id="endDate" class="swal2-input" value="${end_date}" style="margin: 0; font-size: 14px;">
                </div>
            </div>
                    `,
                            confirmButtonText: 'Update',
                            showCancelButton: true,
                            cancelButtonText: 'Cancel',
                            focusConfirm: false,
                            preConfirm: () => {
                                const schoolYear = document.getElementById('schoolYear').value.trim();
                                const semester = document.getElementById('semester').value;
                                const startDate = document.getElementById('startDate').value;
                                const endDate = document.getElementById('endDate').value;

                                if (!schoolYear || !semester || !startDate || !endDate) {
                                    Swal.showValidationMessage('Please fill out all fields');
                                    return false;
                                }

                                return { id, schoolYear, semester, startDate, endDate };
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
                        }).then(() => {
                            location.reload(); // Refresh page
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                            showConfirmButton: true,
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update school year.',
                        showConfirmButton: true,
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
                                }).then(() => {
                                    location.reload(); // Refresh page
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message,
                                    showConfirmButton: true,
                                });
                            }
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to delete school year.',
                                showConfirmButton: true,
                            });
                        });
                }
            });
        }


        function printTable() {
            const tableContainer = document.querySelector('.tableContainer');

            // Temporarily hide the last column (Action column)
            const rows = tableContainer.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = 'none'; // Hide the last cell (Action column)
                }
            });

            // Prepare the content for printing (table only)
            const printContent = tableContainer.outerHTML;

            // Open a new window for the print preview
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Print Table</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid black;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f4f4f4;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
        </style>
    </head>
    <body>
        ${printContent} <!-- Includes only the table -->
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();

            // Restore the visibility of the last column
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = ''; // Restore visibility
                }
            });
        }

    </script>

</body>

</html>