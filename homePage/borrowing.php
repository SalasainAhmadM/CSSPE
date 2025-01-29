<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);
$userid = $_SESSION['user_id'];

$query_notifications = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";
$result_notifications = mysqli_query($conn, $query_notifications);
$notificationCount = 0;

if ($result_notifications && $row_notifications = mysqli_fetch_assoc($result_notifications)) {
    $notificationCount = $row_notifications['notification_count'];
}

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

$limit = 6;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$itemsQuery = "SELECT * FROM items LIMIT $limit OFFSET $offset";
$itemsResult = $conn->query($itemsQuery);

$totalitemsQuery = "SELECT COUNT(*) AS total FROM items";
$totalitemsResult = $conn->query($totalitemsQuery);
$totalRow = mysqli_fetch_assoc($totalitemsResult);
$totalItems = $totalRow['total'];

$totalPages = ceil($totalItems / $limit);

// Fetch users with role 'Instructor'
$teacherQuery = "SELECT id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name FROM users WHERE role = 'Instructor'";
$teacherResult = $conn->query($teacherQuery);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing</title>

    <link rel="stylesheet" href="../assets/css/borrowingHome.css">
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
            .inventoryGrid {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                padding: 50px;
                justify-content: space-between;
                text-align: center;
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
                    <p class="text">Inventory</p>
                </div>

                <div class="searchContainer">
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search...">
                </div>

                <div id="inventoryGrid" class="inventoryGrid">
                    <?php if ($itemsResult->num_rows > 0): ?>
                        <?php while ($item = $itemsResult->fetch_assoc()): ?>
                            <div class="inventoryContainer" data-title="<?= htmlspecialchars($item['name']) ?>">
                                <div class="subInventoryContainer">
                                    <div class="imageContainer" style="border-bottom: solid gray 1px;"
                                        onclick="showNote('<?= htmlspecialchars($item['note'] ?: 'No note available') ?>')">
                                        <img style="height: 50px;" class="image"
                                            src="../assets/uploads/<?= htmlspecialchars($item['image'] ?: '../../assets/img/CSSPE.png') ?>"
                                            alt="Item Image">
                                    </div>

                                    <div class="infoContainer">
                                        <p><?= htmlspecialchars($item['name']) ?></p>
                                    </div>
                                    <div class="infoContainer1">
                                        <p><?= htmlspecialchars($item['brand']) ?></p>
                                    </div>
                                    <div class="infoContainer1">
                                        <p><?= htmlspecialchars($item['description']) ?></p>
                                    </div>
                                    <div class="infoContainer1">
                                        <p>Available: <?= htmlspecialchars($item['quantity']) ?></p>
                                    </div>
                                    <div class="buttonContainer">
                                        <button
                                            onclick="borrowItem(<?= htmlspecialchars($item['id']) ?>, '<?= htmlspecialchars($item['name']) ?>', '<?= htmlspecialchars($item['brand']) ?>')"
                                            class="addButton">Borrow</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No items available</p>
                    <?php endif; ?>
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

    <div class="editContainer"
        style="display: none; background-color: rgba(0, 0, 0, 0.5); position: fixed; top: 0; left: 0; width: 100%; height: 100%; justify-content: center; align-items: center;">
        <div class="subAddContainer"
            style="background-color: white; padding: 20px; border-radius: 10px;transform: scale(0.80);">
            <div class="titleContainer">
                <p>Borrowed Item</p>
            </div>

            <div class="subLoginContainer">
                <!-- hidden id -->
                <div class="inputContainer" style="display: none;">
                    <input id="itemId" type="hidden">
                </div>

                <!-- Item Name -->
                <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label>Item Name:</label>
                    <input id="itemName" class="inputEmail" type="text" readonly>
                </div>

                <!-- Brand  -->
                <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label>Brand:</label>
                    <input id="itemBrand" class="inputEmail" type="text" readonly>
                </div>

                <input id="teacherSelect" class="inputEmail" value="<?php echo htmlspecialchars($userid); ?>"
                    type="hidden">

                <!-- Teacher Selection -->
                <!--  <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                     <label>Choose Teacher:</label>
                     <select id="teacherSelect" class="inputEmail">
                        <option value="">Choose a teacher</option>
                        <?php while ($row = $teacherResult->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['full_name']) ?></option>
                        <?php endwhile; ?>
                    </select> 
                </div>-->

                <!-- Quantity -->
                <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label>Quantity:</label>
                    <input id="quantity" class="inputEmail" type="number" placeholder="Quantity:">
                </div>

                <!-- Borrow Date -->
                <!-- <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label>Borrow Date:</label>
                    <input id="borrowDate" class="inputEmail" type="date">
                </div> -->

                <!-- Return Date -->
                <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label>Return Date:</label>
                    <input id="returnDate" class="inputEmail" type="date">
                </div>

                <!-- Class Date -->
                <!-- <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label>Class Date:</label>
                    <input id="classDate" class="inputEmail" type="date">
                </div> -->

                <!-- Schedule Time -->
                <!-- <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label>Class schedule time from:</label>
                    <input id="timeFrom" class="inputEmail" type="time">
                </div>

                <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                    <label>Class schedule time to:</label>
                    <input id="timeTo" class="inputEmail" type="time">
                </div> -->

                <!-- Buttons -->
                <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                    <button class="confirmButton" style="width: 6rem;">Borrow</button>
                    <button class="addButton1" style="width: 6rem;">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setMinDate() {
            const manilaTime = new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" });
            const today = new Date(manilaTime);

            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, "0");
            const dd = String(today.getDate()).padStart(2, "0");
            const minDate = `${yyyy}-${mm}-${dd}`;

            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.min = minDate;
            });
        }

        setMinDate();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function showNote(note) {
            Swal.fire({
                title: 'Warning Note',
                text: note,
                icon: 'info',
                confirmButtonText: 'Close',
            });
        }


        function borrowItem(itemId, itemName, itemBrand) {
            // Perform an AJAX request to check ban status
            fetch('checkBanStatus.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    userId: <?= json_encode($_SESSION['user_id']) ?>
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.banned) {
                        // Show SweetAlert if user is banned
                        Swal.fire({
                            icon: 'error',
                            title: 'Access Denied',
                            text: 'You are currently banned from borrowing items.',
                            confirmButtonText: 'Okay'
                        });
                    } else {
                        // Proceed with the borrowing process
                        // Open the modal or handle the borrowing logic
                        document.getElementById('itemId').value = itemId;
                        document.getElementById('itemName').value = itemName;
                        document.getElementById('itemBrand').value = itemBrand;
                        document.querySelector('.editContainer').style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while checking your ban status.',
                        confirmButtonText: 'Okay'
                    });
                });
        }


        function closeModal() {
            const modal = document.querySelector('.editContainer');
            modal.style.display = 'none';
        }

        document.querySelector('.addButton1').addEventListener('click', closeModal);

        document.querySelector('.confirmButton').addEventListener('click', function () {
            // Get form values
            const itemId = document.getElementById('itemId').value;
            const teacherId = document.getElementById('teacherSelect').value;
            const quantity = document.getElementById('quantity').value;
            const returnDate = document.getElementById('returnDate').value;

            if (!teacherId || !quantity || !returnDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields.'
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Borrow',
                text: 'Are you sure you want to borrow this item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Borrow'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form using fetch
                    const formData = new FormData();
                    formData.append('item_id', itemId);
                    formData.append('teacher', teacherId);
                    formData.append('quantity', quantity);
                    formData.append('return_date', returnDate);

                    fetch('borrow_item.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                closeModal();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'An error occurred while processing your request.',
                                'error'
                            );
                        });
                }
            });
        });

        const searchBar = document.getElementById('searchBar');
        const inventoryGrid = document.getElementById('inventoryGrid');

        searchBar.addEventListener('input', function () {
            const searchTerm = searchBar.value.toLowerCase();
            const inventoryContainers = inventoryGrid.getElementsByClassName('inventoryContainer');

            for (const container of inventoryContainers) {
                const title = container.getAttribute('data-title').toLowerCase();
                container.style.display = title.includes(searchTerm) ? '' : 'none';
            }
        });
    </script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
</body>

</html>