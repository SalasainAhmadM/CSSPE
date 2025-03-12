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

        /* Search bar and filters */
        .searchContainer {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .searchBar {
            flex: 1;
            min-width: 200px;
            padding: 12px 20px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .searchBar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 2px 12px rgba(107, 13, 13, 0.1);
        }

        .searchContainer::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 18px;
            top: 13px;
            color: var(--primary);
            font-size: 16px;
            z-index: 1;
        }

        .addButton,
        .addButton1,
        .deleteButton {
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .addButton {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 5px rgba(107, 13, 13, 0.2);
        }

        .addButton:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 10px rgba(107, 13, 13, 0.3);
            transform: translateY(-2px);
        }

        .addButton1,
        .deleteButton {
            background-color: var(--secondary);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .addButton1:hover,
        .deleteButton:hover {
            background-color: var(--secondary-dark);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .deleteButton {
            background-color: #dc3545;
        }

        .deleteButton:hover {
            background-color: #bd2130;
        }

        .size {
            min-width: 120px;
        }

        /* Notification Cards */
        .dashboardContainer {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }

        .notificationContainer {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-left: 4px solid var(--primary);
        }

        .notificationContainer:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .subNotificaitonContainer {
            flex-grow: 1;
            padding-right: 40px;
        }

        .messageContainer {
            margin-bottom: 12px;
        }

        .messageContainer p {
            margin: 0;
            color: var(--text);
            font-size: 16px;
            line-height: 1.5;
        }

        .dateContainer {
            display: flex;
            align-items: center;
        }

        .dateContainer p {
            margin: 0;
            color: var(--text-light);
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .dateContainer p i {
            margin-right: 6px;
            color: var(--primary);
        }

        .deleteContainer {
            position: relative;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: var(--light);
            border-radius: 10px;
            margin: 30px 0;
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
                flex-direction: column;
                align-items: flex-start;
            }

            .searchBar {
                max-width: 100%;
                width: 100%;
            }

            .page-title {
                font-size: 1.5rem;
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

            .notificationContainer {
                flex-direction: column;
            }

            .subNotificaitonContainer {
                padding-right: 0;
                margin-bottom: 15px;
                width: 100%;
            }

            .deleteContainer {
                align-self: flex-end;
            }
        }

        @media (max-width: 576px) {
            .searchBar {
                padding: 10px 15px 10px 40px;
                font-size: 14px;
            }

            .addButton,
            .addButton1,
            .deleteButton {
                padding: 8px 12px;
                font-size: 13px;
            }

            .messageContainer p {
                font-size: 15px;
            }

            .dateContainer p {
                font-size: 13px;
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

            body.dark-mode-supported .searchBar {
                background-color: #222;
                border-color: #444;
                color: #eee;
            }

            body.dark-mode-supported select.addButton {
                background-color: var(--primary);
                border-color: #444;
                color: #eee;
            }

            body.dark-mode-supported .notificationContainer {
                border-color: var(--primary);
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
            <a href="../homePage/" class="sidebar-link">
                <i class="fas fa-home"></i> Home
            </a>


            <a href="../inventoryAdmin/index.php" class="sidebar-link">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>

            <a href="../inventoryAdmin/inventory.php" class="sidebar-link">
                <i class="fas fa-boxes"></i> Inventories
            </a>

            <a href="../inventoryAdmin/borrowing.php" class="sidebar-link">
                <i class="fas fa-clipboard-list"></i> Borrow Request
            </a>

            <a href="../inventoryAdmin/borrowItem.php" class="sidebar-link">
                <i class="fas fa-hand-holding"></i> Borrowed Item
            </a>

            <a href="../inventoryAdmin/notification.php" class="sidebar-link active">
                <i class="fas fa-bell"></i> Notification
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
            <img src="../assets/img/CSSPE.png" alt="Logo">
            <h1>CSSPE Inventory & Information System</h1>
        </div>

        <!-- Content -->
        <div class="page-content">
            <div class="content-container">
                <h2 class="page-title">
                    <i class="fas fa-bell"></i>
                    Notifications
                    <span>Manage system notifications</span>
                </h2>

                <!-- Search & Filter -->
                <div style="position: relative;" class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search notifications..." oninput="searchCard()"
                        id="searchInput">

                    <select class="addButton size" id="filterDropdown" onchange="filterByDate()">
                        <option value="">Filter by date</option>
                        <option value="all">All notifications</option>
                        <option value="day">Today</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                    </select>
                </div>

                <!-- Notifications List -->
                <div class="dashboardContainer" id="notifContainer">
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
                                        <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($notif['created_at']); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="deleteContainer">
                                    <button class="deleteButton">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <h3>No Notifications</h3>
                            <p>There are currently no notifications in the system.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 JavaScript -->
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
        toggleBtn.addEventListener('click', function () {
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
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('active');
            sidebar.style.left = '-280px';
            sidebarOverlay.classList.remove('active');
        });

        // Search functionality
        function searchCard() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let notifContainers = document.querySelectorAll('.notificationContainer');

            notifContainers.forEach(container => {
                let description = container.getAttribute('data-description').toLowerCase();
                if (description.includes(input)) {
                    container.style.display = "flex";
                } else {
                    container.style.display = "none";
                }
            });
        }

        // Filter by date
        function filterByDate() {
            let filterValue = document.getElementById('filterDropdown').value;
            let notifContainers = document.querySelectorAll('.notificationContainer');
            let currentDate = new Date();

            notifContainers.forEach(container => {
                let createdAt = new Date(container.getAttribute('data-created-at'));
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

                container.style.display = show ? "flex" : "none";
            });

            // Check if we need to show empty state
            const visibleContainers = document.querySelectorAll('.notificationContainer[style*="display: flex"]');
            const emptyState = document.querySelector('.empty-state');
            const notifContainer = document.getElementById('notifContainer');

            if (visibleContainers.length === 0 && !emptyState) {
                // Create and append empty state for filtered results
                const tempEmptyState = document.createElement('div');
                tempEmptyState.className = 'empty-state';
                tempEmptyState.id = 'temp-empty-state';
                tempEmptyState.innerHTML = `
                    <i class="fas fa-filter"></i>
                    <h3>No Matching Notifications</h3>
                    <p>No notifications match your current filter.</p>
                `;
                notifContainer.appendChild(tempEmptyState);
            } else if (visibleContainers.length > 0) {
                // Remove temporary empty state if it exists
                const tempEmptyState = document.getElementById('temp-empty-state');
                if (tempEmptyState) {
                    tempEmptyState.remove();
                }
            }
        }

        // Delete notification
        document.addEventListener('DOMContentLoaded', () => {
            const deleteButtons = document.querySelectorAll('.deleteButton');

            deleteButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const notificationContainer = e.target.closest('.notificationContainer');
                    const notifId = notificationContainer.dataset.notifId;

                    Swal.fire({
                        title: 'Delete Notification',
                        text: "Are you sure you want to delete this notification?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
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
                                            confirmButtonColor: '#6B0D0D',
                                            showConfirmButton: false,
                                            timer: 3000
                                        });

                                        if (response.status === 'success') {
                                            // Remove notification with animation
                                            notificationContainer.style.opacity = '0';
                                            notificationContainer.style.height = '0';
                                            notificationContainer.style.margin = '0';
                                            notificationContainer.style.padding = '0';
                                            notificationContainer.style.overflow = 'hidden';
                                            notificationContainer.style.transition = 'all 0.3s ease';

                                            setTimeout(() => {
                                                notificationContainer.remove();

                                                // Check if there are any remaining notifications
                                                const remainingNotifications = document.querySelectorAll('.notificationContainer');
                                                if (remainingNotifications.length === 0) {
                                                    // Add empty state if all notifications are deleted
                                                    const emptyState = document.createElement('div');
                                                    emptyState.className = 'empty-state';
                                                    emptyState.innerHTML = `
                                                        <i class="fas fa-bell-slash"></i>
                                                        <h3>No Notifications</h3>
                                                        <p>There are currently no notifications in the system.</p>
                                                    `;
                                                    document.getElementById('notifContainer').appendChild(emptyState);
                                                }
                                            }, 300);
                                        }
                                    } catch (error) {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'An error occurred while processing your request.',
                                            confirmButtonColor: '#6B0D0D',
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    }
                                }
                            };
                            xhr.send(JSON.stringify({
                                id: notifId
                            }));
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>