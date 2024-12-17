<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('instructor');

$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);

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

$query_notifications = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";
$result_notifications = mysqli_query($conn, $query_notifications);
$notificationCount = 0;

if ($result_notifications && $row_notifications = mysqli_fetch_assoc($result_notifications)) {
    $notificationCount = $row_notifications['notification_count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Members</title>

    <link rel="stylesheet" href="../assets/css/members.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
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
                        <a href="../homePage/profile.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Profile</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Announcements</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/borrowing.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Inventories</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/memorandumHome.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Memorandums</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/events.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Events</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/members.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Faculty Members</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/organization.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Organizations</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/notification.php?update=1">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>
                                        Notifications
                                        <span style="background-color:#1a1a1a; padding:5px; border-radius:4px;">
                                            <?php echo $notificationCount; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="subUserContainer">
                    <a href="/dionSe/authentication/login.php">
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
                            <img class="logo" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="collegeNameContainer">
                            <p>CSSPE Inventory & Information System</p>
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">Faculty Members</p>
                </div>


                <div class="searchContainer">
                    <input class="searchBar" id="search" type="text" placeholder="Search...">
                    <select name="" class="addButton size" id="" onchange="filterByDepartment()">
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


                <div class="tableContainer" style="height:475px">
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

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tbody>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                    <td>
                                        <img class="" src="<?= '../assets/img/' . htmlspecialchars($row['image']) ?>" style="width:100px" alt="">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['rank']); ?></td>
                                </tr>
                            </tbody>
                        <?php endwhile; ?>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        function filterByDepartment() {
            const selectedDepartment = document.querySelector('.addButton').value.toLowerCase();
            const rows = document.querySelectorAll('.tableContainer tbody tr');

            rows.forEach(row => {
                const department = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                if (!selectedDepartment || department === selectedDepartment) {
                    row.style.display = ''; // Show row
                } else {
                    row.style.display = 'none'; // Hide row
                }
            });
        }
    </script>

    <script src="../assets/js/search.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
</body>

</html>