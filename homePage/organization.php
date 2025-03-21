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

$query = "SELECT * FROM organizations";
$result = mysqli_query($conn, $query);


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
    <title>Organizations</title>

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Base styles */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        /* Theme colors */
        :root {
            --primary: #6B0D0D;
            --primary-dark: #540A0A;
            --primary-light: rgba(107, 13, 13, 0.1);
            --secondary: #6c757d;
            --secondary-dark: #5a6268;
            --light: #f9f9f9;
            --lighter: #f5f5f5;
            --border: #f0f0f0;
            --text: #333;
            --text-light: #666;
            --white: #fff;
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
            width: calc(100% - 250px);
            min-height: 100vh;
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

        /* Header styling */
        .page-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-header img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .system-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* Content styling */
        .page-content {
            padding: 25px;
            max-width: 1400px;
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
            margin-right: 12px;
            color: var(--primary);
        }

        /* Search container */
        .search-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
            max-width: 500px;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            color: var(--primary);
        }

        /* Organization cards */
        .org-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .org-card {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .org-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .org-image-container {
            width: 100%;
            height: 180px;
            overflow: hidden;
            border-bottom: 1px solid var(--border);
        }

        .org-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .org-card:hover .org-image {
            transform: scale(1.05);
        }

        .org-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .org-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .org-description {
            font-size: 14px;
            color: var(--text);
            line-height: 1.6;
            flex-grow: 1;
            margin-bottom: 15px;
        }

        .org-actions {
            margin-top: auto;
            display: flex;
            gap: 10px;
        }

        .view-details-btn,
        .view-projects-btn {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            flex: 1;
        }

        .view-details-btn {
            background-color: var(--light);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .view-details-btn:hover {
            background-color: var(--lighter);
            border-color: var(--secondary);
        }

        .view-projects-btn {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            box-shadow: 0 2px 5px rgba(107, 13, 13, 0.2);
        }

        .view-projects-btn:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 8px rgba(107, 13, 13, 0.3);
        }

        /* Fallback table view */
        .table-container {
            margin-bottom: 30px;
            overflow-x: auto;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .org-table {
            width: 100%;
            border-collapse: collapse;
        }

        .org-table th {
            background-color: var(--primary);
            color: var(--white);
            text-align: left;
            padding: 12px 15px;
            font-weight: 600;
        }

        .org-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .org-table tr:last-child td {
            border-bottom: none;
        }

        .org-table tr:hover {
            background-color: var(--lighter);
        }

        .table-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: var(--light);
            border-radius: 10px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state h3 {
            color: var(--text-light);
            margin-bottom: 10px;
            font-size: 18px;
        }

        .empty-state p {
            color: #888;
            max-width: 400px;
            margin: 0 auto;
            font-size: 15px;
        }

        /* Organization detail modal */
        .org-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(3px);
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .org-modal-content {
            background-color: var(--white);
            border-radius: 10px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .org-modal-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--white);
            font-size: 20px;
            cursor: pointer;
        }

        .org-modal-body {
            display: flex;
            flex-direction: column;
        }

        .org-modal-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .org-modal-details {
            padding: 20px;
        }

        .org-modal-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }

        .org-modal-description {
            font-size: 15px;
            color: var(--text);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* Projects modal */
        .projects-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(3px);
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .projects-modal-content {
            background-color: var(--white);
            border-radius: 10px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .projects-modal-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .projects-modal-search {
            padding: 15px 20px;
            background-color: var(--light);
            border-bottom: 1px solid var(--border);
            display: flex;
            position: relative;
        }

        .projects-search-input {
            width: 100%;
            padding: 10px 15px 10px 35px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .projects-search-icon {
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
        }

        .projects-modal-body {
            padding: 0;
            overflow-y: auto;
            flex-grow: 1;
        }

        .projects-table {
            width: 100%;
            border-collapse: collapse;
        }

        .projects-table th {
            background-color: var(--primary);
            color: var(--white);
            text-align: left;
            padding: 12px 15px;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .projects-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
        }

        .projects-table tr:hover {
            background-color: var(--lighter);
        }

        .projects-table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        .projects-modal-footer {
            padding: 15px 20px;
            background-color: var(--light);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
        }

        .projects-close-btn {
            background-color: var(--secondary);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .projects-close-btn:hover {
            background-color: var(--secondary-dark);
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

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
                width: calc(100% - 220px);
            }

            .org-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }

            .org-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }

            .page-content {
                padding: 15px;
            }

            .org-image-container {
                height: 160px;
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
                display: block !important;
            }

            .page-header {
                padding-left: 60px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .org-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }

            .org-table th,
            .org-table td,
            .projects-table th,
            .projects-table td {
                padding: 10px;
                font-size: 14px;
            }

            .view-details-btn,
            .view-projects-btn {
                padding: 6px 10px;
                font-size: 13px;
            }
        }

        @media (max-width: 576px) {
            .org-grid {
                grid-template-columns: 1fr;
            }

            .org-image-container {
                height: 180px;
            }

            .org-modal-content,
            .projects-modal-content {
                max-width: 100%;
            }

            .org-modal-image {
                height: 200px;
            }

            .org-actions {
                flex-direction: column;
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--lighter);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Focus states for accessibility */
        button:focus,
        a:focus,
        input:focus {
            outline: 2px solid rgba(107, 13, 13, 0.5);
            outline-offset: 2px;
        }

        /* Display property utility */
        .d-none {
            display: none !important;
        }
    </style>
</head>

<body>
    <!-- Toggle Sidebar Button -->
    <button class="toggle-btn" id="toggleSidebar">
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

            <a href="../homePage/organization.php" class="sidebar-link active">
                <i class="fas fa-sitemap"></i> Organizations
            </a>

            <a href="../homePage/notification.php" class="sidebar-link">
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
        <header class="page-header">
            <img src="../assets/img/CSSPE.png" alt="Logo">
            <h1 class="system-title">CSSPE Inventory & Information System</h1>
        </header>

        <!-- Content -->
        <div class="page-content">
            <h2 class="page-title">
                <i class="fas fa-sitemap"></i> Organizations
            </h2>

            <!-- Search Bar -->
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input id="searchInput" class="search-input" type="text" placeholder="Search organizations...">
            </div>

            <!-- Organizations Grid -->
            <div class="org-grid" id="orgGrid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php mysqli_data_seek($result, 0); // Reset result pointer 
                        ?>
                    <?php while ($org = mysqli_fetch_assoc($result)): ?>
                        <div class="org-card" data-name="<?= htmlspecialchars($org['organization_name']) ?>">
                            <div class="org-image-container">
                                <img class="org-image" src="<?= htmlspecialchars($org['image']) ?>"
                                    alt="<?= htmlspecialchars($org['organization_name']) ?>">
                            </div>
                            <div class="org-content">
                                <h3 class="org-name"><?= htmlspecialchars($org['organization_name']) ?></h3>
                                <p class="org-description"><?= htmlspecialchars($org['description']) ?></p>
                                <div class="org-actions">
                                    <button class="view-details-btn"
                                        onclick="showOrgDetails('<?= htmlspecialchars(addslashes($org['organization_name'])) ?>', '<?= htmlspecialchars(addslashes($org['description'])) ?>', '<?= htmlspecialchars($org['image']) ?>')">
                                        <i class="fas fa-info-circle"></i> Details
                                    </button>
                                    <button class="view-projects-btn"
                                        onclick="showOrgProjects('<?= htmlspecialchars(addslashes($org['organization_name'])) ?>', <?= $org['id'] ?>)">
                                        <i class="fas fa-project-diagram"></i> Projects
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-sitemap"></i>
                        <h3>No organizations found</h3>
                        <p>There are currently no organizations in the system.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Fallback Table View (hidden by default) -->
            <div class="table-container d-none">
                <table class="org-table">
                    <thead>
                        <tr>
                            <th>Organization Name</th>
                            <th>Image</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php mysqli_data_seek($result, 0); // Reset result pointer 
                                ?>
                            <?php while ($org = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($org['organization_name']) ?></td>
                                    <td><img class="table-image" src="<?= htmlspecialchars($org['image']) ?>"
                                            alt="<?= htmlspecialchars($org['organization_name']) ?>"></td>
                                    <td><?= htmlspecialchars($org['description']) ?></td>
                                    <td class="table-actions">
                                        <button class="view-details-btn"
                                            onclick="showOrgDetails('<?= htmlspecialchars(addslashes($org['organization_name'])) ?>', '<?= htmlspecialchars(addslashes($org['description'])) ?>', '<?= htmlspecialchars($org['image']) ?>')">
                                            <i class="fas fa-info-circle"></i> Details
                                        </button>
                                        <button class="view-projects-btn"
                                            onclick="showOrgProjects('<?= htmlspecialchars(addslashes($org['organization_name'])) ?>')">
                                            <i class="fas fa-project-diagram"></i> Projects
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No organizations found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Organization Detail Modal -->
    <div id="orgModal" class="org-modal">
        <div class="org-modal-content">
            <div class="org-modal-header">
                <span id="orgModalTitle">Organization Details</span>
                <button class="close-modal" onclick="closeOrgModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="org-modal-body">
                <img id="orgModalImage" class="org-modal-image" src="" alt="Organization Image">
                <div class="org-modal-details">
                    <h3 id="orgModalName" class="org-modal-title">Organization Name</h3>
                    <div id="orgModalDescription" class="org-modal-description">
                        Organization description goes here.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Modal -->
    <div id="projectsModal" class="projects-modal">
        <div class="projects-modal-content">
            <div class="projects-modal-header">
                <span id="projectsModalTitle">Organization Projects</span>
                <button class="close-modal" onclick="closeProjectsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="projects-modal-search">
                <i class="fas fa-search projects-search-icon"></i>
                <input id="projectsSearchInput" class="projects-search-input" type="text"
                    placeholder="Search projects...">
            </div>
            <div class="projects-modal-body">
                <table class="projects-table" id="projectsTable">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Image</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="projectsTableBody">
                        <!-- Sample project data - will be populated dynamically -->
                    </tbody>
                </table>
            </div>
            <div class="projects-modal-footer">
                <button class="projects-close-btn" onclick="closeProjectsModal()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const searchInput = document.getElementById('searchInput');
        const orgGrid = document.getElementById('orgGrid');
        const orgCards = document.querySelectorAll('.org-card');
        const orgModal = document.getElementById('orgModal');
        const orgModalTitle = document.getElementById('orgModalTitle');
        const orgModalName = document.getElementById('orgModalName');
        const orgModalDescription = document.getElementById('orgModalDescription');
        const orgModalImage = document.getElementById('orgModalImage');
        const projectsModal = document.getElementById('projectsModal');
        const projectsModalTitle = document.getElementById('projectsModalTitle');
        const projectsSearchInput = document.getElementById('projectsSearchInput');
        const projectsTableBody = document.getElementById('projectsTableBody');

        // Sample project data (would come from database in real implementation)
        const projectsData = {
            'Tech Club': [{
                name: 'Smart Campus App',
                image: '../assets/img/CSSPE.png',
                description: 'A mobile app to navigate campus resources.'
            },
            {
                name: 'IoT Weather Station',
                image: '../assets/img/CSSPE.png',
                description: 'Real-time weather monitoring system.'
            },
            {
                name: 'Automated Attendance System',
                image: '../assets/img/CSSPE.png',
                description: 'RFID-based attendance tracking system.'
            }
            ],
            'Science Society': [{
                name: 'Biodiversity Survey',
                image: '../assets/img/CSSPE.png',
                description: 'Ecological study of campus flora and fauna.'
            },
            {
                name: 'Water Quality Testing',
                image: '../assets/img/CSSPE.png',
                description: 'Analysis of campus water sources.'
            }
            ],
            'Arts Club': [{
                name: 'Annual Art Exhibition',
                image: '../assets/img/CSSPE.png',
                description: 'Showcase of student artwork.'
            },
            {
                name: 'Mural Project',
                image: '../assets/img/CSSPE.png',
                description: 'Campus beautification initiative.'
            },
            {
                name: 'Digital Media Workshop',
                image: '../assets/img/CSSPE.png',
                description: 'Training in digital art tools.'
            }
            ]
        };

        // Check if on mobile and show toggle button
        function checkMobile() {
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
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
                mainContent.style.width = `calc(100% - ${sidebar.offsetWidth}px)`;
            }
        }

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

        // Search organizations
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            let hasVisibleCards = false;

            orgCards.forEach(card => {
                const orgName = card.getAttribute('data-name').toLowerCase();

                if (orgName.includes(searchTerm)) {
                    card.style.display = '';
                    hasVisibleCards = true;
                } else {
                    card.style.display = 'none';
                }
            });

            // Check if any cards are visible
            if (!hasVisibleCards) {
                // If no existing empty state, add one
                if (!document.querySelector('.empty-state')) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'empty-state';
                    emptyState.style.gridColumn = '1 / -1';
                    emptyState.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h3>No organizations found</h3>
                        <p>Try adjusting your search criteria.</p>
                    `;
                    orgGrid.appendChild(emptyState);
                }
            } else {
                // Remove empty state if it exists
                const emptyState = document.querySelector('.empty-state');
                if (emptyState) {
                    emptyState.remove();
                }
            }
        });

        // Show organization details in modal
        function showOrgDetails(name, description, image) {
            orgModalTitle.textContent = 'Organization Details';
            orgModalName.textContent = name;
            orgModalDescription.textContent = description;
            orgModalImage.src = image;
            orgModalImage.alt = name;

            // Show modal
            orgModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        // Close organization modal
        function closeOrgModal() {
            orgModal.style.display = 'none';
            document.body.style.overflow = ''; // Enable scrolling
        }

        // Show organization projects in modal
        function showOrgProjects(orgName, orgId) {
            projectsModalTitle.textContent = orgName + ' - Projects';

            // Fetch projects from the server
            fetch(`fetch_projects.php?organization_id=${orgId}`)
                .then(response => response.json())
                .then(projects => {
                    // Populate projects table
                    populateProjectsTable(projects);
                })
                .catch(error => {
                    console.error('Error fetching projects:', error);
                });

            // Show modal
            projectsModal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        // Populate projects table based on organization name
        function populateProjectsTable(projects) {
            // Clear table first
            projectsTableBody.innerHTML = '';

            // If no projects, show message
            if (projects.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `
            <td colspan="3" style="text-align: center; padding: 20px;">
                No projects found for this organization.
            </td>
        `;
                projectsTableBody.appendChild(emptyRow);
                return;
            }

            // Add each project to the table
            projects.forEach(project => {
                const row = document.createElement('tr');
                // Use default image if project.image is empty
                const projectImage = project.image || '../assets/img/CSSPE.png';
                row.innerHTML = `
            <td>${project.project_name}</td>
            <td><img src="${projectImage}" alt="${project.project_name}" /></td>
            <td>${project.description}</td>
        `;
                projectsTableBody.appendChild(row);
            });
        }

        // Close projects modal
        function closeProjectsModal() {
            projectsModal.style.display = 'none';
            document.body.style.overflow = ''; // Enable scrolling
        }



        // Search projects within the projects modal
        projectsSearchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = projectsTableBody.querySelectorAll('tr');
            let hasVisibleRows = false;

            rows.forEach(row => {
                // Skip empty state row
                if (row.cells.length === 1) return;

                const projectName = row.cells[0].textContent.toLowerCase();
                const projectDesc = row.cells[2].textContent.toLowerCase();

                if (projectName.includes(searchTerm) || projectDesc.includes(searchTerm)) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Check if any rows are visible
            if (!hasVisibleRows) {
                // Remove existing empty row if it exists
                const existingEmptyRow = projectsTableBody.querySelector('tr td[colspan="3"]')?.parentNode;
                if (existingEmptyRow) existingEmptyRow.remove();

                // Add empty state row
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `
                    <td colspan="3" style="text-align: center; padding: 20px;">
                        No projects found matching your search.
                    </td>
                `;
                projectsTableBody.appendChild(emptyRow);
            } else {
                // Remove empty state row if it exists
                const emptyRow = projectsTableBody.querySelector('tr td[colspan="3"]')?.parentNode;
                if (emptyRow) emptyRow.remove();
            }
        });

        // Close modals when clicking outside content
        orgModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeOrgModal();
            }
        });

        projectsModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeProjectsModal();
            }
        });

        // Initialize
        window.addEventListener('load', checkMobile);
        window.addEventListener('resize', checkMobile);
    </script>
</body>

</html>