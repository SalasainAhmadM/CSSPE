<?php
session_start();
require_once '../conn/conn.php';

$query = "SELECT * FROM departments";
$result = mysqli_query($conn, $query);

// Check if the form was submitted
if (isset($_POST['add_department'])) {
    $department_name = mysqli_real_escape_string($conn, $_POST['department_name']);
    $department_description = mysqli_real_escape_string($conn, $_POST['department_description']);

    if (isset($_FILES['department_image']) && $_FILES['department_image']['error'] == 0) {
        $image_name = $_FILES['department_image']['name'];
        $image_tmp = $_FILES['department_image']['tmp_name'];
        $image_size = $_FILES['department_image']['size'];

        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $image_new_name = uniqid() . '.' . $image_ext;
        $image_path = "../assets/img/" . $image_new_name;

        if (in_array(strtolower($image_ext), ['jpg', 'jpeg', 'png', 'gif']) && $image_size < 5000000) {
            move_uploaded_file($image_tmp, $image_path);
        } else {
            echo "Invalid image format or size!";
            exit();
        }
    } else {
        $image_path = "../assets/img/CSSPE.png";
    }

    $query = "INSERT INTO departments (department_name, description, image) 
              VALUES ('$department_name', '$department_description', '$image_path')";

    if (mysqli_query($conn, $query)) {
        echo "Department added successfully!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}


// Update department logic
if (isset($_POST['update_department'])) {
    $department_id = $_POST['department_id'];
    $department_name = mysqli_real_escape_string($conn, $_POST['department_name']);
    $department_description = mysqli_real_escape_string($conn, $_POST['department_description']);

    if (isset($_FILES['department_image']) && $_FILES['department_image']['error'] == 0) {
        $image_name = $_FILES['department_image']['name'];
        $image_tmp = $_FILES['department_image']['tmp_name'];
        $image_size = $_FILES['department_image']['size'];

        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $image_new_name = uniqid() . '.' . $image_ext;
        $image_path = "../assets/img/" . $image_new_name;

        if (in_array(strtolower($image_ext), ['jpg', 'jpeg', 'png', 'gif']) && $image_size < 5000000) {
            move_uploaded_file($image_tmp, $image_path);
        } else {
            echo "Invalid image format or size!";
            exit();
        }
    } else {
        // If no new image is uploaded, retain the current image
        $query_image = "SELECT image FROM departments WHERE id = $department_id";
        $result_image = mysqli_query($conn, $query_image);
        $row = mysqli_fetch_assoc($result_image);
        $image_path = $row['image'];
    }

    // Update department information
    $update_query = "UPDATE departments 
                     SET department_name = '$department_name', description = '$department_description', image = '$image_path' 
                     WHERE id = $department_id";

    if (mysqli_query($conn, $update_query)) {
        echo "Department updated successfully!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}


// delete request
if (isset($_GET['delete_id'])) {
    $department_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM departments WHERE id = $department_id";
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
    <title>Department</title>

    <link rel="stylesheet" href="../assets/css/program.css">
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
                    <p class="text">Departments</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" id="search" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size" onclick="printTable()">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Departments</button>
                        <select name="" class="addButton size" id="">
                            <option value="">Filter</option>
                        </select>
                    </div>
                </div>

                <div class="tableContainer">
                    <table id="departmentTable">
                        <thead>
                            <tr>
                                <th>Department Name</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                                    <td><img class="image" src="<?php echo htmlspecialchars($row['image']); ?>" alt=""></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td class="button">
                                        <a href="#" onclick="editProgram(
                                            <?php echo $row['id']; ?>, 
                                            '<?php echo addslashes($row['image']); ?>', 
                                            '<?php echo addslashes($row['department_name']); ?>', 
                                            '<?php echo addslashes($row['description']); ?>')">
                                            <button class="addButton1" style="width: 6rem;">Edit</button>
                                        </a>
                                        <a href="#" onclick="deleteProgram(<?php echo $row['id']; ?>)">
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
        <div class="editContainer" style="display: none; background-color: none;">
            <div class="editContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Edit Departments</p>
                    </div>

                    <div class="subLoginContainer">

                        <!-- Hidden input to store event id -->
                        <input type="hidden" name="department_id" id="department_id">

                        <div class="uploadContainer">
                            <div class="subUploadContainer">
                                <div class="uploadContainer">
                                    <div class="subUploadContainer">
                                        <div class="displayImage">
                                            <img class="image1" id="department_image" src="" alt="Image Preview" style="max-width: 100%; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="uploadButton">
                                <input type="file" name="department_image" accept="image/*">
                            </div>
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Departments:</label>
                            <input class="inputEmail" type="text" id="department_name" name="department_name" placeholder="Departments:" required>
                        </div>

                        <div class="inputContainer" style="flex-direction: column; height: 5rem; min-height: 12rem;">
                            <label for=""
                                style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Description:</label>
                            <textarea style="min-height: 10rem;" class="inputEmail" name="department_description" id="department_description" placeholder="Description" required></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                            <button type="submit" name="update_department" type="button" class="addButton" style="width: 6rem;" id="saveButton" name="update_department">Save</button>
                            <button onclick="cancelContainer()" class="addButton1" style="width: 6rem;">Cancel</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>


    <form method="POST" action="" enctype="multipart/form-data">
        <div class="addContainer" style="display: none; background-color: none;">
            <div class="addContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Add Departments</p>
                    </div>

                    <div class="subLoginContainer">

                        <div class="uploadContainer">
                            <div class="subUploadContainer">
                                <div class="uploadContainer">
                                    <div class="subUploadContainer">
                                        <div class="displayImage">
                                            <img class="image1" id="preview" src="" alt="Image Preview" style="max-width: 100%; display: none;">
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="uploadButton">
                                <input id="imageUpload" type="file" name="department_image" accept="image/*" style="display: none;" onchange="previewImage()">
                                <button type="button" onclick="triggerImageUpload()" class="addButton" style="height: 2rem; width: 5rem;">Upload</button>
                            </div>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="department_name" placeholder="Department Name" required>
                        </div>

                        <div class="inputContainer" style="height: 10rem;">
                            <textarea class="inputEmail" name="department_description" placeholder="Description" required></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" name="add_department" class="addButton" style="width: 6rem;">Add</button>
                            <button onclick="addProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/program.js"></script>
    <script src="../assets/js/uploadImage.js"></script>
    <script src="../assets/js/printTable.js"></script>
    <script src="../assets/js/search.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        function editProgram(id, image, name, description) {
            document.getElementById('department_id').value = id;

            document.getElementById('department_image').src = image;
            document.getElementById('department_image').style.display = 'block';

            document.getElementById('department_name').value = name;
            document.getElementById('department_description').value = description;

            document.querySelector('.editContainer').style.display = 'block';
        }

        function cancelContainer() {
            document.querySelector('.editContainer').style.display = 'none';
        }
    </script>

    <script>
        function previewImage() {
            const file = document.getElementById('imageUpload').files[0];
            const reader = new FileReader();

            reader.onloadend = function() {
                const image = document.getElementById('preview');
                image.src = reader.result;
                image.style.display = 'block'; // Display the image after loading
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>

    <script>
        function deleteProgram(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete this department?',
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