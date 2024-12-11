<?php
session_start();
require_once '../conn/conn.php';

$query = "SELECT * FROM organizations";
$result = mysqli_query($conn, $query);

// Check if the form was submitted
if (isset($_POST['add_organization'])) {
    $organization_name = mysqli_real_escape_string($conn, $_POST['organization_name']);
    $department_description = mysqli_real_escape_string($conn, $_POST['organization_description']);

    if (isset($_FILES['organization_image']) && $_FILES['organization_image']['error'] == 0) {
        $image_name = $_FILES['organization_image']['name'];
        $image_tmp = $_FILES['organization_image']['tmp_name'];
        $image_size = $_FILES['organization_image']['size'];

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

    $query = "INSERT INTO organizations (organization_name, description, image) 
              VALUES ('$organization_name', '$department_description', '$image_path')";

    if (mysqli_query($conn, $query)) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}


// Check if the form was submitted for updating
if (isset($_POST['update_organization'])) {
    $organization_id = $_POST['organization_id'];
    $organization_name = mysqli_real_escape_string($conn, $_POST['organization_name']);
    $department_description = mysqli_real_escape_string($conn, $_POST['organization_description']);

    if (isset($_FILES['organization_image']) && $_FILES['organization_image']['error'] == 0) {
        $image_name = $_FILES['organization_image']['name'];
        $image_tmp = $_FILES['organization_image']['tmp_name'];
        $image_size = $_FILES['organization_image']['size'];

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
        $result = mysqli_query($conn, "SELECT image FROM organizations WHERE id = '$organization_id'");
        $row = mysqli_fetch_assoc($result);
        $image_path = $row['image']; 
    }

    $query = "UPDATE organizations 
              SET organization_name = '$organization_name', description = '$department_description', image = '$image_path' 
              WHERE id = '$organization_id'";

    if (mysqli_query($conn, $query)) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// delete request
if (isset($_GET['delete_id'])) {
    $organizations_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM organizations WHERE id = $organizations_id";
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
    <title>Organizations</title>

    <link rel="stylesheet" href="../assets/css/organization.css">
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
                            <img class="subUserPictureContainer" src="../assets/img/CSSPE.png" alt="">
                        </div>
                    </div>

                    <div class="userPictureContainer1">
                        <p>Khriz marr l. falcatan</p>
                    </div>
                </div>

                <div class="navContainer">
                    <div class="subNavContainer">

                        <a href="../informationAdmin/homePage/announcement.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Home</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/program.php">
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

                        <a href="../informationAdmin/oraganization.php">
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
                    <p class="text">Organizations</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Organization</button>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Organization Name</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['organization_name']); ?></td>
                                    <td><img class="image" src="<?php echo htmlspecialchars($row['image']); ?>" alt=""></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td class="button">
                                        <a href="#" onclick="editProgram(<?php echo $row['id']; ?>)">
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

    <div class="popup" style="display: none;">
        <div class="popup">
            <div class="mainContainer" style="margin-left: 250px;">
                <div class="container">

                    <div class="textContainer">
                        <p class="text">Tech Club</p>
                    </div>

                    <div class="searchContainer">
                        <input class="searchBar" type="text" placeholder="Search...">
                        <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                            <button class="addButton size">Print</button>
                            <button onclick="addProgram()" class="addButton size">Add Organization</button>
                        </div>
                    </div>

                    <div class="tableContainer">
                        <table>
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Image</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>Hakdog</td>
                                    <td>
                                        <img class="image" src="../assets/img/CSSPE.png" alt="">
                                    </td>
                                    <td>Hakdog</td>
                                    <td class="button">
                                        <button onclick="editProgram()" class="addButton"
                                            style="width: 5rem;">Edit</button>
                                        <button class="addButton1" style="width: 5rem;">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="addContainer" style="display: none; background-color: none;">
            <div class="addContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Add Organization</p>
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
                                <input id="imageUpload" type="file" name="organization_image" accept="image/*" style="display: none;" onchange="previewImage()">
                                <button type="button" onclick="triggerImageUpload()" class="addButton" style="height: 2rem; width: 5rem;">Upload</button>
                            </div>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="organization_name" placeholder="Organization Name" required>
                        </div>

                        <div class="inputContainer" style="height: 10rem;">
                            <textarea class="inputEmail" name="organization_description" placeholder="Description" required></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" name="add_organization" class="addButton" style="width: 6rem;">Add</button>
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
                        <p>Add Organization</p>
                    </div>

                    <div class="subLoginContainer">

                        <!-- Hidden input to store event id -->
                        <input type="hidden" name="organization_id" id="organization_id">

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
                                <input id="imageUpload" type="file" name="organization_image" accept="image/*" style="display: none;" onchange="previewImage_edit()">
                                <button type="button" onclick="triggerImageUpload()" class="addButton" style="height: 2rem; width: 5rem;">Upload</button>
                            </div>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="organization_name" placeholder="Organization Name">
                        </div>

                        <div class="inputContainer" style="height: 10rem;">
                            <textarea class="inputEmail" name="organization_description" placeholder="Description"></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" name="update_organization" class="addButton" style="width: 6rem;">Add</button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    <script>
        function editProgram(id) {
            document.getElementById('organization_id').value = id;
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
                text: 'Do you want to delete this user?',
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