<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);

$limit = 12;

// Get the current page or default to 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the starting point for the query
$offset = ($page - 1) * $limit;

// Query with LIMIT and OFFSET
$query = "SELECT * FROM memorandums LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$totalQuery = "SELECT COUNT(*) AS total FROM memorandums";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalItems = $totalRow['total'];

$totalPages = ceil($totalItems / $limit);

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
    <title>Memorandums</title>

    <link rel="stylesheet" href="../assets/css/memorandumHome.css">
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
                        <p><?php echo ($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
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

                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search..." oninput="searchCard()">
                    <select name="" class="addButton size" id="filterDropdown" onchange="filterByDate()">
                        <option value="">Filter</option>
                        <option value="all">All</option>
                        <option value="day">This day</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                    </select>
                </div>

                <div class="inventoryContainer1" id="inventoryContainer">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="subInventoryContainer1">
                            <!-- <div class="imageContainer1" style="border-bottom: solid gray 1px;">
                                <img class="image2" src="../assets/img/CSSPE.png" alt="">
                            </div> -->

                            <div class="infoContainer1">
                                <p><?php echo htmlspecialchars($row['title']); ?></p>
                            </div>

                            <div class="infoContainer2">
                                <p>Uploaded at:
                                    <?php echo htmlspecialchars(date('Y-m-d', strtotime($row['uploaded_at']))); ?>
                                </p>
                            </div>

                            <div class="buttonContainer">
                                <button class="addButton" style="width: 6rem;" onclick="editProgram(
                        '<?php echo $row['title']; ?>', 
                        '<?php echo $row['description']; ?>',
                        '<?php echo $row['file_path']; ?>',
                        '<?php echo $row['uploaded_at']; ?>'
                    )">
                                    View
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="prev">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>"
                            class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="next">Next</a>
                    <?php endif; ?>
                </div>


            </div>
        </div>
    </div>

    <div class="editContainer" style="display: none;">
        <div class="subAddContainer">
            <div class="titleContainer">
                <p>View Memorandum</p>
            </div>

            <div class="inputContainer" style="flex-direction: column; min-height: 20rem;">
                <div class="imageContainer">
                    <img id="memorandumImage" class="image2"
                        src="../assets/img/freepik-untitled-project-20241018143133NtJY.png" alt="">
                </div>
            </div>

            <div class="subLoginContainer">
                <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label style="text-align: center; font-size: 1.5rem;">Title: <p id="memorandumTitle"></p></label>
                </div>

                <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label style="text-align: center; font-size: 1.5rem;">Description: <p id="memorandumDescription">
                        </p></label>
                </div>

                <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label style="text-align: center; font-size: 1.5rem;">Date Uploaded: <p id="memorandumUploadedAt">
                        </p></label>
                </div>

                <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                    <a id="memorandumDownloadLink" href="#" download>
                        <button class="addButton" style="width: 6rem;">Download</button>
                    </a>
                    <button onclick="cancelContainer()" class="addButton1" style="width: 6rem;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editProgram(title, description, filePath, uploadedAt) {
            document.querySelector('.editContainer').style.display = 'block';

            document.getElementById('memorandumDescription').textContent = description;
            document.getElementById('memorandumTitle').textContent = title;
            document.getElementById('memorandumUploadedAt').textContent = uploadedAt;
            document.getElementById('memorandumDownloadLink').href = filePath;
        }

        function cancelContainer() {
            document.querySelector('.editContainer').style.display = 'none';
        }
    </script>

    <script>
        function filterByDate() {
            const filterValue = document.getElementById('filterDropdown').value;
            const cards = document.querySelectorAll('.subInventoryContainer1');
            const today = new Date();

            cards.forEach(card => {
                const uploadedAtText = card.querySelector('.infoContainer2 p').textContent.replace('Uploaded at: ', '');
                const uploadedAt = new Date(uploadedAtText);
                let showCard = true;

                if (filterValue === 'day') {
                    showCard = uploadedAt.toDateString() === today.toDateString();
                } else if (filterValue === 'week') {
                    // Calculate the start and end of the week (consider Sunday as the start)
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay()); // Sunday
                    const weekEnd = new Date(today);
                    weekEnd.setDate(today.getDate() - today.getDay() + 6); // Saturday
                    showCard = uploadedAt >= weekStart && uploadedAt <= weekEnd;
                } else if (filterValue === 'month') {
                    showCard = uploadedAt.getMonth() === today.getMonth() && uploadedAt.getFullYear() === today.getFullYear();
                } else if (filterValue === 'all') {
                    showCard = true;
                }

                card.style.display = showCard ? '' : 'none';
            });
        }
    </script>

    <script src="../assets/js/search_Card.js"></script>
    <script src="../assets/js/sidebar.js"></script>

</body>

</html>