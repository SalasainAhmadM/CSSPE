<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('information_admin');

$informationAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $informationAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $image = $row['image'];
} else {
    $fullName = "User Not Found";
}

$query = "SELECT * FROM events";
$result = mysqli_query($conn, $query);

if (isset($_POST['add_event'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $date_uploaded_at = date('Y-m-d H:i:s');

    $query = "INSERT INTO events (title, description, date_uploaded_at) 
              VALUES ('$title', '$description', '$date_uploaded_at')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Event added successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if (isset($_POST['update_event'])) {
    $event_id = $_POST['event_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $current_time = date('H:i:s');
    $date_uploaded_at = $date . ' ' . $current_time;

    $query = "UPDATE events SET title = '$title', description = '$description', date_uploaded_at = '$date_uploaded_at' WHERE id = $event_id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Event updated successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if (isset($_GET['delete_id'])) {
    $event_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM events WHERE id = $event_id";

    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Event deleted successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Calendar</title>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="../assets/css/output.css">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease forwards;
        }

        .event-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .calendar-day:hover {
            background-color: #f3f4f6;
        }

        .calendar-day.has-events {
            cursor: pointer;
        }

        .calendar-day.active {
            background-color: #fef2f2;
            border-color: #ef4444;
        }

        .calendar-day.today {
            font-weight: bold;
            color: #ef4444;
        }

        .calendar-day.other-month {
            color: #9ca3af;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-100 min-h-screen">
    <!-- Toggle Sidebar Button (mobile only) -->
    <button id="toggleSidebar" class="fixed top-4 left-4 z-50 lg:hidden bg-red-900 text-white p-2 rounded-md shadow-md flex items-center justify-center w-10 h-10 hover:bg-red-800 transition-colors">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden backdrop-blur-sm transition-opacity duration-300"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-red-900 text-white shadow-lg overflow-y-auto transition-transform duration-300 ease-in-out lg:translate-x-0 -translate-x-full z-40">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-red-800 flex items-center gap-3">
            <img src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover">
            <span class="font-bold truncate"><?php echo $fullName; ?></span>
        </div>

        <!-- Navigation Links -->
        <nav class="flex flex-col py-2">
            <a href="../homePage/" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-home w-5"></i> Home
            </a>
            <a href="../informationAdmin/" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-building w-5"></i> Departments
            </a>
            <a href="../informationAdmin/facultyMember.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-user-tie w-5"></i> Faculty Members
            </a>
            <a href="../informationAdmin/organization.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-sitemap w-5"></i> Organizations
            </a>
            <a href="../informationAdmin/memorandum.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-file-alt w-5"></i> Memorandums
            </a>
            <a href="../informationAdmin/announcement.php" class="text-white py-3 px-4 hover:bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-transparent">
                <i class="fas fa-bullhorn w-5"></i> Announcements
            </a>
            <a href="../informationAdmin/events.php" class="text-white py-3 px-4 bg-red-800/40 transition-colors duration-200 flex items-center gap-3 border-l-4 border-white">
                <i class="fas fa-calendar-alt w-5"></i> Events
            </a>
        </nav>

        <!-- Logout Link -->
        <a href="../logout.php" class="text-white py-3 px-4 hover:bg-red-800 transition-colors duration-200 absolute bottom-0 w-full border-t border-red-800 flex items-center gap-3">
            <i class="fas fa-sign-out-alt w-5"></i> Logout
        </a>
    </aside>

    <!-- Main Content -->
    <main id="mainContent" class="lg:ml-64 transition-all duration-300 ease-in-out min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-red-900 text-white p-4 flex items-center gap-3 shadow-md">
            <img src="../assets/img/CSSPE.png" alt="Logo" class="w-8 h-8 object-contain">
            <h1 class="text-xl font-bold">CSSPE Inventory & Information System</h1>
        </header>

        <!-- Page Content -->
        <div class="p-4 md:p-6 flex-grow">
            <div class="max-w-6xl mx-auto">
                <!-- Page Title -->
                <div class="flex items-center pb-4 mb-6 border-b border-gray-200">
                    <div class="flex items-center justify-center bg-gray-100 rounded-full p-3 mr-4 text-red-900">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Events Calendar</h2>
                        <p class="text-sm text-gray-500 hidden md:block">Manage college events and activities</p>
                    </div>
                </div>

                <!-- Calendar Actions -->
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <div class="flex-grow flex items-center">
                        <button id="prevMonth" class="p-2 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h3 id="currentMonthYear" class="text-xl font-bold mx-4 flex-grow text-center"></h3>
                        <button id="nextMonth" class="p-2 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button onclick="printCalendar()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2 shadow-sm min-w-[120px] justify-center">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button onclick="addProgram()" class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2 shadow-sm min-w-[120px] justify-center">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                    </div>
                </div>

                <!-- Calendar and Event List Container -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Calendar Section -->
                    <div class="lg:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
                        <!-- Weekday Headers -->
                        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
                            <div class="py-2 text-center font-semibold text-gray-600">Sun</div>
                            <div class="py-2 text-center font-semibold text-gray-600">Mon</div>
                            <div class="py-2 text-center font-semibold text-gray-600">Tue</div>
                            <div class="py-2 text-center font-semibold text-gray-600">Wed</div>
                            <div class="py-2 text-center font-semibold text-gray-600">Thu</div>
                            <div class="py-2 text-center font-semibold text-gray-600">Fri</div>
                            <div class="py-2 text-center font-semibold text-gray-600">Sat</div>
                        </div>

                        <!-- Calendar Grid -->
                        <div id="calendarGrid" class="grid grid-cols-7 grid-rows-6 auto-rows-fr border-gray-200"></div>
                    </div>

                    <!-- Events List Section -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex items-center justify-between">
                            <h3 id="selectedDate" class="font-semibold text-gray-700">Events</h3>
                            <div id="eventCount" class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">0 events</div>
                        </div>
                        <div id="eventsList" class="p-4 max-h-[475px] overflow-y-auto">
                            <div id="noEvents" class="text-center py-6 text-gray-500">
                                <i class="fas fa-calendar-day text-3xl mb-2"></i>
                                <p>No events for the selected date</p>
                            </div>
                            <div id="eventItems" class="space-y-3 hidden">
                                <!-- Event items will be dynamically populated here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Events List (Table) -->
                <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700">All Events</h3>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="search" class="pl-10 pr-4 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm" placeholder="Search events...">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="p-3 text-left font-semibold text-red-900">Title</th>
                                    <th class="p-3 text-left font-semibold text-red-900">Description</th>
                                    <th class="p-3 text-left font-semibold text-red-900">Date/Time</th>
                                    <th class="p-3 text-left font-semibold text-red-900">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50" data-date="<?php echo date('Y-m-d', strtotime($row['date_uploaded_at'])); ?>">
                                        <td class="p-3"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($row['date_uploaded_at']); ?></td>
                                        <td class="p-3">
                                            <div class="flex flex-wrap gap-2">
                                                <button onclick="editProgram(
                                                    <?php echo $row['id'] ?>,
                                                    '<?php echo addslashes($row['title']); ?>',
                                                    '<?php echo addslashes($row['description']); ?>',
                                                    '<?php echo addslashes(date('Y-m-d', strtotime($row['date_uploaded_at']))); ?>')"
                                                    class="bg-red-900 hover:bg-red-800 text-white px-3 py-1 rounded transition duration-200 text-sm flex items-center justify-center gap-1">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button onclick="deleteEvent(<?php echo $row['id']; ?>)"
                                                    class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded transition duration-200 text-sm flex items-center justify-center gap-1">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Event Modal -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div id="addModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto animate-fadeIn">
                <div class="bg-red-900 text-white px-6 py-4 flex items-center justify-between rounded-t-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-plus-circle text-xl"></i>
                        <h3 class="text-xl font-bold">Add Event</h3>
                    </div>
                    <button type="button" onclick="addProgram()" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Title -->
                    <div class="mb-4">
                        <label for="add_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" id="add_title" name="title" placeholder="Enter event title" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Date -->
                    <div class="mb-4">
                        <label for="add_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" id="add_date" name="date" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" placeholder="Enter event details" required rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-y"></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="addProgram()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="add_event"
                            class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Edit Event Modal -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div id="editModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto animate-fadeIn">
                <div class="bg-red-900 text-white px-6 py-4 flex items-center justify-between rounded-t-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-edit text-xl"></i>
                        <h3 class="text-xl font-bold">Edit Event</h3>
                    </div>
                    <button type="button" onclick="cancelContainer()" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Hidden input to store event id -->
                    <input type="hidden" name="event_id" id="event_id">

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="event_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" id="event_title" name="title" placeholder="Enter event title" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Date -->
                    <div class="mb-4">
                        <label for="event_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" id="event_date" name="date" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="event_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="event_description" name="description" placeholder="Enter event details" required rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-y"></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="cancelContainer()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="update_event"
                            class="bg-red-900 hover:bg-red-800 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Event Details Tooltip -->
    <div id="eventTooltip" class="hidden fixed bg-white rounded-lg shadow-lg p-4 z-50 max-w-xs animate-fadeIn">
        <h4 class="font-bold text-red-900 text-lg mb-1"></h4>
        <p class="text-gray-600 text-sm mb-2"></p>
        <p class="text-gray-500 text-xs"></p>
    </div>

    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Global variables
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();
        let selectedDate = new Date();
        selectedDate.setHours(0, 0, 0, 0);
        let events = [];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Collect events from table rows
            collectEvents();

            // Initialize calendar
            updateCalendar();

            // Set up event listeners
            document.getElementById('prevMonth').addEventListener('click', previousMonth);
            document.getElementById('nextMonth').addEventListener('click', nextMonth);

            // Setup sidebar toggle
            setupSidebar();

            // Setup search
            setupSearch();

            // Display SweetAlert messages if any
            displayMessages();
        });

        // Collect events from table rows
        function collectEvents() {
            events = [];
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const dateAttr = row.getAttribute('data-date');

                // Ensure date is in YYYY-MM-DD format
                let date = dateAttr;
                if (!date) {
                    // If no data-date attribute, try to extract it from the date column
                    const dateCell = row.cells[2].textContent;
                    // Convert to YYYY-MM-DD format if possible
                    const dateObj = new Date(dateCell);
                    if (!isNaN(dateObj.getTime())) {
                        date = dateObj.toISOString().split('T')[0];
                    }
                }

                const title = row.cells[0].textContent;
                const description = row.cells[1].textContent;
                const datetime = row.cells[2].textContent;

                // Extract ID from the Edit button's onclick attribute
                const editButton = row.querySelector('button');
                const onclickAttr = editButton ? editButton.getAttribute('onclick') : '';
                const idMatch = onclickAttr ? onclickAttr.match(/\d+/) : null;
                const id = idMatch ? idMatch[0] : 'unknown';

                console.log("Adding event:", {
                    id,
                    title,
                    date,
                    description,
                    datetime
                });

                events.push({
                    id: id,
                    title: title,
                    description: description,
                    date: date,
                    datetime: datetime
                });
            });

            // Sort events by date
            events.sort((a, b) => new Date(a.date) - new Date(b.date));
            console.log("All events:", events);
        }
        // Update calendar display
        function updateCalendar() {
            // Update month/year display
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            document.getElementById('currentMonthYear').textContent = `${monthNames[currentMonth]} ${currentYear}`;

            // Get first day of month and total days
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

            // Get last day of previous month
            const prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

            // Clear calendar grid
            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';

            // Fill calendar grid
            let dayCount = 1;
            let nextMonthDay = 1;

            // Create 6 rows for the calendar (max possible)
            for (let i = 0; i < 6; i++) {
                for (let j = 0; j < 7; j++) {
                    const dayCell = document.createElement('div');
                    dayCell.className = 'calendar-day border border-gray-200 p-2 relative min-h-[80px]';

                    // Previous month days
                    if (i === 0 && j < firstDay) {
                        const prevDay = prevMonthDays - firstDay + j + 1;
                        dayCell.innerHTML = `<span class="text-sm text-gray-400">${prevDay}</span>`;
                        dayCell.classList.add('other-month');
                    }
                    // Current month days
                    else if (dayCount <= daysInMonth) {
                        const currentDate = new Date(currentYear, currentMonth, dayCount);
                        const isToday = isSameDay(currentDate, new Date());
                        const isSelected = isSameDay(currentDate, selectedDate);

                        // Add day number
                        dayCell.innerHTML = `<span class="text-sm ${isToday ? 'font-bold text-red-600' : ''}">${dayCount}</span>`;

                        // Check if this day has events
                        const dayEvents = events.filter(event => {
                            const eventDate = new Date(event.date);
                            return isSameDay(eventDate, currentDate);
                        });

                        // If has events, add event indicators
                        if (dayEvents.length > 0) {
                            const eventIndicator = document.createElement('div');
                            eventIndicator.className = 'event-indicators flex flex-wrap gap-1 mt-1';

                            // Show up to 3 event dots
                            const maxDots = Math.min(dayEvents.length, 3);
                            for (let k = 0; k < maxDots; k++) {
                                const dot = document.createElement('div');
                                dot.className = 'event-dot bg-red-500';
                                eventIndicator.appendChild(dot);
                            }

                            // Add event count if more than 3
                            if (dayEvents.length > 3) {
                                const count = document.createElement('span');
                                count.className = 'text-xs text-red-600 ml-1';
                                count.textContent = `+${dayEvents.length - 3}`;
                                eventIndicator.appendChild(count);
                            }

                            dayCell.appendChild(eventIndicator);
                            dayCell.classList.add('has-events');

                            // Event count badge
                            const countBadge = document.createElement('div');
                            countBadge.className = 'absolute top-1 right-1 bg-red-100 text-red-800 text-xs font-medium px-1.5 rounded-full';
                            countBadge.textContent = dayEvents.length;
                            dayCell.appendChild(countBadge);

                            // Add click event to view events for this day
                            dayCell.addEventListener('click', function(e) {
                                e.stopPropagation(); // Prevent event bubbling
                                const clickedDate = new Date(currentYear, currentMonth, dayCount);
                                selectCalendarDay(this, clickedDate);
                            });
                        }


                        // Mark selected day
                        if (isSelected) {
                            dayCell.classList.add('active', 'ring-2', 'ring-red-500');
                        }

                        // Mark today
                        if (isToday) {
                            dayCell.classList.add('today');
                        }

                        dayCount++;
                    }
                    // Next month days
                    else {
                        dayCell.innerHTML = `<span class="text-sm text-gray-400">${nextMonthDay}</span>`;
                        dayCell.classList.add('other-month');
                        nextMonthDay++;
                    }

                    calendarGrid.appendChild(dayCell);
                }
            }

            // Update events list for selected date
            showEvents(selectedDate);
        }

        function selectCalendarDay(day, date) {
            // Remove selection from all days
            document.querySelectorAll('.calendar-day').forEach(cell => {
                cell.classList.remove('active', 'ring-2', 'ring-red-500');
            });

            // Add selection to clicked day
            day.classList.add('active', 'ring-2', 'ring-red-500');

            // Update selected date
            selectedDate = date;

            // Show events for the selected date
            showEvents(selectedDate);
        }

        // Check if two dates are the same day
        function isSameDay(date1, date2) {
            return date1.getFullYear() === date2.getFullYear() &&
                date1.getMonth() === date2.getMonth() &&
                date1.getDate() === date2.getDate();
        }

        // Show events for a specific date
        function showEvents(date) {
            const eventsListEl = document.getElementById('eventsList');
            const eventItemsEl = document.getElementById('eventItems');
            const noEventsEl = document.getElementById('noEvents');
            const eventCountEl = document.getElementById('eventCount');
            const selectedDateEl = document.getElementById('selectedDate');

            // Format the date for display
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            selectedDateEl.textContent = date.toLocaleDateString('en-US', options);

            // Filter events for this date - make sure to create Date objects properly
            const dayEvents = events.filter(event => {
                // Create a new Date from the event's date string
                const eventDate = new Date(event.date + 'T00:00:00'); // Add time component to ensure proper parsing
                return isSameDay(eventDate, date);
            });

            console.log("Selected date:", date);
            console.log("Available events:", events);
            console.log("Filtered events for this day:", dayEvents);

            // Rest of your function...
        }

        // Format date time for display
        function formatDateTime(datetime) {
            const options = {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return new Date(datetime).toLocaleString('en-US', options);
        }

        // Escape string for use in HTML
        function escapeStr(str) {
            return str
                .replace(/\\/g, '\\\\')
                .replace(/'/g, "\\'")
                .replace(/"/g, '\\"')
                .replace(/\n/g, '\\n');
        }

        // Navigate to previous month
        function previousMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            updateCalendar();
        }

        // Navigate to next month
        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            updateCalendar();
        }

        // Toggle sidebar on mobile
        function setupSidebar() {
            document.getElementById('toggleSidebar').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');

                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });

            // Close sidebar when clicking overlay
            document.getElementById('sidebarOverlay').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');

                sidebar.classList.add('-translate-x-full');
                this.classList.add('hidden');
            });
        }

        // Setup search functionality
        function setupSearch() {
            document.getElementById('search').addEventListener('input', function() {
                const searchText = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('tbody tr');

                tableRows.forEach(row => {
                    const title = row.cells[0].textContent.toLowerCase();
                    const description = row.cells[1].textContent.toLowerCase();
                    const date = row.cells[2].textContent.toLowerCase();

                    if (title.includes(searchText) || description.includes(searchText) || date.includes(searchText)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Toggle add event modal
        function addProgram() {
            const modal = document.getElementById('addModal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');

            // If opening the modal, set the date to the selected date
            if (!modal.classList.contains('hidden')) {
                const dateInput = document.getElementById('add_date');
                const formattedDate = selectedDate.toISOString().split('T')[0];
                dateInput.value = formattedDate;
            }
        }

        // Handle edit event modal
        function editProgram(id, title, description, date) {
            document.getElementById('event_id').value = id;
            document.getElementById('event_title').value = title;
            document.getElementById('event_description').value = description;
            document.getElementById('event_date').value = date;

            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Close edit event modal
        function cancelContainer() {
            const modal = document.getElementById('editModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Delete event with confirmation
        function deleteEvent(id) {
            Swal.fire({
                title: 'Delete Event',
                text: 'Are you sure you want to delete this event?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6B0D0D',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?delete_id=" + id;
                }
            });
        }

        // Print calendar function
        function printCalendar() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');

            // Get the current date and time
            const now = new Date();
            const dateString = now.toLocaleDateString();
            const timeString = now.toLocaleTimeString();

            // Get month name and year
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            const monthYear = `${monthNames[currentMonth]} ${currentYear}`;

            // Get events for the current month
            const monthEvents = events.filter(event => {
                const eventDate = new Date(event.date);
                return eventDate.getMonth() === currentMonth && eventDate.getFullYear() === currentYear;
            });

            // Sort events by date
            monthEvents.sort((a, b) => new Date(a.date) - new Date(b.date));

            // Create print content with Tailwind-inspired styling
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Events Calendar - ${monthYear}</title>
                    <style>
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            padding: 20px;
                            color: #333;
                        }
                        .print-header {
                            display: flex;
                            align-items: center;
                            margin-bottom: 20px;
                        }
                        .print-header img {
                            height: 60px;
                            margin-right: 20px;
                        }
                        .print-title {
                            flex: 1;
                        }
                        h1 {
                            color: #6B0D0D;
                            margin: 0;
                            font-size: 24px;
                        }
                        h2 {
                            color: #666;
                            margin: 5px 0 0;
                            font-size: 16px;
                            font-weight: normal;
                        }
                        .print-info {
                            margin-bottom: 20px;
                            text-align: right;
                            color: #666;
                            font-size: 14px;
                        }
                        .month-title {
                            text-align: center;
                            margin: 20px 0;
                            font-size: 22px;
                            color: #6B0D0D;
                        }
                        .calendar {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 30px;
                        }
                        .calendar th {
                            background-color: #f8fafc;
                            color: #6B0D0D;
                            padding: 10px;
                            text-align: center;
                            border: 1px solid #e2e8f0;
                            font-weight: 600;
                        }
                        .calendar td {
                            border: 1px solid #e2e8f0;
                            padding: 10px;
                            height: 100px;
                            vertical-align: top;
                        }
                        .day-number {
                            font-weight: bold;
                            margin-bottom: 5px;
                        }
                        .other-month {
                            color: #9ca3af;
                            background-color: #f9fafb;
                        }
                        .today {
                            background-color: #fef2f2;
                        }
                        .event {
                            margin: 2px 0;
                            padding: 2px 5px;
                            background-color: #fecaca;
                            border-left: 3px solid #ef4444;
                            border-radius: 3px;
                            font-size: 11px;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                        .events-list {
                            margin-top: 30px;
                        }
                        .events-list h3 {
                            color: #6B0D0D;
                            margin-bottom: 10px;
                            border-bottom: 1px solid #e2e8f0;
                            padding-bottom: 5px;
                        }
                        table.events-table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        .events-table th {
                            background-color: #f8fafc;
                            color: #6B0D0D;
                            padding: 8px;
                            text-align: left;
                            border: 1px solid #e2e8f0;
                        }
                        .events-table td {
                            border: 1px solid #e2e8f0;
                            padding: 8px;
                            text-align: left;
                        }
                        .events-table tr:nth-child(even) {
                            background-color: #f9fafb;
                        }
                        .footer {
                            margin-top: 30px;
                            text-align: center;
                            font-size: 14px;
                            color: #6c757d;
                            border-top: 1px solid #e2e8f0;
                            padding-top: 10px;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <img src="../assets/img/CSSPE.png" alt="CSSPE Logo">
                        <div class="print-title">
                            <h1>CSSPE Inventory & Information System</h1>
                            <h2>Events Calendar</h2>
                        </div>
                    </div>
                    <div class="print-info">
                        <p>Printed on ${dateString} at ${timeString}</p>
                    </div>
                    <div class="month-title">
                        ${monthYear}
                    </div>
            `);

            // Add calendar grid
            printWindow.document.write(`
                <table class="calendar">
                    <thead>
                        <tr>
                            <th>Sunday</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                        </tr>
                    </thead>
                    <tbody>
            `);

            // Get first day of month and total days
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

            // Get last day of previous month
            const prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

            // Fill calendar grid
            let dayCount = 1;
            let nextMonthDay = 1;

            // In your updateCalendar function, when creating day cells:
            for (let i = 0; i < 6; i++) {
                for (let j = 0; j < 7; j++) {
                    const dayCell = document.createElement('div');
                    dayCell.className = 'calendar-day border border-gray-200 p-2 relative min-h-[80px]';

                    // Previous month days
                    if (i === 0 && j < firstDay) {
                        const prevDay = prevMonthDays - firstDay + j + 1;
                        dayCell.innerHTML = `<span class="text-sm text-gray-400">${prevDay}</span>`;
                        dayCell.classList.add('other-month');
                    }
                    // Current month days
                    else if (dayCount <= daysInMonth) {
                        const currentDate = new Date(currentYear, currentMonth, dayCount);
                        const isToday = isSameDay(currentDate, new Date());
                        const isSelected = isSameDay(currentDate, selectedDate);

                        // Add day number and make the entire cell clickable
                        dayCell.innerHTML = `<span class="text-sm ${isToday ? 'font-bold text-red-600' : ''}">${dayCount}</span>`;
                        dayCell.dataset.day = dayCount;
                        dayCell.dataset.month = currentMonth;
                        dayCell.dataset.year = currentYear;

                        // Check if this day has events
                        const dayEvents = events.filter(event => {
                            const eventDate = new Date(event.date);
                            return isSameDay(eventDate, currentDate);
                        });

                        // If has events, add event indicators
                        if (dayEvents.length > 0) {
                            const eventIndicator = document.createElement('div');
                            eventIndicator.className = 'event-indicators flex flex-wrap gap-1 mt-1';

                            // Show up to 3 event dots
                            const maxDots = Math.min(dayEvents.length, 3);
                            for (let k = 0; k < maxDots; k++) {
                                const dot = document.createElement('div');
                                dot.className = 'event-dot bg-red-500';
                                eventIndicator.appendChild(dot);
                            }

                            // Add event count if more than 3
                            if (dayEvents.length > 3) {
                                const count = document.createElement('span');
                                count.className = 'text-xs text-red-600 ml-1';
                                count.textContent = `+${dayEvents.length - 3}`;
                                eventIndicator.appendChild(count);
                            }

                            dayCell.appendChild(eventIndicator);
                            dayCell.classList.add('has-events');

                            // Event count badge
                            const countBadge = document.createElement('div');
                            countBadge.className = 'absolute top-1 right-1 bg-red-100 text-red-800 text-xs font-medium px-1.5 rounded-full';
                            countBadge.textContent = dayEvents.length;
                            dayCell.appendChild(countBadge);
                        }

                        // Make ALL days in current month clickable, not just days with events
                        dayCell.classList.add('cursor-pointer', 'hover:bg-gray-100');

                        // Mark selected day
                        if (isSelected) {
                            dayCell.classList.add('active', 'ring-2', 'ring-red-500');
                        }

                        // Mark today
                        if (isToday) {
                            dayCell.classList.add('today');
                        }

                        // Add click event directly to the cell
                        dayCell.addEventListener('click', function() {
                            // Update selected date
                            selectedDate = new Date(currentYear, currentMonth, parseInt(this.dataset.day));

                            // Refresh calendar to update visual selection
                            updateCalendar();

                            // Show events for selected date
                            showEvents(selectedDate);
                        });

                        dayCount++;
                    }
                    // Next month days
                    else {
                        dayCell.innerHTML = `<span class="text-sm text-gray-400">${nextMonthDay}</span>`;
                        dayCell.classList.add('other-month');
                        nextMonthDay++;
                    }

                    calendarGrid.appendChild(dayCell);
                }
            }
            printWindow.document.write(`
                    </tbody>
                </table>
            `);

            // Add events list
            if (monthEvents.length > 0) {
                printWindow.document.write(`
                    <div class="events-list">
                        <h3>Events for ${monthYear}</h3>
                        <table class="events-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                `);

                monthEvents.forEach(event => {
                    const eventDate = new Date(event.date);
                    const formattedDate = eventDate.toLocaleDateString('en-US', {
                        weekday: 'short',
                        month: 'short',
                        day: 'numeric'
                    });

                    printWindow.document.write(`
                        <tr>
                            <td>${formattedDate}</td>
                            <td>${event.title}</td>
                            <td>${event.description}</td>
                        </tr>
                    `);
                });

                printWindow.document.write(`
                            </tbody>
                        </table>
                    </div>
                `);
            }

            // Add footer
            printWindow.document.write(`
                <div class="footer">
                    &copy; ${new Date().getFullYear()} CSSPE Inventory & Information System
                </div>
            `);

            // Close the HTML
            printWindow.document.write(`
                </body>
                </html>
            `);

            // Trigger print
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                // printWindow.close();
            }, 500);
        }

        // Display SweetAlert messages (from PHP)
        function displayMessages() {
            <?php if (isset($_SESSION['message'])): ?>
                Swal.fire({
                    icon: "<?php echo $_SESSION['message_type']; ?>",
                    title: "<?php echo $_SESSION['message']; ?>",
                    showConfirmButton: false,
                    timer: 3000
                });
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
        }
    </script>
</body>

</html>