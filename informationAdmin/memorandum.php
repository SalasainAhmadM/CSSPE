<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('information_admin');

$query = "SELECT * FROM memorandums";
$result = mysqli_query($conn, $query);

// Handle adding a new memorandum
if (isset($_POST['add_memorandum'])) {

    $memorandum_title = mysqli_real_escape_string($conn, $_POST['memorandum_title']);
    $memorandum_description = mysqli_real_escape_string($conn, $_POST['memorandum_description']);
    $uploaded_at = date('Y-m-d H:i:s'); // Current timestamp


    if (isset($_FILES['memorandum_file']) && $_FILES['memorandum_file']['error'] === 0) {

        $file_name = $_FILES['memorandum_file']['name'];
        $file_tmp = $_FILES['memorandum_file']['tmp_name'];
        $upload_dir = '../assets/uploads/'; 

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_path = $upload_dir . uniqid() . '_' . basename($file_name);

        // Move file to upload directory
        if (move_uploaded_file($file_tmp, $file_path)) {

            $insert_query = "INSERT INTO memorandums (file_path, title, description, uploaded_at) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssss", $file_path, $memorandum_title, $memorandum_description, $uploaded_at);

            $notification_query = "INSERT INTO notifications (title, description, uploaded_at, type) 
            VALUES (?, ?, ?, 'Memorandums')";
            $stmt_notification = $conn->prepare($notification_query);
            $stmt_notification->bind_param("sss", $memorandum_title, $memorandum_description, $uploaded_at);


            if ($stmt->execute() && $stmt_notification->execute()) {
                $_SESSION['success'] = "Memorandum and notification added successfully!";
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error'] = "Failed to add memorandum and notification. Please try again.";
            }

            $stmt->close();
            $stmt_notification->close();
        } else {
            $_SESSION['error'] = "File upload failed. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Please upload a valid file.";
    }
}




// Update event logic
if (isset($_POST['update_memorandum'])) {
    $memorandum_id = $_POST['memorandum_id'];
    $memorandum_title = mysqli_real_escape_string($conn, $_POST['memorandum_title']);
    $memorandum_description = mysqli_real_escape_string($conn, $_POST['memorandum_description']);
    $uploaded_at = date('Y-m-d H:i:s'); 

    $new_file_path = null;

    // Check if a new file is uploaded
    if (isset($_FILES['memorandum_file']) && $_FILES['memorandum_file']['error'] === 0) {

        $file_name = $_FILES['memorandum_file']['name'];
        $file_tmp = $_FILES['memorandum_file']['tmp_name'];
        $upload_dir = '../assets/uploads/'; 

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $new_file_path = $upload_dir . uniqid() . '_' . basename($file_name);

        if (!move_uploaded_file($file_tmp, $new_file_path)) {
            $_SESSION['error'] = "File upload failed. Please try again.";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    if ($new_file_path === null) {
        $existing_query = "SELECT file_path FROM memorandums WHERE id = ?";
        $stmt = $conn->prepare($existing_query);
        $stmt->bind_param("i", $memorandum_id);
        $stmt->execute();
        $stmt->bind_result($existing_file_path);
        $stmt->fetch();
        $stmt->close();

        $new_file_path = $existing_file_path;
    }

    // Update the memorandum record in the database
    $update_query = "UPDATE memorandums SET title = ?, description = ?, file_path = ?, updated_at = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssii", $memorandum_title, $memorandum_description, $new_file_path, $uploaded_at, $memorandum_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Memorandum updated successfully!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error'] = "Failed to update memorandum. Please try again.";
    }

    $stmt->close();
}



// delete request
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM memorandums WHERE id = $user_id";
    if (mysqli_query($conn, $delete_query)) {
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
    <title>Events</title>

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
                            <img class="subUserPictureContainer" src="../assets/img/CSSPE.png" alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p>Khriz marr l. falcatan</p>
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
                    <a href="../authentication/login.php">
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
                    <p class="text">Events</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" id="search" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size" onclick="printTable()">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Events</button>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>File path</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Uploaded At</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" download>
                                            <?php echo htmlspecialchars(basename($row['file_path'])); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['uploaded_at']); ?></td>
                                    <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                                    <td class="button">
                                        <a href="#" onclick="editProgram(<?php echo $row['id']; ?>, 
                                        '<?php echo addslashes($row['title']); ?>', 
                                        '<?php echo addslashes($row['description']); ?>')">
                                            <button class="addButton1" style="width: 6rem;">Edit</button>
                                        </a>
                                        <a href="#" onclick="deleteMemorandum(<?php echo $row['id']; ?>)">
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
                        <p>Add Memorandum</p>
                    </div>

                    <div class="subLoginContainer">
                        <div class="inputContainer">
                            <input class="inputEmail" name="memorandum_file" type="file" accept="application/pdf" placeholder="File path:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" id="memorandum_title" name="memorandum_title" type="text" placeholder="Title:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" id="memorandum_description" name="memorandum_description" type="text" placeholder="Description:">
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" name="add_memorandum" class="addButton" style="width: 6rem;">Add</button>
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
                        <p>Edit Memorandums</p>
                    </div>

                    <input type="hidden" name="memorandum_id" id="memorandum_id">

                    <div class="subLoginContainer">
                        <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                            <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">File path:</label>
                            <input class="inputEmail" id="memorandum_file" name="memorandum_file" type="file">
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                            <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Title:</label>
                            <input class="inputEmail" id="memorandum_title" value="" name="memorandum_title" type="text">
                        </div>

                        <div class="inputContainer" style="flex-direction: column;">
                            <label for="" style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Description:</label>
                            <input class="inputEmail" id="memorandum_description" value="" name="memorandum_description" type="text">
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                            <button type="submit" name="update_memorandum" class="addButton" style="width: 6rem;">Save</button>
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
        function editProgram(id, title, description) {
            document.getElementById('memorandum_id').value = id;
            document.getElementById('memorandum_title').value = title;
            document.getElementById('memorandum_description').value = description;

            document.querySelector('.editContainer').style.display = 'block';
        }

        function cancelContainer() {
            document.querySelector('.editContainer').style.display = 'none';
        }
    </script>

    <script>
        function deleteMemorandum(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete this memorandum?',
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
    </script>
</body>

</html>