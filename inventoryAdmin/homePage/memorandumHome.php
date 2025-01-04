<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name, image FROM users WHERE id = ?";
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

$memoQuery = "SELECT * FROM memorandums";
$memoResult = $conn->query($memoQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/memorandumHome.css">
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
        <style>
            .inventoryGrid {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                padding: 50px;
                justify-content: space-between;
            }

            .inventoryContainer {
                flex: 0 0 calc(16.6% - 20px);
                box-sizing: border-box;
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                padding: 10px;
                text-align: center;
            }

            .inventoryContainer img {
                object-fit: cover;
            }
        </style>
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
                    <p class="text">Memorandums</p>
                </div>

                <?php

                date_default_timezone_set('Asia/Manila');

                $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
                $currentDate = date('Y-m-d');
                $currentWeekStart = date('Y-m-d', strtotime('monday this week'));
                $currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));
                $currentMonth = date('Y-m');

                $query = "SELECT * FROM memorandums";

                if ($filter === 'day') {
                    $query .= " WHERE DATE(uploaded_at) = '$currentDate'";
                } elseif ($filter === 'week') {
                    $query .= " WHERE DATE(uploaded_at) BETWEEN '$currentWeekStart' AND '$currentWeekEnd'";
                } elseif ($filter === 'month') {
                    $query .= " WHERE DATE_FORMAT(uploaded_at, '%Y-%m') = '$currentMonth'";
                }

                $memoResult = $conn->query($query);
                ?>

                <div class="searchContainer">
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search by title...">
                    <select name="filter" class="addButton size" id="filterSelect" onchange="applyFilter()">
                        <option value="">Filter</option>
                        <option value="all">All</option>
                        <option value="day">This day</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                    </select>
                </div>


                <!-- Main inventory grid -->
                <div id="inventoryGrid" class="inventoryGrid">
                    <?php if ($memoResult->num_rows > 0): ?>
                        <?php while ($memo = $memoResult->fetch_assoc()): ?>
                            <div class="inventoryContainer" data-title="<?= htmlspecialchars($memo['title']) ?>">
                                <h3><?= htmlspecialchars($memo['title']) ?></h3>
                                <p><?= htmlspecialchars($memo['description'] ?? 'No Description Available') ?></p>
                                <p><strong>Uploaded At:</strong> <?= htmlspecialchars($memo['uploaded_at']) ?></p>
                                <div class="buttonContainer">
                                    <button class="addButton" onclick="viewMemo(
                '<?= addslashes($memo['title']) ?>',
                '<?= addslashes($memo['description'] ?? 'No Description Available') ?>',
                '<?= addslashes($memo['uploaded_at']) ?>',
                '<?= addslashes($memo['file_path']) ?>'
            )">View</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No memorandums available</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>


    <div class="editContainer" style="display: none; background-color: ndone;">
        <div class="editContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>View Memorandums</p>
                </div>

                <div class="subLoginContainer">
                    <div class="inputContainer" style="flex-direction: column; min-height: 20rem;">
                        <div class="imageContainer">
                            <img class="imageContainer"
                                src="/dionSe/assets/img/freepik-untitled-project-20241018143133NtJY.png" alt="">
                        </div>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Title:</label>
                        <input class="inputEmail" type="text" readonly>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Description:</label>
                        <textarea name="" id="" class="inputEmail" readonly></textarea>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Date:</label>
                        <input class="inputEmail" type="text" readonly>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                        <button class="addButton" style="width: 6rem;">Download</button>
                        <button onclick="editProgram()" class="addButton1" style="width: 6rem;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const searchBar = document.getElementById('searchBar');
        const inventoryGrid = document.getElementById('inventoryGrid');

        searchBar.addEventListener('input', function () {
            const searchTerm = searchBar.value.toLowerCase();
            const memoContainers = inventoryGrid.getElementsByClassName('inventoryContainer');

            for (const container of memoContainers) {
                const title = container.getAttribute('data-title').toLowerCase();
                container.style.display = title.includes(searchTerm) ? '' : 'none';
            }
        });

        function viewMemo(title, description, uploadedAt, filePath) {
            // Find modal elements
            const editContainer = document.querySelector('.editContainer');
            const titleInput = editContainer.querySelector('input[type="text"]');
            const descriptionTextarea = editContainer.querySelector('textarea');
            const dateInput = editContainer.querySelectorAll('input[type="text"]')[1]; // Second text input for date
            const downloadButton = editContainer.querySelector('button.addButton');

            // Update modal content with the memo data
            titleInput.value = title || 'No Title';
            descriptionTextarea.value = description || 'No Description Available';
            dateInput.value = uploadedAt || 'No Date';

            // Set up the download button to download the file
            downloadButton.onclick = () => {
                if (filePath) {
                    const absolutePath = filePath.startsWith('http') ? filePath : `http://localhost/CSSPE/${filePath}`;
                    const link = document.createElement('a');
                    link.href = absolutePath;
                    link.download = absolutePath.split('/').pop(); // Extracts the file name from the path
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('No file available for download');
                }
            };

            // Show the modal
            editContainer.style.display = 'block';
        }

        // Function to close the modal
        function editProgram() {
            const editContainer = document.querySelector('.editContainer');
            editContainer.style.display = 'none';
        }

        function applyFilter() {
            const filterValue = document.getElementById('filterSelect').value;
            const urlParams = new URLSearchParams(window.location.search);

            // Update filter parameter in URL
            if (filterValue === 'all' || filterValue === '') {
                urlParams.delete('filter'); // Remove filter if "All" is selected
            } else {
                urlParams.set('filter', filterValue);
            }

            // Reload page with the selected filter
            window.location.search = urlParams.toString();
        }

    </script>

    <script src="/dionSe/assets/js/sidebar.js"></script>
    <script src="/dionSe/assets/js/program.js"></script>
</body>

</html>