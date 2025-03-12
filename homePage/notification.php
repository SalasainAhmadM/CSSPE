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

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Base styles */
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            scroll-behavior: smooth;
        }

        * {
            box-sizing: border-box;
        }

        /* Theme colors */
        :root {
            --primary: #6B0D0D;
            --primary-dark: #540A0A;
            --secondary: #6c757d;
            --secondary-dark: #5a6268;
            --light: #f9f9f9;
            --lighter: #f5f5f5;
            --border: #f0f0f0;
            --text: #333;
            --text-light: #666;
            --white: #fff;
        }

        .bg-dark-red {
            background-color: var(--primary);
        }

        /* Layout */
        .sidebar {
            position: fixed;
            width: 250px;
            height: 100%;
            background-color: var(--primary);
            color: var(--white);
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        /* Button styling */
        .btn {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            white-space: nowrap;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            box-shadow: 0 2px 4px rgba(107, 13, 13, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 3px 6px rgba(107, 13, 13, 0.3);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: var(--white);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }

        /* Mobile navigation */
        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 101;
            display: none;
            padding: 8px;
            border-radius: 6px;
            background-color: var(--primary);
            color: var(--white);
            font-size: 18px;
            border: none;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .toggle-btn:hover {
            background-color: var(--primary-dark);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 99;
            backdrop-filter: blur(2px);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Sidebar styling */
        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header img {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 50%;
        }

        .sidebar-header span {
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-link {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: rgba(255, 255, 255, 0.5);
        }

        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }

        .sidebar-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }

        .sidebar-footer {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            position: absolute;
            bottom: 0;
            width: 100%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-footer i {
            width: 20px;
            margin-right: 10px;
        }

        /* Header styling */
        .header {
            background-color: var(--primary);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .header h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* Page content */
        .page-content {
            padding: 20px;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            border-bottom: 2px solid var(--border);
            padding-bottom: 15px;
        }

        .page-title i {
            margin-right: 15px;
            background-color: var(--lighter);
            padding: 12px;
            border-radius: 50%;
            color: var(--primary);
        }

        .page-title span {
            font-size: 16px;
            font-weight: normal;
            margin-left: 15px;
            color: var(--text-light);
        }

        /* Search bar */
        .searchContainer {
            margin: 0 25px;
            position: relative;
            max-width: 600px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .searchBar {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .searchBar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 2px 15px rgba(107, 13, 13, 0.1);
        }

        .searchContainer::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 16px;
        }

        /* Filter dropdown */
        .filterDropdown {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 15px;
            background-color: var(--white);
            color: var(--text);
            cursor: pointer;
            outline: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            min-width: 120px;
        }

        .filterDropdown:focus {
            border-color: var(--primary);
            box-shadow: 0 2px 12px rgba(107, 13, 13, 0.1);
        }

        /* Notification Styling */
        .notificationsContainer {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 25px;
            padding: 0 25px;
        }

        .notificationCard {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            border: 1px solid var(--border);
        }

        .notificationCard:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(107, 13, 13, 0.15);
            border-color: rgba(107, 13, 13, 0.1);
        }

        .notificationContent {
            padding: 20px;
        }

        .notificationTitle {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin: 0 0 10px 0;
        }

        .notificationDesc {
            font-size: 15px;
            color: var(--text);
            margin: 0 0 15px 0;
            line-height: 1.5;
        }

        .notificationMeta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px dashed var(--border);
            font-size: 14px;
            color: var(--text-light);
        }

        .notificationType {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .notificationType i {
            color: var(--primary);
        }

        .notificationDate {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .notificationDate i {
            color: var(--primary);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: var(--light);
            border-radius: 10px;
            margin: 30px 25px;
        }

        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
            display: block;
        }

        .empty-state h3 {
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #888;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .searchContainer {
                margin: 0 15px;
            }

            .page-title {
                font-size: 1.6rem;
            }

            .notificationsContainer {
                padding: 0 15px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                left: -280px;
                box-shadow: none;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .toggle-btn {
                display: flex !important;
            }

            .header {
                padding-left: 60px;
            }

            .searchContainer {
                margin: 0 10px;
                flex-direction: column;
                align-items: stretch;
            }

            .page-title span {
                display: none;
            }

            /* Make header text smaller on mobile */
            h1 {
                font-size: 1rem !important;
            }

            h2 {
                font-size: 1.3rem !important;
            }
        }

        @media (max-width: 576px) {
            .notificationsContainer {
                padding: 0 10px;
            }

            .notificationContent {
                padding: 15px;
            }

            .notificationTitle {
                font-size: 16px;
            }

            .notificationDesc {
                font-size: 14px;
            }

            .notificationMeta {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .searchBar {
                font-size: 14px;
                padding: 10px 15px 10px 40px;
            }

            .filterDropdown {
                padding: 8px 12px;
                font-size: 14px;
            }
        }

        /* Focus states for accessibility */
        button:focus,
        a:focus,
        input:focus,
        select:focus {
            outline: 2px solid rgba(107, 13, 13, 0.5);
            outline-offset: 2px;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body.dark-mode-supported {
                --light: #2a2a2a;
                --lighter: #222;
                --border: #333;
                --text: #eee;
                --text-light: #ccc;
                --white: #1a1a1a;

                background-color: #181818;
                color: #eee;
            }

            body.dark-mode-supported .searchBar,
            body.dark-mode-supported .filterDropdown {
                background-color: #222;
                border-color: #444;
                color: #eee;
            }
        }

        /* Print styles */
        @media print {

            .sidebar,
            .searchContainer,
            .toggle-btn {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body>
    <!-- Toggle Sidebar Button -->
    <button class="toggle-btn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay (shown on mobile when sidebar is open) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>" alt="Profile">
            <span><?php echo $fullName; ?></span>
        </div>

        <div style="display: flex; flex-direction: column; padding: 10px 0;">
            <?php if ($_SESSION['user_role'] === 'inventory_admin'): ?>
                <a href="../inventoryAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Inventory Admin Panel
                </a>
            <?php elseif ($_SESSION['user_role'] === 'information_admin'): ?>
                <a href="../informationAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Information Admin Panel
                </a>
            <?php elseif ($_SESSION['user_role'] === 'super_admin'): ?>
                <a href="../superAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Super Admin Panel
                </a>
            <?php endif; ?>

            <a href="../homePage/profile.php" class="sidebar-link">
                <i class="fas fa-user"></i> Profile
            </a>

            <a href="../homePage/" class="sidebar-link">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>

            <a href="../homePage/borrowing.php" class="sidebar-link">
                <i class="fas fa-boxes"></i> Inventories
            </a>

            <a href="../homePage/memorandumHome.php" class="sidebar-link">
                <i class="fas fa-file-alt"></i> Memorandums
            </a>

            <a href="../homePage/events.php" class="sidebar-link">
                <i class="fas fa-calendar-alt"></i> Events
            </a>

            <a href="../homePage/members.php" class="sidebar-link">
                <i class="fas fa-users"></i> Faculty Members
            </a>

            <a href="../homePage/organization.php" class="sidebar-link">
                <i class="fas fa-sitemap"></i> Organizations
            </a>

            <a href="../homePage/notification.php" class="sidebar-link active">
                <i class="fas fa-bell"></i> Notifications
            </a>
        </div>

        <a href="../logout.php" class="sidebar-footer">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="header">
            <img src="/dionSe/assets/img/CSSPE.png" alt="Logo">
            <h1>CSSPE Inventory & Information System</h1>
        </div>

        <!-- Content -->
        <div class="page-content">
            <div class="content-container">
                <h2 class="page-title">
                    <i class="fas fa-bell"></i>
                    Notifications
                    <span>View your recent notifications</span>
                </h2>

                <!-- Search and Filter -->
                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search notifications..." oninput="searchNotifications()" id="searchInput">
                    <select class="filterDropdown" id="filterDropdown" onchange="filterNotifications()">
                        <option value="">Filter</option>
                        <option value="all">All</option>
                        <option value="day">This day</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                    </select>
                </div>

                <!-- Notifications Container -->
                <div class="notificationsContainer" id="notifContainer">
                    <?php foreach ($notifications as $type => $notificationList): ?>
                        <?php if (!empty($notificationList)): ?>
                            <?php foreach ($notificationList as $notification): ?>
                                <div class="notificationCard"
                                    data-description="<?php echo htmlspecialchars($notification['description']); ?>"
                                    data-created-at="<?php echo htmlspecialchars($notification['formatted_date']); ?>">
                                    <div class="notificationContent">
                                        <h3 class="notificationTitle"><?php echo htmlspecialchars($notification['title']); ?></h3>
                                        <p class="notificationDesc"><?php echo htmlspecialchars($notification['description']); ?></p>
                                        <div class="notificationMeta">
                                            <div class="notificationType">
                                                <i class="fas fa-tag"></i>
                                                <span><?php echo htmlspecialchars($type); ?></span>
                                            </div>
                                            <div class="notificationDate">
                                                <i class="fas fa-calendar"></i>
                                                <span><?php echo htmlspecialchars($notification['formatted_date']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-bell-slash"></i>
                                <h3>No <?php echo htmlspecialchars($type); ?> Notifications</h3>
                                <p>There are currently no <?php echo htmlspecialchars($type); ?> notifications available.</p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Elements
        const toggleBtn = document.querySelector('.toggle-btn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Check if on mobile and show toggle button
        function checkMobile() {
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'flex';
                sidebar.classList.remove('active');
                mainContent.style.marginLeft = '0';

                // Check if sidebar is showing and hide it
                if (sidebar.style.left === '0px') {
                    sidebar.style.left = '-280px';
                    sidebarOverlay.classList.remove('active');
                }
            } else {
                toggleBtn.style.display = 'none';
                sidebar.style.left = '0';
                mainContent.style.marginLeft = sidebar.offsetWidth + 'px';
            }
        }

        // Run on page load
        window.addEventListener('load', checkMobile);
        window.addEventListener('resize', checkMobile);

        // Toggle sidebar on mobile
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');

            if (sidebar.classList.contains('active')) {
                sidebar.style.left = '0';
                sidebarOverlay.classList.add('active');
            } else {
                sidebar.style.left = '-280px';
                sidebarOverlay.classList.remove('active');
            }
        });

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebar.style.left = '-280px';
            sidebarOverlay.classList.remove('active');
        });

        // Search functionality
        function searchNotifications() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let notificationCards = document.querySelectorAll('.notificationCard');

            notificationCards.forEach(card => {
                let description = card.getAttribute('data-description').toLowerCase();
                let title = card.querySelector('.notificationTitle').textContent.toLowerCase();
                card.style.display = (description.includes(input) || title.includes(input)) ? "block" : "none";
            });
        }

        // Filter functionality
        function filterNotifications() {
            let filterValue = document.getElementById('filterDropdown').value;
            let notificationCards = document.querySelectorAll('.notificationCard');
            let currentDate = new Date();

            notificationCards.forEach(card => {
                let createdAt = new Date(card.getAttribute('data-created-at'));
                let show = false;

                if (filterValue === "" || filterValue === "all") {
                    show = true;
                } else if (filterValue === "day") {
                    show = createdAt.toDateString() === currentDate.toDateString();
                } else if (filterValue === "week") {
                    let oneWeekAgo = new Date();
                    oneWeekAgo.setDate(currentDate.getDate() - 7);
                    show = createdAt >= oneWeekAgo;
                } else if (filterValue === "month") {
                    show = createdAt.getMonth() === currentDate.getMonth() &&
                        createdAt.getFullYear() === currentDate.getFullYear();
                }

                card.style.display = show ? "block" : "none";
            });
        }
    </script>
</body>

</html>