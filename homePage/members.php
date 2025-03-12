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

$instructorsQuery = "SELECT first_name, middle_name, last_name, email, address, contact_no, rank, image, department FROM users WHERE role = 'Instructor'";
$instructorsResult = $conn->query($instructorsQuery);

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

        /* Search and filter controls */
        .controls-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
            background-color: var(--white);
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .search-container {
            flex: 1;
            min-width: 200px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
        }

        .filter-select {
            min-width: 180px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.1);
        }

        /* Faculty cards grid */
        .faculty-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .faculty-card {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .faculty-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .faculty-image-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .faculty-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .faculty-card:hover .faculty-image {
            transform: scale(1.05);
        }

        .faculty-details {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .faculty-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--primary);
        }

        .faculty-position {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .faculty-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .faculty-info-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 14px;
            color: var(--text);
        }

        .faculty-info-item i {
            color: var(--primary);
            margin-top: 3px;
            width: 16px;
            text-align: center;
        }

        .faculty-info-text {
            flex: 1;
            word-break: break-word;
        }

        .faculty-department {
            margin-top: auto;
            padding-top: 10px;
            font-weight: 500;
            font-size: 14px;
            color: var(--text);
            border-top: 1px solid var(--border);
        }

        /* Traditional Table View (as fallback) */
        .table-container {
            overflow-x: auto;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        .faculty-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
        }

        .faculty-table th {
            background-color: var(--primary);
            color: var(--white);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .faculty-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .faculty-table tr:hover {
            background-color: var(--lighter);
        }

        .faculty-table tr:last-child td {
            border-bottom: none;
        }

        .table-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
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

            .faculty-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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

            .faculty-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 20px;
            }

            .page-content {
                padding: 15px;
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

            .controls-container {
                flex-direction: column;
                align-items: stretch;
            }

            .faculty-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .faculty-table th,
            .faculty-table td {
                padding: 10px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.5rem;
            }

            .faculty-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .faculty-image-container {
                height: 180px;
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
        input:focus,
        select:focus {
            outline: 2px solid rgba(107, 13, 13, 0.5);
            outline-offset: 2px;
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

            <a href="../homePage/members.php" class="sidebar-link active">
                <i class="fas fa-users"></i> Faculty Members
            </a>

            <a href="../homePage/organization.php" class="sidebar-link">
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
                <i class="fas fa-users"></i> Faculty Members
            </h2>

            <!-- Controls -->
            <div class="controls-container">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input id="searchBar" class="search-input" type="text" placeholder="Search by name...">
                </div>
                <select id="rankFilter" class="filter-select">
                    <option value="">All Positions</option>
                    <option value="Instructor">Instructor</option>
                    <option value="Assistant Professor">Assistant Professor</option>
                    <option value="Associate Professor">Associate Professor</option>
                    <option value="Professor">Professor</option>
                </select>
            </div>

            <!-- Faculty Cards Grid -->
            <div class="faculty-grid" id="facultyGrid">
                <?php if ($instructorsResult->num_rows > 0): ?>
                    <?php mysqli_data_seek($instructorsResult, 0); // Reset result pointer 
                    ?>
                    <?php while ($instructor = $instructorsResult->fetch_assoc()): ?>
                        <div class="faculty-card" data-name="<?= htmlspecialchars($instructor['first_name'] . ' ' . ($instructor['middle_name'] ? $instructor['middle_name'] . ' ' : '') . $instructor['last_name']) ?>" data-position="<?= htmlspecialchars($instructor['rank']) ?>">
                            <div class="faculty-image-container">
                                <img class="faculty-image" src="../assets/img/<?= htmlspecialchars($instructor['image'] ?: 'CSSPE.png') ?>" alt="Faculty Image">
                            </div>
                            <div class="faculty-details">
                                <h3 class="faculty-name"><?= htmlspecialchars($instructor['first_name'] . ' ' . ($instructor['middle_name'] ? $instructor['middle_name'] . ' ' : '') . $instructor['last_name']) ?></h3>
                                <div class="faculty-position"><?= htmlspecialchars($instructor['rank']) ?></div>
                                <div class="faculty-info">
                                    <div class="faculty-info-item">
                                        <i class="fas fa-envelope"></i>
                                        <div class="faculty-info-text"><?= htmlspecialchars($instructor['email']) ?></div>
                                    </div>
                                    <div class="faculty-info-item">
                                        <i class="fas fa-phone"></i>
                                        <div class="faculty-info-text"><?= htmlspecialchars($instructor['contact_no']) ?></div>
                                    </div>
                                    <div class="faculty-info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div class="faculty-info-text"><?= htmlspecialchars($instructor['address']) ?></div>
                                    </div>
                                </div>
                                <div class="faculty-department">
                                    <i class="fas fa-building"></i> <?= htmlspecialchars($instructor['department'] ?: 'No Department') ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-user-slash"></i>
                        <h3>No faculty members found</h3>
                        <p>There are currently no faculty members in the system.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Fallback Table View (in case the cards don't work) -->
            <div class="table-container" style="display: none;">
                <table class="faculty-table">
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
                    <tbody>
                        <?php if ($instructorsResult->num_rows > 0): ?>
                            <?php mysqli_data_seek($instructorsResult, 0); // Reset result pointer 
                            ?>
                            <?php while ($instructor = $instructorsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($instructor['first_name'] . ' ' . ($instructor['middle_name'] ? $instructor['middle_name'] . ' ' : '') . $instructor['last_name']) ?></td>
                                    <td>
                                        <img class="table-image" src="../assets/img/<?= htmlspecialchars($instructor['image'] ?: 'CSSPE.png') ?>" alt="Faculty Image">
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

    <script>
        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const searchBar = document.getElementById('searchBar');
        const rankFilter = document.getElementById('rankFilter');
        const facultyGrid = document.getElementById('facultyGrid');
        const facultyCards = document.querySelectorAll('.faculty-card');

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

        // Filter faculty cards
        function filterFaculty() {
            const searchTerm = searchBar.value.toLowerCase();
            const selectedRank = rankFilter.value;
            let hasVisibleCards = false;

            facultyCards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const position = card.getAttribute('data-position');

                const matchesSearch = name.includes(searchTerm);
                const matchesRank = !selectedRank || position === selectedRank;

                if (matchesSearch && matchesRank) {
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
                        <h3>No faculty members found</h3>
                        <p>Try adjusting your search or filter criteria.</p>
                    `;
                    facultyGrid.appendChild(emptyState);
                }
            } else {
                // Remove empty state if it exists
                const emptyState = document.querySelector('.empty-state');
                if (emptyState) {
                    emptyState.remove();
                }
            }
        }

        // Add event listeners
        searchBar.addEventListener('input', filterFaculty);
        rankFilter.addEventListener('change', filterFaculty);

        // Initialize on page load
        window.addEventListener('load', checkMobile);
        window.addEventListener('resize', checkMobile);
    </script>
</body>

</html>