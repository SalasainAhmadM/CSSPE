<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name , image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $inventoryAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $image = $row['image'];
} else {
    $fullName = "User Not Found";
}

$instructorsQuery = "SELECT first_name, middle_name, last_name, email, address, contact_no, rank, image, department FROM users WHERE role = 'Instructor'";
$instructorsResult = $conn->query($instructorsQuery);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/members.css">
    <link rel="stylesheet" href="/dionSe/assets/css/sidebar.css">
</head>

<body>
    <div class="body">
        <div class="sidebar">
            <div class="sidebarContent">
                <div class="arrowContainer" style="margin-left: 80rem;" id="toggleButton">
                    <div class="subArrowContainer">
                        <img class="hideIcon" src="/dionSe/assets/img/arrow.png" alt="">
                    </div>
                </div>
            </div>
            <div class="userContainer">
                <div class="subUserContainer">
                    <div class="userPictureContainer">
                        <div class="subUserPictureContainer">
                            <img class="subUserPictureContainer"
                                src="../../assets/img/<?= !empty($image) ? htmlspecialchars($image) : '/dionSe/assets/img/CSSPE.png' ?>"
                                alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p><?php echo $fullName; ?></p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">
                        <a href="../index.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Back to Inventory Admin Panel</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/profile.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Profile</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/announcement.php">
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
                                    <p>Manage Inventory</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/notification.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Notificaitons</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="subUserContainer">
                    <a href="../../logout.php">
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
                    <input class="searchBar" id="searchBar" type="text" placeholder="Search by name...">
                    <select name="" class="addButton size" id="rankFilter">
                        <option value="">Choose a Rank</option>
                        <option value="Instructor">Instructor</option>
                        <option value="Assistant Professor">Assistant Professor</option>
                        <option value="Associate Professor">Associate Professor</option>
                        <option value="Professor">Professor</option>
                    </select>
                </div>

                <div class="tableContainer">
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
                        <tbody id="instructorTableBody">
                            <?php if ($instructorsResult->num_rows > 0): ?>
                                <?php while ($instructor = $instructorsResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($instructor['first_name'] . ' ' . ($instructor['middle_name'] ? $instructor['middle_name'] . ' ' : '') . $instructor['last_name']) ?>
                                        </td>
                                        <td>
                                            <img class="image"
                                                src="../../assets/img/<?= htmlspecialchars($instructor['image'] ?: '../../assets/img/CSSPE.png') ?>"
                                                alt="Instructor Image" style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td><?= htmlspecialchars($instructor['email']) ?></td>
                                        <td><?= htmlspecialchars($instructor['address']) ?></td>
                                        <td><?= htmlspecialchars($instructor['contact_no']) ?></td>
                                        <td><?= htmlspecialchars($instructor['department']) ?></td>
                                        <td><?= htmlspecialchars($instructor['rank']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No instructors found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Get references to the search bar, rank filter, and table body
        const searchBar = document.getElementById('searchBar');
        const rankFilter = document.getElementById('rankFilter');
        const tableBody = document.getElementById('instructorTableBody');

        // Add an input event listener to the search bar
        searchBar.addEventListener('input', filterTable);

        // Add a change event listener to the rank filter
        rankFilter.addEventListener('change', filterTable);

        function filterTable() {
            const searchTerm = searchBar.value.toLowerCase();
            const selectedRank = rankFilter.value;
            const rows = tableBody.getElementsByTagName('tr');

            for (const row of rows) {
                const nameCell = row.cells[0]?.textContent.toLowerCase();
                const rankCell = row.cells[6]?.textContent;

                // Check if the row matches both filters
                const matchesSearch = !searchTerm || nameCell.includes(searchTerm);
                const matchesRank = !selectedRank || rankCell === selectedRank;

                row.style.display = matchesSearch && matchesRank ? '' : 'none';
            }
        }

    </script>
    <script src="/dionSe/assets/js/sidebar.js"></script>
    <script src="/dionSe/assets/js/program.js"></script>
</body>

</html>