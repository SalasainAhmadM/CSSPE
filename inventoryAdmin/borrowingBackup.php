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

// Fetch items with type 'origin'
$originQuery = "SELECT id, name FROM items WHERE type = 'origin'";
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
                    <input class="searchBar" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size">Print</button>
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
                                <th>Return Date</th>
                                <th>Fullname</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td>Hakdog</td>
                                <td class="button">
                                    <button class="addButton" style="width: 7rem;">Approve</button>
                                    <button class="addButton1" style="width: 7rem;">Declined</button>
                                    <button onclick="editProgram()" class="addButton" style="width: 7rem;">Edit</button>
                                </td>
                            </tr>
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
                            onchange="fetchBrands(this.value)">
                            <option value="">Choose an Item</option>
                            <?php while ($origin = $originResult->fetch_assoc()): ?>
                                <option value="<?= $origin['id'] ?>"><?= htmlspecialchars($origin['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="inputContainer">
                        <!-- Dropdown for Brand Items -->
                        <select name="brand_item" id="brand_item" class="inputEmail" disabled>
                            <option value="">Choose a Brand</option>
                            <!-- Options will be dynamically populated via JavaScript -->
                        </select>
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
                        <input class="inputEmail" type="Number">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Borrow
                            Date:</label>
                        <input class="inputEmail" type="date" placeholder="Date:">
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
        <div class="editContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Edit Borrowed Item</p>
                </div>

                <div class="subLoginContainer">
                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Borrowed
                            Item:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">First
                            Name:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Last
                            Name:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Middle
                            Name:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                        <input class="inputEmail" type="email">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                        <input class="inputEmail" type="text">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                            No.:</label>
                        <input class="inputEmail" type="number">
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem;">
                        <select class="inputEmail" name="" id="">
                            <option value="">Choose a Departments</option>
                        </select>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem;">
                        <select class="inputEmail" name="" id="">
                            <option value="">Choose a rank</option>
                            <option value="Instructor">Instructor</option>
                            <option value="Assistant Professor">Assistant Professor</option>
                            <option value="Associate Professor">Associate Professor</option>
                            <option value="Professor">Professor</option>
                        </select>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                        <button class="addButton" style="width: 6rem;">Save</button>
                        <button onclick="editProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        function fetchBrands(originItemId) {
            const brandDropdown = document.getElementById('brand_item');

            // Reset the brand dropdown
            brandDropdown.innerHTML = '<option value="">Choose a Brand</option>';
            brandDropdown.disabled = true;

            if (!originItemId) {
                return;
            }

            // Send an AJAX request to fetch brand items and update dropdown
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./endpoints/fetch_brands.php?origin_id=${originItemId}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.length > 0) {
                        brandDropdown.innerHTML = response.map(
                            brand => `<option value="${brand.id}">${brand.name} (${brand.brand})</option>`
                        ).join('');
                        brandDropdown.disabled = false;
                    }
                }
            };
            xhr.send();
        }

        function confirmBorrow() {
            // Gather input values
            const brandItem = document.getElementById('brand_item').value;
            const teacher = document.getElementById('teacher').value;
            const quantity = document.querySelector('input[type="Number"]').value;
            const borrowDate = document.querySelector('input[type="date"]').value;
            const scheduleFrom = document.querySelector('input[placeholder="From:"]').value;
            const scheduleTo = document.querySelector('input[placeholder="To"]').value;

            // Validate inputs
            if (!brandItem || !teacher || !quantity || !borrowDate || !scheduleFrom || !scheduleTo) {
                alert('Please fill out all fields.');
                return;
            }

            // Send AJAX request to the backend
            const xhr = new XMLHttpRequest();
            xhr.open('POST', './endpoints/borrow_item.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    alert(response.message);
                    if (response.status === 'success') {
                        // Reset form after successful submission
                        document.getElementById('origin_item').value = '';
                        document.getElementById('brand_item').innerHTML = '<option value="">Choose a Brand</option>';
                        document.getElementById('brand_item').disabled = true;
                        document.getElementById('teacher').value = '';
                        document.querySelector('input[type="Number"]').value = '';
                        document.querySelector('input[type="date"]').value = '';
                        document.querySelector('input[placeholder="From:"]').value = '';
                        document.querySelector('input[placeholder="To"]').value = '';
                    }
                }
            };

            const params = `brand_item=${brandItem}&teacher=${teacher}&quantity=${quantity}&borrow_date=${borrowDate}&schedule_from=${scheduleFrom}&schedule_to=${scheduleTo}`;
            xhr.send(params);
        }

    </script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
</body>

</html>