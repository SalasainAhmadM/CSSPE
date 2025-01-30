<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

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

// Fetch notifications
$notifQuery = "SELECT id, description, created_at FROM notif_items ORDER BY created_at DESC";
$notifResult = $conn->query($notifQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="../assets/css/organization.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/notification.css">
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

                        <a href="../inventoryAdmin/inventory.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Inventories</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/borrowing.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Borrow request</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/borrowItem.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Borrowed Item</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/notification.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Notification</p>
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
                    <p class="text">Notification</p>
                </div>
                <style>
                    .notificationContainer {
                        position: relative;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        padding: 1rem;
                        border: 1px solid #ccc;
                        margin-bottom: 1rem;
                        border-radius: 5px;
                        background-color: #f9f9f9;
                    }

                    .subNotificaitonContainer {
                        display: flex;
                        flex-direction: column;
                        flex-grow: 1;
                    }

                    .messageContainer p {
                        margin: 0;
                        font-size: 1rem;
                    }

                    .dateContainer p {
                        margin: 0;
                        font-size: 0.9rem;
                        color: #888;
                    }

                    .deleteContainer {
                        position: absolute;
                        right: 15px;
                        top: 50%;
                        transform: translateY(-50%);
                        display: flex;
                        align-items: right;
                        justify-content: right;
                    }

                    .deleteButton {
                        background-color: rgb(109, 18, 10);
                        color: white;
                        border: none;
                        border-radius: 3px;
                        padding: 0.4rem 0.6rem;
                        cursor: pointer;
                        font-size: 0.9rem;
                        width: auto;
                        min-width: 60px;
                    }
                </style>
                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search..." oninput="searchCard()"
                        id="searchInput">
                    <select class="addButton size" id="filterDropdown" onchange="filterByDate()">
                        <option value="">Filter</option>
                        <option value="all">All</option>
                        <option value="day">This day</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                    </select>
                </div>

                <div style="margin-top: 20px" class="dashboardContainer" id="notifContainer">
                    <?php if ($notifResult && $notifResult->num_rows > 0): ?>
                        <?php while ($notif = $notifResult->fetch_assoc()): ?>
                            <div class="notificationContainer" data-notif-id="<?php echo $notif['id']; ?>"
                                data-description="<?php echo htmlspecialchars($notif['description']); ?>"
                                data-created-at="<?php echo htmlspecialchars($notif['created_at']); ?>">

                                <div class="subNotificaitonContainer">
                                    <div class="messageContainer">
                                        <p><?php echo htmlspecialchars($notif['description']); ?></p>
                                    </div>
                                    <div class="dateContainer">
                                        <p style="margin-left: 0.5rem;"><?php echo htmlspecialchars($notif['created_at']); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="deleteContainer">
                                    <button class="deleteButton">Delete</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="notificationContainer">
                            <div class="subNotificaitonContainer">
                                <div class="messageContainer">
                                    <p>No notifications found.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <script>
                    function searchCard() {
                        let input = document.getElementById('searchInput').value.toLowerCase();
                        let notifContainers = document.querySelectorAll('.notificationContainer');

                        notifContainers.forEach(container => {
                            let description = container.getAttribute('data-description').toLowerCase();
                            if (description.includes(input)) {
                                container.style.display = "block";
                            } else {
                                container.style.display = "none";
                            }
                        });
                    }

                    function filterByDate() {
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


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButtons = document.querySelectorAll('.deleteButton');

            deleteButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const notificationContainer = e.target.closest('.notificationContainer');
                    const notifId = notificationContainer.dataset.notifId;

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Perform the delete action
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', './endpoints/delete_notif.php', true);
                            xhr.setRequestHeader('Content-Type', 'application/json');
                            xhr.onreadystatechange = function () {
                                if (xhr.readyState === 4) {
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        Swal.fire({
                                            icon: response.status === 'success' ? 'success' : 'error',
                                            title: response.message,
                                            showConfirmButton: false,
                                            timer: 3000
                                        });

                                        if (response.status === 'success') {
                                            // Remove the notification from the DOM
                                            notificationContainer.remove();
                                        }
                                    } catch (error) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'An error occurred while processing your request.',
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    }
                                }
                            };
                            xhr.send(JSON.stringify({ id: notifId }));
                        }
                    });
                });
            });
        });
    </script>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>