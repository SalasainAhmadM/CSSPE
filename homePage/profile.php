<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);

// Fetch user data
$userid = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, middle_name, email, address, contact_no, rank, role, image, department 
        FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_SESSION['user_id'];

    // Update profile details
    $firstName = $_POST['first_name'] ?? $user['first_name'];
    $lastName = $_POST['last_name'] ?? $user['last_name'];
    $middleName = $_POST['middle_name'] ?? $user['middle_name'];
    $email = $_POST['email'] ?? $user['email'];
    $address = $_POST['address'] ?? $user['address'];
    $contactNo = $_POST['contact_no'] ?? $user['contact_no'];
    $rank = $_POST['rank'] ?? $user['rank'];
    $department = $_POST['department'] ?? $user['department'];

    $sql = "UPDATE users SET first_name = ?, last_name = ?, middle_name = ?, email = ?, address = ?, contact_no = ?, rank = ?, department = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $firstName, $lastName, $middleName, $email, $address, $contactNo, $rank, $department, $userid);
    $stmt->execute();
    $stmt->close();

    // Check if a file is uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = $_FILES['profile_image']['name'];
        $fileSize = $_FILES['profile_image']['size'];
        $fileType = $_FILES['profile_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Allowed extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            // Generate a unique name for the file
            $newFileName = $userid . '_profile.' . $fileExtension;
            $uploadFileDir = '../assets/img/';
            $destPath = $uploadFileDir . $newFileName;

            // Move the file to the server directory
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Update the user's image in the database
                $sql = "UPDATE users SET image = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $newFileName, $userid);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$query_notifications = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";
$result_notifications = mysqli_query($conn, $query_notifications);
$notificationCount = 0;

if ($result_notifications && $row_notifications = mysqli_fetch_assoc($result_notifications)) {
    $notificationCount = $row_notifications['notification_count'];
}

$conn->close();
?>



<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <link rel="stylesheet" href="../assets/css/organization.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/profile.css">


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
                            <img class="subUserPictureContainer" src="../assets/img/CSSPE.png" alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p><?php echo ($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">
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
                                    <p>
                                        Notifications
                                        <span style="background-color:#1a1a1a; padding:5px; border-radius:4px;">
                                            <?php echo $notificationCount; ?>
                                        </span>
                                    </p>
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
                    <p class="text">Profile</p>
                </div>

                <div class="profileContainer">
                    <div class="subProfileContainer">

                        <div class="infoContainer">
                            <div class="pictureContainer1" style="background-color: none;">

                                <div class="pictureContainer">
                                    <img src="<?= '../assets/img/' . htmlspecialchars($user['image']) ?>"
                                        alt="Profile Picture" style="width: 100%" border-radius: 50%; object-fit: cover;
                                        margin-left: 10%;">
                                </div>

                                <div style="margin-top: 1rem;">
                                    <button onclick="profile()" class="addButton">Edit Profile</button>
                                </div>
                            </div>

                            <div class="subLoginContainer">

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Full
                                        Name:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                                        No.:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['contact_no']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['address']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Position:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['rank']) ?>
                                    </h3>
                                </div>

                                <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                    <label for=""
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Department:</label>
                                    <h3
                                        style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                                        <?= htmlspecialchars($user['department']) ?>
                                    </h3>
                                </div>

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
                            <img src="<?= '../assets/img/' . htmlspecialchars($user['image']) ?>" alt="Profile Picture"
                                style="width: 100%; border-radius: 50%; object-fit: cover;">
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="file" name="profile_image" accept="image/*" required>
                            <button type="submit" class="addButton">Change Profile Image</button>
                        </form>


                    </div>

                    <div class="subLoginContainer">

                        <form action="" method="POST" enctype="multipart/form-data">

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">First
                                    Name:</label>
                                <input class="inputEmail" name="first_name"
                                    value="<?= htmlspecialchars($user['first_name']) ?>"="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Last
                                    Name:</label>
                                <input class="inputEmail" name="last_name"
                                    value="<?= htmlspecialchars($user['last_name']) ?>" type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Middle
                                    Name:</label>
                                <input class="inputEmail" name="middle_name"
                                    value="<?= htmlspecialchars($user['middle_name']) ?>" type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Email:</label>
                                <input class="inputEmail" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                                    type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Contact
                                    No.:</label>
                                <input class="inputEmail" name="contact_no"
                                    value="<?= htmlspecialchars($user['contact_no']) ?>" type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Address:</label>
                                <input class="inputEmail" name="address"
                                    value="<?= htmlspecialchars($user['address']) ?>" type="text">
                            </div>

                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Position:</label>
                                <input class="inputEmail" name="rank" value="<?= htmlspecialchars($user['rank']) ?>"
                                    type="text">
                            </div>


                            <div class="inputContainer" style="flex-direction: column; height: 4rem;">
                                <label for=""
                                    style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Department:</label>
                                <input class="inputEmail" name="department"
                                    value="<?= htmlspecialchars($user['department']) ?>" type="text">
                            </div>

                            <div class="inputContainer"
                                style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                                <button type="submit" class="addButton" style="width: 6rem;">Save</button>
                                <button onclick="profile()" class="addButton1" style="width: 6rem;">Cancel</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/uploadImage.js"></script>
    <script src="../assets/js/profile.js"></script>
</body>

</html>