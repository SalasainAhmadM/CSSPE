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

$query = "SELECT * FROM announcements";
$result = mysqli_query($conn, $query);

if (isset($_POST['add_announcement'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    $date_uploaded_at = date('Y-m-d H:i:s');

    $query_announcements = "INSERT INTO announcements (title, description, location, date_uploaded_at) 
                            VALUES ('$title', '$description', '$location', '$date_uploaded_at')";

    $query_notifications = "INSERT INTO notifications (title, description, uploaded_at, type) 
                            VALUES ('$title', '$description', '$date_uploaded_at', 'Announcements')";

    $success_announcements = mysqli_query($conn, $query_announcements);
    $success_notifications = mysqli_query($conn, $query_notifications);

    if ($success_announcements && $success_notifications) {
        $_SESSION['message'] = "Announcement and notification added successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
}

if (isset($_POST['update_announcement'])) {
    $announcement_id = $_POST['announcement_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    $current_time = date('H:i:s');
    $date_uploaded_at = $date . ' ' . $current_time;

    $query = "UPDATE announcements SET title = '$title', description = '$description', date_uploaded_at = '$date_uploaded_at',  location = '$location' WHERE id = $announcement_id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Event updated successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
}

if (isset($_GET['delete_id'])) {
    $announcement_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM announcements WHERE id = $announcement_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Announcement deleted successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error deleting record: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>

    <link rel="stylesheet" href="../assets/css/events.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

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

                        <a href="../informationAdmin/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Departments</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/facultyMember.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Faculty Members</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/organization.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Organizations</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/memorandum.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Memorandums</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/announcement.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Announcements</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/events.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Events</p>
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
                    <p class="text">Announcements</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" id="search" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size" onclick="printTable()">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Announcement</button>
                    </div>
                </div>

                <div class="tableContainer" style="height:475px">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date/Time</th>
                                <th>Location</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date_uploaded_at']); ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td class="button">
                                        <a href="#" onclick="editProgram(<?php echo $row['id']; ?>, 
                                        '<?php echo addslashes($row['title']); ?>', 
                                        '<?php echo addslashes($row['description']); ?>',
                                        '<?php echo addslashes(date('Y-m-d', strtotime($row['date_uploaded_at']))); ?>',
                                        '<?php echo addslashes($row['location']); ?>')">
                                            <button class="addButton1" style="width: 6rem;">Edit</button>
                                        </a>
                                        <a href="#" onclick="deleteAnnouncement(<?php echo $row['id']; ?>)">
                                            <button class="addButton1" style="width: 6rem;">Delete</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="addContainer" style="display: none; background-color: none;">
            <div class="addContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Add Announcement</p>
                    </div>

                    <div class="subLoginContainer">
                        <div class="inputContainer">
                            <input class="inputEmail" name="title" type="text" placeholder="Title:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="location" type="text" placeholder="Location:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="date" type="date" placeholder="Date:">
                        </div>

                        <div class="inputContainer" style="height: 10rem;">
                            <textarea class="inputEmail" name="description" id="description"
                                placeholder="Description"></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" name="add_announcement" class="addButton"
                                style="width: 6rem;">Add</button>
                            <button onclick="addProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>



    <!-- Edit Container -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="editContainer" style="display: none; background-color: none;">
            <div class="editContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Edit Events</p>
                    </div>

                    <div class="subLoginContainer">

                        <!-- Hidden input to store event id -->
                        <input type="hidden" name="announcement_id" id="announcement_id">

                        <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Title:</label>
                            <input class="inputEmail" type="text" id="announcement_title" name="title" value=""
                                placeholder="Title:">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Location:</label>
                            <input class="inputEmail" type="text" id="announcement_location" name="location" value=""
                                placeholder="Location:">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Date:</label>
                            <input class="inputEmail" type="date" id="announcement_date" name="date" value=""
                                placeholder="Date:">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 5rem; min-height: 12rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Content:</label>
                            <textarea style="min-height: 10rem;" id="announcement_description" class="inputEmail"
                                value="" name="description" id="" placeholder="Content"></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                            <button type="submit" name="update_announcement" class="addButton"
                                style="width: 6rem;">Save</button>
                            <button onclick="cancelContainer()" class="addButton1" style="width: 6rem;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
    <script src="../assets/js/printTable.js"></script>
    <script src="../assets/js/search.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function editProgram(id, title, description, date, location) {
            document.getElementById('announcement_id').value = id;
            document.getElementById('announcement_title').value = title;
            document.getElementById('announcement_location').value = location;
            document.getElementById('announcement_date').value = date;
            document.getElementById('announcement_description').value = description;

            document.querySelector('.editContainer').style.display = 'block';
        }

        function cancelContainer() {
            document.querySelector('.editContainer').style.display = 'none';
        }

        function deleteAnnouncement(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete this announcement?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "?delete_id=" + userId;
                }
            });
        }


        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: "<?php echo $_SESSION['message_type']; ?>",
                title: "<?php echo $_SESSION['message']; ?>",
                showConfirmButton: false,
                timer: 3000
            });
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
    </script>
</body>

</html>