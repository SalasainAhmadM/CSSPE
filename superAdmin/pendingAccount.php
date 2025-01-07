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
    <title>Pending Users</title>

    <link rel="stylesheet" href="../assets/css/pendingAccount.css">
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
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">Pending Account</p>
                </div>

                <div class="searchContainer">
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search...">
                    <!-- <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size">Print</button>
                        <select name="" class="addButton size" id="">
                            <option value="">Choose a position</option>
                        </select>
                    </div> -->
                </div>

                <div class="tableContainer" style="height:475px">
                    <table id="dataTable">
                        <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Contact Number</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['rank']); ?></td>
                                    <td class="button">
                                        <a href="#" onclick="approveUser(<?php echo $row['id']; ?>)">
                                            <button class="addButton1" style="width: 6rem;">Approve</button>
                                        </a>
                                        <a href="#" onclick="deleteUser(<?php echo $row['id']; ?>)">
                                            <button class="addButton1" style="width: 6rem;">Delete</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function approveUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to approve this user?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve!',
                cancelButtonText: 'No, cancel'
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
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?delete_id=" + userId;
                }
            });
        }

        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: "<?php echo $_SESSION['message_type']; ?>",
                title: "<?php echo $_SESSION['message']; ?>",
                showConfirmButton: false,
                timer: 3000
            });

            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
        document.getElementById('searchBar').addEventListener('input', function () {
            const searchQuery = this.value.toLowerCase();
            const rows = document.querySelectorAll('#dataTable tbody tr');

            rows.forEach(row => {
                const fullName = row.cells[0].textContent.toLowerCase();
                if (fullName.includes(searchQuery)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>