<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

// Fetch user details
$query = "SELECT first_name, middle_name, last_name, email, address, contact_no, department, role, rank FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $inventoryAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $firstName = $row['first_name'];
    $middleName = $row['middle_name'];
    $lastName = $row['last_name'];
    $email = $row['email'];
    $address = $row['address'];
    $contactNo = $row['contact_no'];
    $department = $row['department'];
    $role = $row['role'];
    $rank = $row['rank'];
} else {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/organization.css">
    <link rel="stylesheet" href="/dionSe/assets/css/sidebar.css">
    <link rel="stylesheet" href="/dionSe/assets/css/profile.css">

    <!-- Include SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="body">
        <div class="sidebar">
            <div class="sidebarContent">
                <div class="arrowContainer" style="margin-left: 80rem;" id="toggleButton">
                    <div class="subArrowContainer">
                        <img class="hideIcon" src="/dionSe/assets/img/arrow.png" alt="">
                    </div>
                </div>
            </div>
            <div class="userContainer">
                <div class="subUserContainer">
                    <div class="userPictureContainer">
                        <div class="subUserPictureContainer">
                            <img class="subUserPictureContainer" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p><?php echo $fullName; ?></p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">
                        <a href="../index.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Back to Inventory Admin Panel</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/profile.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Profile</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/announcement.php">
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
                                    <p>Manage Inventory</p>
                                </div>
                            </div>
                        </a>

                        <a href="../homePage/notification.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Notificaitons</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="subUserContainer">
                    <a href="../../login.php">
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
                            <img class="logo" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="collegeNameContainer">
                            <p>CSSPE Inventory & Information System</p>
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">Profile</p>
                </div>

                <div class="profileContainer">
                    <div class="subProfileContainer">
                        <div class="infoContainer">
                            <div class="pictureContainer1">
                                <div class="pictureContainer">
                                    <img class="picture" src="/dionSe/assets/img/CSSPE.png" alt="">
                                </div>

                                <div style="margin-top: 1rem;">
                                    <button onclick="confirmEditProfile()" class="addButton">Edit Profile</button>
                                </div>
                            </div>

                            <div class="subLoginContainer">
                                <form id="editProfileForm">
                                    <div class="inputContainer"
                                        style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; height: 4rem;">
                                        <div style="flex: 10 0 30%; padding-left: 30px;">
                                            <label for="firstName"
                                                style="display: block; font-size: 1.2rem; margin-bottom: 0.5rem;">First
                                                Name:</label>
                                            <input class="inputEmail" id="firstName" name="first_name" type="text"
                                                value="<?= htmlspecialchars($firstName) ?>" style="width: 100%;"
                                                required>
                                        </div>
                                        <div style="flex: 10 0 20%;">
                                            <label for="middleName"
                                                style="display: block; font-size: 1.2rem; margin-bottom: 0.5rem;">Middle
                                                Name:</label>
                                            <input class="inputEmail" id="middleName" name="middle_name" type="text"
                                                value="<?= htmlspecialchars($middleName) ?>" style="width: 100%;">
                                        </div>
                                        <div style="flex: 10 0 35%; padding-right: 50px;">
                                            <label for="lastName"
                                                style="display: block; font-size: 1.2rem; margin-bottom: 0.5rem;">Last
                                                Name:</label>
                                            <input class="inputEmail" id="lastName" name="last_name" type="text"
                                                value="<?= htmlspecialchars($lastName) ?>" style="width: 100%;"
                                                required>
                                        </div>
                                    </div>


                                    <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                        <label for="email"
                                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                                        <input class="inputEmail" id="email" name="email" type="text"
                                            value="<?= htmlspecialchars($email) ?>" required>
                                    </div>

                                    <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                        <label for="contactNo"
                                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                                            No.:</label>
                                        <input class="inputEmail" id="contactNo" name="contact_no" type="text"
                                            value="<?= htmlspecialchars($contactNo) ?>" required>
                                    </div>

                                    <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                        <label for="address"
                                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                                        <input class="inputEmail" id="address" name="address" type="text"
                                            value="<?= htmlspecialchars($address) ?>" required>
                                    </div>

                                    <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                        <label for="rank"
                                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Position:</label>
                                        <input class="inputEmail" id="rank" name="rank" type="text"
                                            value="<?= htmlspecialchars($rank) ?>" readonly>
                                    </div>

                                    <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                        <label for="department"
                                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Department:</label>
                                        <input class="inputEmail" id="department" name="department" type="text"
                                            value="<?= htmlspecialchars($department) ?>" required>
                                    </div>

                                    <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                        <label for="role"
                                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Role:</label>
                                        <input class="inputEmail" id="role" name="role" type="text"
                                            value="<?= htmlspecialchars($role) ?>" readonly>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="borrowContainer">
                            <div class="titleContainer1">
                                <p>Borrow History</p>
                            </div>

                            <div class="searchContainer">
                                <input class="searchBar" type="text" placeholder="Search...">
                            </div>

                            <div class="tableContainer">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Item Name</th>
                                            <th>Brand</th>
                                            <th>Quantity</th>
                                            <th>Expected Return Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Hakdog</td>
                                            <td>Hakdog</td>
                                            <td>Hakdog</td>
                                            <td>Hakdog</td>
                                            <td>Hakdog</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="editContainer3 size1" style="display: none; background-color: none; width: 100%;">
        <div class="editContainer3 size1">
            <div class="subProfileContainer size6">
                <div class="infoContainer">
                    <div class="pictureContainer1" style="background-color: none;">
                        <div class="pictureContainer">
                            <img class="picture" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>

                        <div style="margin-top: 1rem; display: flex; justify-content: center; align-items: center;">
                            <button onclick="triggerImageUpload()" class="addButton" id="imageUpload"
                                style="width: 100%;">Change Profile</button>
                        </div>
                    </div>

                    <div class="subLoginContainer">
                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Full
                                Name:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                                No.:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Position:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Department:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Role:</label>
                            <input class="inputEmail" type="text">
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button class="addButton" style="width: 6rem;">Save</button>
                            <button onclick="profile()" class="addButton1" style="width: 6rem;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmEditProfile() {
            Swal.fire({
                title: 'Edit Profile',
                text: 'Are you sure you want to save these changes?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'No, cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('editProfileForm');
                    const formData = new FormData(form);

                    fetch('../endpoints/edit_profile.php', {
                        method: 'POST',
                        body: formData,
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                Swal.fire('Success', data.message, 'success').then(() => {
                                    location.reload(); // Reload page to reflect changes
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch((error) => {
                            Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
                        });
                }
            });
        }
    </script>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="/dionSe/assets/js/sidebar.js"></script>
    <script src="/dionSe/assets/js/uploadImage.js"></script>
    <script src="/dionSe/assets/js/profile.js"></script>
</body>

</html>