<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('information_admin');

// Fetch data from the pending_users table
$query = "SELECT id, first_name, last_name, middle_name, email, address, contact_no, rank, password, created_at, role, department, image FROM users";
$result = mysqli_query($conn, $query);

// delete request
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM users WHERE id = $user_id";
    if (mysqli_query($conn, $delete_query)) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

function fetchDepartments()
{
    global $conn;
    $query = "SELECT id, department_name FROM departments";
    $result = $conn->query($query);

    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }

    return $departments;
}


if (isset($_POST['add_faculty'])) {

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $rank = mysqli_real_escape_string($conn, $_POST['rank']);

    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);


    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $image = $_FILES['profile_image'];
        $image_name = basename($image['name']);
        $image_tmp_name = $image['tmp_name'];
        $image_size = $image['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($image_ext), $allowed_ext) && $image_size < 5000000) {
            $new_image_name = uniqid() . '.' . $image_ext;
            $image_path = '../assets/img/' . $new_image_name;

            // Move uploaded image to the directory
            if (move_uploaded_file($image_tmp_name, $image_path)) {
                $image_path = $new_image_name;
            } else {
                echo "Error uploading the image.";
                $image_path = 'CSSPE.png'; // default image
            }
        } else {
            $image_path = 'CSSPE.png';
        }
    } else {
        $image_path = 'CSSPE.png';
    }


    // Insert user data into the database
    $insert_query = "INSERT INTO users (first_name, last_name, middle_name, email, address, contact_no, department, rank, password, image)
                 VALUES ('$first_name', '$last_name', '$middle_name', '$email', '$address', '$contact_no', '$department', '$rank', '$hashedPassword', '$image_path')";

    if (mysqli_query($conn, $insert_query)) {
        echo "New user added successfully!";
        header("Location: facultyMember.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}




if (isset($_POST['update_faculty'])) {
    $faculty_id = $_POST['faculty_id'];

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $rank = mysqli_real_escape_string($conn, $_POST['rank']);

    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $hashedPassword = $_POST['current_password'];
    }

    // Handle image upload for update
    if (isset($_FILES['faculty_image']) && $_FILES['faculty_image']['error'] == 0) {
        $image = $_FILES['faculty_image'];
        $image_name = basename($image['name']);
        $image_tmp_name = $image['tmp_name'];
        $image_size = $image['size'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($image_ext), $allowed_ext) && $image_size < 5000000) {
            $new_image_name = uniqid() . '.' . $image_ext;
            $image_path = '../assets/img/' . $new_image_name;

            // Move uploaded image to the directory
            if (move_uploaded_file($image_tmp_name, $image_path)) {
                $image_path = $new_image_name;
            } else {
                echo "Error uploading the image.";
                $image_path = 'CSSPE.png'; // default image
            }
        } else {
            $image_path = 'CSSPE.png';
        }
    } else {
        // If no new image is uploaded, retain the current image
        $query_image = "SELECT image FROM users WHERE id = $faculty_id";
        $result_image = mysqli_query($conn, $query_image);
        $row = mysqli_fetch_assoc($result_image);
        $image_path = $row['image'];
    }

    $update_query = "UPDATE users 
                     SET first_name = '$first_name', last_name = '$last_name', middle_name = '$middle_name', email = '$email', address = '$address', contact_no = '$contact_no', department = '$department', rank = '$rank', password = '$hashedPassword', image = '$image_path' 
                     WHERE id = $faculty_id";

    if (mysqli_query($conn, $update_query)) {
        echo "Faculty member updated successfully!";
        header("Location: facultyMember.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Member</title>

    <link rel="stylesheet" href="../assets/css/facultyMember.css">
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
                    <p class="text">Faculty Members</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" id="search" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size" onclick="printTable()">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Faculty Member</button>
                    </div>
                </div>

                <div class="tableContainer" style="height:475px">
                    <table>
                        <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Image</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Contact Number</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                    <td>
                                        <img class="" src="<?= '../assets/img/' . htmlspecialchars($row['image']) ?>" style="width:100px" alt="">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['rank']); ?></td>
                                    <td class="button">
                                        <a href="#" onclick="editProgram(<?php echo $row['id']; ?>,
                                        '<?php echo addslashes('../assets/img/' . $row['image']); ?>',
                                        '<?php echo addslashes($row['first_name']); ?>',
                                        '<?php echo addslashes($row['middle_name']); ?>',
                                        '<?php echo addslashes($row['last_name']); ?>',
                                        '<?php echo addslashes($row['email']); ?>',
                                        '<?php echo addslashes($row['password']); ?>',
                                        '<?php echo addslashes($row['address']); ?>',
                                        '<?php echo addslashes($row['contact_no']); ?>',
                                        '<?php echo addslashes($row['department']); ?>',
                                        '<?php echo addslashes($row['rank']); ?>')">
                                            <button class="addButton1" style="width: 6rem;">Edit</button>
                                        </a>
                                        <a href="#" onclick="deleteUser(<?php echo $row['id']; ?>)">
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
                        <p>Add Faculty Member</p>
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
                                <input id="imageUpload" type="file" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage()">
                                <button type="button" onclick="triggerImageUpload()" class="addButton" style="height: 2rem; width: 5rem;">Upload</button>
                            </div>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="first_name" type="text" placeholder="First Name:" >
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="last_name" type="text" placeholder="Last Name:" >
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="middle_name" type="text" placeholder="Middle Name (Optional):">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="password" type="password" placeholder="Password:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="email" type="email" placeholder="Email:" >
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="address" type="text" placeholder="Address:" >
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="contact_no" type="text" placeholder="Contact No.:" >
                        </div>

                        <div class="inputContainer">
                            <select class="inputEmail" name="department" >
                                <option value="">Choose a Department</option>
                                <?php
                                $departments = fetchDepartments();
                                foreach ($departments as $department) {
                                    echo "<option value='" . $department['department_name'] . "'>" . $department['department_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="inputContainer">
                            <select class="inputEmail" name="rank" >
                                <option value="">Choose a Rank</option>
                                <option value="Instructor">Instructor</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Professor">Professor</option>
                            </select>
                        </div>

                        <div class="inputContainer">
                            <button type="submit" name="add_faculty" class="addButton">Add Faculty Member</button>
                        </div>
                        <div class="inputContainer">
                            <button onclick="cancelEdit()" class="addButton1" style="width: 6rem;">Cancel</button>
                        </div>


                    </div>
    </form>

    </div>
    </div>
    </div>


    <!-- Edit Container -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="editContainer" style="display: none; background-color: none;">
            <div class="editContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Edit Faculty Member Information</p>
                    </div>

                    <div class="subLoginContainer">

                        <!-- Hidden input to store event id -->
                        <input type="hidden" name="faculty_id" id="faculty_id">

                        <div class="uploadContainer">
                            <div class="subUploadContainer">
                                <div class="uploadContainer">
                                    <div class="subUploadContainer">
                                        <div class="displayImage">
                                            <img class="image1" id="faculty_image" src="" alt="Image Preview" style="max-width: 100%; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="uploadButton">
                                <input type="file" name="faculty_image" accept="image/*" >
                            </div>
                        </div>
                        
                        

                        <div class="inputContainer">
                            <input class="inputEmail" name="first_name" id="first_name" type="text" placeholder="First Name:" >
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="last_name" id="last_name" type="text" placeholder="Last Name:" >
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="middle_name" id="middle_name" type="text" placeholder="Middle Name (Optional):">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="password" id="password" type="password" placeholder="Password:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="email" id="email" type="email" placeholder="Email:" >
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="address" id="address" type="text" placeholder="Address:" >
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="contact_no" id="contact_no" type="text" placeholder="Contact No.:" >
                        </div>

                        <div class="inputContainer">
                            <select class="inputEmail" name="department" id="department" >
                                <option value="">Choose a Department</option>
                                <?php
                                $departments = fetchDepartments();
                                foreach ($departments as $department) {
                                    echo "<option value='" . $department['department_name'] . "'>" . $department['department_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="inputContainer">
                            <select class="inputEmail" name="rank" id="rank" >
                                <option value="">Choose a Rank</option>
                                <option value="Instructor">Instructor</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Professor">Professor</option>
                            </select>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                            <button type="submit" name="update_faculty" class="addButton" style="width: 6rem;">Save</button>
                            <button onclick="cancelContainer()" class="addButton1" style="width: 6rem;">Cancel</button>
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
        function editProgram(id, image, first_name, middle_name, last_name, email, password, address, contact_no, department, rank) {
            document.getElementById('faculty_id').value = id;

            document.getElementById('faculty_image').src = image; 
            document.getElementById('faculty_image').style.display = 'block'; 

            document.getElementById('first_name').value = first_name;
            document.getElementById('last_name').value = last_name;
            document.getElementById('middle_name').value = middle_name;
            document.getElementById('password').value = password;
            document.getElementById('email').value = email;
            document.getElementById('address').value = address;
            document.getElementById('contact_no').value = contact_no;
            document.getElementById('department').value = department;
            document.getElementById('rank').value = rank;

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
        function deleteUser(userId) {
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