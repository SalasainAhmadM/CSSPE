<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);
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

$query = "SELECT type, title, description, DATE_FORMAT(uploaded_at, '%Y-%m-%d %H:%i:%s') AS formatted_date 
          FROM notifications 
          ORDER BY uploaded_at DESC";
$result = $conn->query($query);

$notifications = [
    'Announcements' => [],
    'Memorandums' => []
];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[$row['type']][] = $row;
    }
}
// Update the is_read column to 1 for all notifications when the page is opened
// $updateQuery = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";
// mysqli_query($conn, $updateQuery);

// $query = "SELECT * FROM notifications ORDER BY uploaded_at DESC";
// $result = mysqli_query($conn, $query);


// $query_notifications = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";
// $result_notifications = mysqli_query($conn, $query_notifications);
// $notificationCount = 0;

// if ($result_notifications && $row_notifications = mysqli_fetch_assoc($result_notifications)) {
//     $notificationCount = $row_notifications['notification_count'];
// }

// // delete request
// if (isset($_GET['delete_id'])) {
//     $notifications_id = $_GET['delete_id'];
//     $delete_query = "DELETE FROM notifications WHERE id = $notifications_id";
//     if (mysqli_query($conn, $delete_query)) {
//         header('Location: ' . $_SERVER['PHP_SELF']);
//         exit();
//     } else {
//         echo "Error deleting record: " . mysqli_error($conn);
//     }
// }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>

    <link rel="stylesheet" href="../assets/css/organization.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/notificationHome.css">


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
                        <?php if ($_SESSION['user_role'] === 'inventory_admin'): ?>
                            <a href="../inventoryAdmin/index.php">
                                <div class="buttonContainer1">
                                    <div class="nameOfIconContainer">
                                        <p>Back to Inventory Admin Panel</p>
                                    </div>
                                </div>
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'information_admin'): ?>
                            <a href="../informationAdmin/index.php">
                                <div class="buttonContainer1">
                                    <div class="nameOfIconContainer">
                                        <p>Back to Information Admin Panel</p>
                                    </div>
                                </div>
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'super_admin'): ?>
                            <a href="../superAdmin/index.php">
                                <div class="buttonContainer1">
                                    <div class="nameOfIconContainer">
                                        <p>Back to Super Admin Panel</p>
                                    </div>
                                </div>
                            </a>
                        <?php endif; ?>

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

                        <a href="../homePage/notification.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Notifications</p>
                                </div>
                            </div>
                        </a>
                        <!-- <a href="../homePage/notification.php">
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
                        </a> -->
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
        <style>
            .dashboardContainer {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 2rem;
                flex-direction: column;
            }

            .mainContainer {
                background-color: rgb(243, 243, 243);
                padding-bottom: 2rem;
            }

            .notificationContainer {
                width: 80%;
                background-color: rgb(223, 222, 222);
                box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.699);
                border-radius: 0.5rem;
                padding: 0.5rem;
                display: flex;
                flex-direction: column;
            }

            .subNotificaitonContainer {
                display: flex;
                flex-direction: column;
            }

            .messageContainer {
                font-size: 1.2rem;
                text-align: left;
                font-weight: bold;
            }

            .dateContainer {
                /* height: 2rem; */
                font-size: 1.2rem;
                text-align: left;
                display: flex;
                align-items: center;
                color: gray;
                display: inline;
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
                    <p class="text">Notifications</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search..." oninput="searchNotifications()"
                        id="searchInput">
                    <select class="addButton size" id="filterDropdown" onchange="filterNotifications()">
                        <option value="">Filter</option>
                        <option value="all">All</option>
                        <option value="day">This day</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                    </select>
                </div>

                <div style="margin-top: 20px" class="dashboardContainer" id="notifContainer">
                    <?php foreach ($notifications as $type => $notificationList): ?>
                        <?php if (!empty($notificationList)): ?>
                            <?php foreach ($notificationList as $notification): ?>
                                <div class="notificationContainer"
                                    data-description="<?php echo htmlspecialchars($notification['description']); ?>"
                                    data-created-at="<?php echo htmlspecialchars($notification['formatted_date']); ?>">

                                    <a class="subNotificaitonContainer">
                                        <div class="messageContainer" style="padding:5px 5px;">
                                            <h5><?php echo htmlspecialchars($notification['title']); ?></h5>
                                            <p><?php echo htmlspecialchars($notification['description']); ?></p>
                                        </div>
                                        <div class="dateContainer" style="padding:10px 2px;">
                                            <h6 style="margin-left: 0.5rem;">Type:
                                                <?php echo htmlspecialchars($type); ?>
                                            </h6>
                                            <h6 style="margin-left: 0.5rem;">Date:
                                                <?php echo htmlspecialchars($notification['formatted_date']); ?>
                                            </h6>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="notificationContainer">
                                <div class="subNotificaitonContainer">
                                    <div class="messageContainer" style="padding:5px 5px; text-align: center;">
                                        <h5>No <?php echo htmlspecialchars($type); ?> available.</h5>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <script>
                    function searchNotifications() {
                        let input = document.getElementById('searchInput').value.toLowerCase();
                        let notifContainers = document.querySelectorAll('.notificationContainer');

                        notifContainers.forEach(container => {
                            let description = container.getAttribute('data-description').toLowerCase();
                            container.style.display = description.includes(input) ? "block" : "none";
                        });
                    }

                    function filterNotifications() {
                        let filterValue = document.getElementById('filterDropdown').value;
                        let notifContainers = document.querySelectorAll('.notificationContainer');
                        let currentDate = new Date();

                        notifContainers.forEach(container => {
                            let createdAt = new Date(container.getAttribute('data-created-at'));
                            let show = false;

                            if (filterValue === "all") {
                                show = true;
                            } else if (filterValue === "day") {
                                show = createdAt.toDateString() === currentDate.toDateString();
                            } else if (filterValue === "week") {
                                let oneWeekAgo = new Date();
                                oneWeekAgo.setDate(currentDate.getDate() - 7);
                                show = createdAt >= oneWeekAgo;
                            } else if (filterValue === "month") {
                                show = createdAt.getMonth() === currentDate.getMonth() && createdAt.getFullYear() === currentDate.getFullYear();
                            }

                            container.style.display = show ? "block" : "none";
                        });
                    }
                </script>

            </div>
        </div>

    </div>

    <script src="../assets/js/sidebar.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

<script>
    function deleteNoti(userId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this notification?',
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
</script>

</html>