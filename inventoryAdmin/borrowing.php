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

// Fetch items 
$originQuery = "SELECT id, name, brand FROM items";
$originResult = $conn->query($originQuery);

// Fetch users with role 'Instructor'
$teacherQuery = "SELECT id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name FROM users WHERE role = 'Instructor'";
$teacherResult = $conn->query($teacherQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/borrowing.css">
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

                        <a href="../homePage/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Home</p>
                                </div>
                            </div>
                        </a>

                        <!-- <a href="../inventoryAdmin/dashboard.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Dashboard</p>
                                </div>
                            </div>
                        </a> -->

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
                    <p class="text">Borrow request</p>
                </div>

                <div class="searchContainer">
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search..." oninput="filterTable()">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button onclick="printTable()" class="addButton size">Print</button>
                        <button onclick="addProgram()" class="addButton size">Borrow Item</button>
                        <!-- <select name="" class="addButton size" id="">
                            <option value="">Filter</option>
                        </select> -->
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Class Schedule</th>
                                <th>Borrow Date</th>
                                <th>Return Date</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows will be dynamically inserted here -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="addContainer" style="display: none; background-color: none;">
        <div class="addContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Borrow Item</p>
                </div>

                <div class="subLoginContainer">
                    <div class="inputContainer">
                        <!-- Dropdown for Origin Items -->
                        <select name="origin_item" id="origin_item" class="inputEmail"
                            onchange="fetchItemDetails(this.value)">
                            <option value="">Choose an Item</option>
                            <?php while ($origin = $originResult->fetch_assoc()): ?>
                                <option value="<?= $origin['id'] ?>">
                                    <?= htmlspecialchars($origin['name'] . ' (' . $origin['brand'] . ')') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="inputContainer">
                        <input id="item_brand" class="inputEmail" placeholder="Brand" type="text" readonly>

                    </div>

                    <div class="inputContainer">

                        <input id="item_quantity" class="inputEmail" placeholder="Available" type="text" readonly>
                    </div>
                    <div class="inputContainer">
                        <!-- Dropdown for Teachers -->
                        <select name="teacher" id="teacher" class="inputEmail">
                            <option value="">Choose a Teacher</option>
                            <?php while ($teacher = $teacherResult->fetch_assoc()): ?>
                                <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars(trim($teacher['full_name'])) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Quantity:</label>
                        <input class="inputEmail" type="number">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Borrow
                            Date:</label>
                        <input class="inputEmail" type="date" placeholder="Date:">
                    </div>
                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Return
                            Date:</label>
                        <input class="inputEmail" type="date" placeholder="Return Date">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class
                            Date:</label>
                        <input class="inputEmail" type="date" placeholder="Class Date">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class
                            schedule time from:</label>
                        <input class="inputEmail" type="time" placeholder="From:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Class
                            schedule time to:</label>
                        <input class="inputEmail" type="time" placeholder="To">
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                        <button class="addButton" style="width: 6rem;" onclick="confirmBorrow()">Add</button>

                        <button onclick="addProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="editContainer" style="display: none; background-color: none;">
        <div class="addContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Edit Borrowed Item</p>
                </div>

                <div class="subLoginContainer">
                    <!-- <div class="inputContainer">
                        Dropdown for Origin Items 
                    <select name="origin_item" id="edit_origin_item" class="inputEmail"
                        onchange="fetchItemDetails(this.value)">
                        <option value="">Choose an Item</option>
                    </select>
                </div> -->

                    <input id="edit_origin_item" class="inputEmail" placeholder="Item Name" type="hidden" readonly>

                    <div class="inputContainer">
                        <!-- Read-only input for Origin Items -->
                        <input id="edit_origin_item_name" class="inputEmail" placeholder="Item Name" type="text"
                            readonly>
                    </div>

                    <div class="inputContainer">
                        <input id="edit_item_brand" class="inputEmail" placeholder="Brand" type="text" readonly>
                    </div>

                    <div class="inputContainer">
                        <input id="edit_item_quantity" class="inputEmail" placeholder="Available Quantity" type="text"
                            readonly>
                    </div>

                    <div class="inputContainer">
                        <!-- Dropdown for Teachers -->
                        <select name="teacher" id="edit_teacher" class="inputEmail">
                            <option value="">Choose a Teacher</option>
                            <!-- Dynamically populated via JavaScript -->
                        </select>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                            Quantity:
                        </label>
                        <input id="edit_quantity" class="inputEmail" type="number" placeholder="Quantity">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                            Borrow Date:
                        </label>
                        <input id="edit_borrow_date" class="inputEmail" type="date" placeholder="Borrow Date">
                    </div>
                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                            Return Date:
                        </label>
                        <input id="edit_return_date" class="inputEmail" type="date" placeholder="Return Date">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                            Class Date:
                        </label>
                        <input id="edit_class_date" class="inputEmail" type="date" placeholder="Class Date">
                    </div>


                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                            Class schedule time from:
                        </label>
                        <input id="edit_schedule_from" class="inputEmail" type="time" placeholder="From">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                            Class schedule time to:
                        </label>
                        <input id="edit_schedule_to" class="inputEmail" type="time" placeholder="To">
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                        <button class="addButton" style="width: 6rem;" onclick="saveEditTransaction()">Save</button>

                        <button onclick="cancelEdit()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function approveTransaction(transactionId) {
            Swal.fire({
                title: 'Approve Transaction',
                text: 'Are you sure you want to approve this transaction?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', './endpoints/update_transaction_status.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.message,
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                        fetchTransactions(); // Refresh the transaction list
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: response.message,
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'An unexpected error occurred.',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed to communicate with the server.',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        }
                    };
                    xhr.send(`transaction_id=${transactionId}&status=Approved`);
                }
            });
        }


        function declineTransaction(transactionId, itemId, quantityBorrowed) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will return the borrowed quantity to the item.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, decline it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', './endpoints/decline_transaction.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.message,
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                        fetchTransactions(); // Refresh the transaction list
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: response.message,
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'An unexpected error occurred.',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'An error occurred while declining the transaction.',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        }
                    };

                    xhr.send(`transaction_id=${transactionId}&item_id=${itemId}&quantity_borrowed=${quantityBorrowed}`);
                }
            });
        }


        function fetchTransactions() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', './endpoints/get_item_transactions.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const transactions = response.data;
                            const tbody = document.querySelector('.tableContainer tbody');
                            tbody.innerHTML = '';

                            function formatTime24To12(timeString) {
                                if (!timeString) return 'N/A';
                                const [hour, minute] = timeString.split(':');
                                const hourInt = parseInt(hour, 10);
                                const isPM = hourInt >= 12;
                                const formattedHour = hourInt % 12 || 12;
                                const suffix = isPM ? 'PM' : 'AM';
                                return `${formattedHour}:${minute} ${suffix}`;
                            }

                            function formatDateTimeWithNewline(datetimeString) {
                                if (!datetimeString) return 'N/A';
                                const [date, time] = datetimeString.split(' ');
                                const formattedTime = formatTime24To12(time);
                                return `${date}<br>${formattedTime}`;
                            }

                            transactions.forEach(transaction => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                            <td>${transaction.transaction_id}</td>
                            <td>${transaction.item_name}</td>
                            <td>${transaction.item_brand}</td>
                            <td>${transaction.quantity_borrowed}</td>
                            <td>${transaction.class_date} - ${formatTime24To12(transaction.schedule_from)} - ${formatTime24To12(transaction.schedule_to)}</td>
                            <td>${transaction.borrowed_at ? formatDateTimeWithNewline(transaction.borrowed_at) : 'N/A'}</td>
                            <td>${transaction.return_date}</td>
                            <td>${transaction.first_name} ${transaction.last_name}</td>
                            <td>${transaction.contact_no}</td>
                            <td>${transaction.email}</td>
                            <td class="button">
                                <button class="addButton" style="width: 7rem;" onclick="approveTransaction(${transaction.transaction_id})">Approve</button>
                                <button class="addButton1" style="width: 7rem;" 
                                    onclick="declineTransaction(${transaction.transaction_id}, '${transaction.item_id}', ${transaction.quantity_borrowed})">Decline</button>
                                <button class="addButton" style="width: 7rem;" onclick="editTransaction(${transaction.transaction_id})">Edit</button>
                            </td>
                        `;
                                tbody.appendChild(row);
                            });
                        } else {
                            alert(response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            xhr.send();
        }


        // Fetch transactions on page load
        document.addEventListener('DOMContentLoaded', fetchTransactions);



        function fetchItemDetails(itemId) {
            if (!itemId) {
                document.getElementById('item_brand').value = '';
                document.getElementById('item_quantity').value = '';
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./endpoints/fetch_item_details.php?item_id=${itemId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            document.getElementById('item_brand').value = response.data.brand;
                            document.getElementById('item_quantity').value = response.data.quantity;
                        } else {
                            alert(response.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            xhr.send();
        }

        function confirmBorrow() {
            const itemId = document.getElementById('origin_item').value;
            const teacherId = document.getElementById('teacher').value;
            const quantity = parseInt(document.querySelector('input[type="number"]').value, 10);
            const borrowDate = document.querySelector('input[placeholder="Date:"]').value;
            const returnDate = document.querySelector('input[placeholder="Return Date"]').value;
            const classDate = document.querySelector('input[placeholder="Class Date"]').value;
            const scheduleFrom = document.querySelector('input[placeholder="From:"]').value;
            const scheduleTo = document.querySelector('input[placeholder="To"]').value;
            const availableQuantity = parseInt(document.getElementById('item_quantity').value, 10);

            if (!itemId || !teacherId || isNaN(quantity) || !borrowDate || !returnDate || !classDate || !scheduleFrom || !scheduleTo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Fields',
                    text: 'Please fill out all fields.',
                    showConfirmButton: true
                });
                return;
            }

            if (quantity > availableQuantity || quantity <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Quantity',
                    text: `Invalid quantity. Available: ${availableQuantity}`,
                    showConfirmButton: true
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Borrow',
                text: "Are you sure you want to proceed with this transaction?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, confirm it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', './endpoints/borrow_item.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    Swal.fire({
                                        icon: response.status === 'success' ? 'success' : 'error',
                                        title: response.message,
                                        showConfirmButton: false,
                                        timer: 3000
                                    });

                                    if (response.status === 'success') {
                                        // Reset form
                                        document.getElementById('origin_item').value = '';
                                        document.getElementById('item_brand').value = '';
                                        document.getElementById('item_quantity').value = '';
                                        document.getElementById('teacher').value = '';
                                        document.querySelector('input[type="number"]').value = '';
                                        document.querySelector('input[placeholder="Date:"]').value = '';
                                        document.querySelector('input[placeholder="Return Date"]').value = '';
                                        document.querySelector('input[placeholder="Class Date"]').value = '';
                                        document.querySelector('input[placeholder="From:"]').value = '';
                                        document.querySelector('input[placeholder="To"]').value = '';


                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 2000);
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'An unexpected error occurred.',
                                        showConfirmButton: true
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to communicate with the server.',
                                    showConfirmButton: true
                                });
                            }
                        }
                    };

                    const params = `item_id=${itemId}&teacher=${teacherId}&quantity=${quantity}&borrow_date=${borrowDate}&return_date=${returnDate}&class_date=${classDate}&schedule_from=${scheduleFrom}&schedule_to=${scheduleTo}`;
                    xhr.send(params);
                }
            });
        }

        function editTransaction(transactionId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./endpoints/get_transaction_details.php?id=${transactionId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        const transaction = response.data;

                        const editContainer = document.querySelector('.editContainer');
                        editContainer.style.display = 'block';
                        editContainer.dataset.transactionId = transactionId;

                        // Populate fields
                        document.getElementById('edit_origin_item').value = transaction.item_id;
                        document.getElementById('edit_origin_item_name').value = transaction.item_name;
                        document.getElementById('edit_item_brand').value = transaction.brand;
                        document.getElementById('edit_item_quantity').value = transaction.available_quantity;
                        document.getElementById('edit_teacher').value = transaction.teacher_id;
                        document.getElementById('edit_quantity').value = transaction.quantity_borrowed;
                        document.getElementById('edit_borrow_date').value = transaction.borrow_date;
                        document.getElementById('edit_return_date').value = transaction.return_date;
                        document.getElementById('edit_class_date').value = transaction.class_date;
                        document.getElementById('edit_schedule_from').value = transaction.schedule_from;
                        document.getElementById('edit_schedule_to').value = transaction.schedule_to;

                        fetchItems(transaction.item_id);
                        fetchTeachers(transaction.users_id);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            showConfirmButton: true,
                        });
                    }
                }
            };
            xhr.send();
        }


        function saveEditTransaction() {
            const transactionId = document.querySelector('.editContainer').dataset.transactionId;
            const itemId = document.getElementById('edit_origin_item').value;
            const teacherId = document.getElementById('edit_teacher').value;
            const quantity = document.getElementById('edit_quantity').value;
            const borrowDate = document.getElementById('edit_borrow_date').value;
            const returnDate = document.getElementById('edit_return_date').value;
            const classDate = document.getElementById('edit_class_date').value;
            const scheduleFrom = document.getElementById('edit_schedule_from').value;
            const scheduleTo = document.getElementById('edit_schedule_to').value;

            if (!transactionId || !itemId || !teacherId || !quantity || !borrowDate || !returnDate || !classDate || !scheduleFrom || !scheduleTo) {
                Swal.fire({
                    icon: 'error',
                    title: 'All fields are required.',
                    showConfirmButton: true,
                });
                return;
            }

            const data = new FormData();
            data.append('transaction_id', transactionId);
            data.append('item_id', itemId);
            data.append('teacher', teacherId);
            data.append('quantity', quantity);
            data.append('borrow_date', borrowDate);
            data.append('return_date', returnDate);
            data.append('class_date', classDate);
            data.append('schedule_from', scheduleFrom);
            data.append('schedule_to', scheduleTo);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', './endpoints/edit_borrowed_item.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: true,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            showConfirmButton: true,
                        });
                    }
                }
            };
            xhr.send(data);
        }



        // Cancel Edit
        function cancelEdit() {
            document.querySelector('.editContainer').style.display = 'none';
        }
        function fetchItems(selectedItemId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', './endpoints/get_items.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        const itemDropdown = document.getElementById('edit_origin_item');
                        itemDropdown.innerHTML = '<option value="">Choose an Item</option>';

                        response.data.forEach((item) => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = `${item.name} (${item.brand})`;
                            if (item.id == selectedItemId) {
                                option.selected = true;
                                document.getElementById('edit_item_quantity').value = item.quantity; // Update available quantity
                            }
                            itemDropdown.appendChild(option);
                        });
                    }
                }
            };
            xhr.send();
        }

        function fetchTeachers(selectedTeacherId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', './endpoints/get_teachers.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        const teacherDropdown = document.getElementById('edit_teacher');
                        teacherDropdown.innerHTML = '<option value="">Choose a Teacher</option>'; // Default option

                        response.data.forEach((teacher) => {
                            const option = document.createElement('option');
                            option.value = teacher.id;
                            option.textContent = `${teacher.first_name} ${teacher.last_name}`; // Full name

                            // Preselect the current teacher
                            if (teacher.id == selectedTeacherId) {
                                option.selected = true; // Mark the current teacher as selected
                            }

                            teacherDropdown.appendChild(option);
                        });
                    } else {
                        console.error('Failed to fetch teachers:', response.message);
                    }
                } else if (xhr.readyState === 4) {
                    console.error('Error fetching teachers:', xhr.statusText);
                }
            };
            xhr.send();
        }


        // Call these functions on page load or edit form open
        fetchItems();
        fetchTeachers();

        // Function to filter the table based on the search input
        function filterTable() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const tableRows = document.querySelectorAll('.tableContainer tbody tr');
            tableRows.forEach(row => {
                const itemName = row.children[1].textContent.toLowerCase(); // Column 2: Item Name
                if (itemName.includes(searchValue)) {
                    row.style.display = ''; // Show row
                } else {
                    row.style.display = 'none'; // Hide row
                }
            });
        }

        function printTable() {
            const tableContainer = document.querySelector('.tableContainer');
            const rows = tableContainer.querySelectorAll('tr');

            // Temporarily hide the "Action" column (last column) and any unwanted text
            const printHeader = document.querySelector('.searchContainer'); // Adjust this to the correct element if needed
            printHeader.style.display = 'none'; // Hide the "Print Table" text

            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = 'none'; // Hide last cell
                }
            });

            // Get HTML for printing
            const printContent = tableContainer.outerHTML;
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Table</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid black;
                padding: 8px;
                text-align: left;
            }
            th, td img {
                width: 90px;
            }                   
            th {
                background-color: #f4f4f4;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
        </style>
    </head>
    <body>
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();

            // Restore visibility after print
            printHeader.style.display = ''; // Restore the visibility of the "Print Table" text
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = ''; // Restore visibility
                }
            });
        }


    </script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
</body>

</html>