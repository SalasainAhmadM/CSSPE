<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('super_admin');
$userid = $_SESSION['user_id'];
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
// Fetch data from the users table, excluding the super_admin role
$query = "SELECT id, first_name, last_name, middle_name, email, address, contact_no, rank, status, password, created_at, role, department, image 
          FROM users 
          WHERE role != 'super_admin'";
$result = mysqli_query($conn, $query);


// Delete request
if (isset($_GET['delete_id'])) {
    $user_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM users WHERE id = $user_id";

    if (mysqli_query($conn, $delete_query)) {

        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {

        $_SESSION['message'] = "Error deleting record: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
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

    // Function to handle empty values
    function checkEmpty($value)
    {
        return empty(trim($value)) ? "N/A" : mysqli_real_escape_string($GLOBALS['conn'], $value);
    }

    // Process form data
    $first_name = checkEmpty($_POST['first_name']);
    $last_name = checkEmpty($_POST['last_name']);
    $middle_name = checkEmpty($_POST['middle_name']);
    $email = checkEmpty($_POST['email']);
    $address = checkEmpty($_POST['address']);
    $contact_no = checkEmpty($_POST['contact_no']);
    $department = checkEmpty($_POST['department']);
    $rank = checkEmpty($_POST['rank']);
    $status = 0;

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
    $insert_query = "INSERT INTO users (first_name, last_name, middle_name, email, address, contact_no, department, rank, status, password, image)
                     VALUES ('$first_name', '$last_name', '$middle_name', '$email', '$address', '$contact_no', '$department', '$rank', '$status', '$hashedPassword', '$image_path')";

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
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    } else {
        $hashedPassword = $_POST['current_password'];
    }

    // Handle image upload
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

            if (move_uploaded_file($image_tmp_name, $image_path)) {
                $image_path = $new_image_name;
            } else {
                $image_path = 'CSSPE.png'; // Default image
            }
        } else {
            $image_path = 'CSSPE.png';
        }
    } else {
        $query_image = "SELECT image FROM users WHERE id = $faculty_id";
        $result_image = mysqli_query($conn, $query_image);
        $row = mysqli_fetch_assoc($result_image);
        $image_path = $row['image'];
    }

    $update_query = "UPDATE users 
                     SET first_name = '$first_name', last_name = '$last_name', middle_name = '$middle_name', 
                         email = '$email', address = '$address', contact_no = '$contact_no', 
                         department = '$department', rank = '$rank', status = '$status', 
                         password = '$hashedPassword', image = '$image_path' 
                     WHERE id = $faculty_id";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Faculty member updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        .passwordContainer {
            position: relative;
            display: flex;
            align-items: center;
        }


        .toggle-password-icon {
            position: absolute;
            right: 35px;
            cursor: pointer;
            color: #aaa;
            font-size: 18px;
        }

        .toggle-password-icon:hover {
            color: #333;
        }
    </style>

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

                        <a href="../superAdmin/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Accounts</p>
                                </div>
                            </div>
                        </a>

                        <a href="../superAdmin/pendingAccount.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Pending Accounts</p>
                                </div>
                            </div>
                        </a>

                        <a href="../superAdmin/createAdmin.php">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Create Account</p>
                                </div>
                            </div>
                        </a>

                        <a href="../informationAdmin/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Information Admin Panel</p>
                                </div>
                            </div>
                        </a>

                        <a href="../inventoryAdmin/">
                            <div class="buttonContainer1">
                                <div class="nameOfIconContainer">
                                    <p>Inventory Admin Panel</p>
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
                        <select name="" class="addButton size" id="roleFilter" onchange="filterByRole()">
                            <option value="">Choose Position</option>
                            <option value="instructor">Instructor</option>
                            <option value="admin">Admin</option>
                        </select>
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
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?>
                                    </td>
                                    <td>
                                        <img class=""
                                            src="<?= '../assets/img/' . (!empty($row['image']) ? htmlspecialchars($row['image']) : 'CSSPE.png') ?>"
                                            style="width:100px" alt="Image">
                                    </td>

                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['rank']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td>
                                        <?php
                                        echo htmlspecialchars($row['status'] == 0 ? 'Activated' : 'Deactivated');
                                        ?>
                                    </td>
                                    <td class="button">
                                        <a href="#" onclick="editProgram(<?php echo $row['id']; ?>,
                                        '<?php echo addslashes('../assets/img/' . $row['image']); ?>',
                                        '<?php echo addslashes($row['first_name']); ?>',
                                        '<?php echo addslashes($row['middle_name']); ?>',
                                        '<?php echo addslashes($row['last_name']); ?>',
                                        '<?php echo addslashes($row['email']); ?>',
                                        '<?php echo addslashes($row['address']); ?>',
                                        '<?php echo addslashes($row['contact_no']); ?>',
                                        '<?php echo addslashes($row['department']); ?>',
                                        '<?php echo addslashes($row['rank']); ?>',
                                        '<?php echo addslashes($row['status']); ?>')">
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

                        <!-- Hidden input to store faculty id -->
                        <input type="hidden" name="faculty_id" id="faculty_id">

                        <div class="uploadContainer">
                            <div class="subUploadContainer">
                                <div class="uploadContainer">
                                    <div class="subUploadContainer">
                                        <div class="displayImage">
                                            <img class="image1" id="faculty_image" src="../assets/img/CSSPE.png"
                                                style="max-width: 100%; display: block;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="uploadButton">
                                <input type="file" name="faculty_image" id="imageUpload" accept="image/*"
                                    onchange="previewImage()">
                            </div>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="first_name" id="first_name" type="text"
                                placeholder="First Name:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="last_name" id="last_name" type="text"
                                placeholder="Last Name:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="middle_name" id="middle_name" type="text"
                                placeholder="Middle Name (Optional):">
                        </div>

                        <div class="inputContainer passwordContainer">
                            <input class="inputEmail" name="password" id="password" type="password"
                                placeholder="Password:" required>
                            <i id="togglePassword" class="fas fa-eye toggle-password-icon"></i>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="email" id="email" type="email" placeholder="Email:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="address" id="address" type="text" placeholder="Address:">
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" name="contact_no" id="contact_no" type="text"
                                placeholder="Contact No.:">
                        </div>

                        <div class="inputContainer">
                            <select class="inputEmail" name="department" id="department">
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
                            <select class="inputEmail" name="rank" id="rank">
                                <option value="">Choose a Rank</option>
                                <option value="Instructor">Instructor</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Professor">Professor</option>
                            </select>
                        </div>

                        <div class="inputContainer">
                            <select class="inputEmail" name="status" id="status">
                                <option value="0">Activated</option>
                                <option value="1">Deactivated</option>
                            </select>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                            <button type="submit" name="update_faculty" class="addButton"
                                style="width: 6rem;">Save</button>
                            <button onclick="cancelContainer()" type="button" class="addButton1"
                                style="width: 6rem;">Cancel</button>
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
        // JavaScript to toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            // Toggle the password field type
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Toggle the icon class
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>

    <script>
        function filterByRole() {
            const selectedRole = document.getElementById('roleFilter').value.toLowerCase();
            const tableRows = document.querySelectorAll('.tableContainer table tbody tr');

            tableRows.forEach((row) => {
                const role = row.querySelector('td:nth-child(8)').textContent.trim().toLowerCase();

                if (
                    selectedRole === "" || // Show all if no role is selected
                    (selectedRole === "instructor" && role.includes("instructor")) || // Match "instructor"
                    (selectedRole === "admin" &&
                        (role.includes("information_admin") ||
                            role.includes("super_admin") ||
                            role.includes("inventory_admin"))
                    ) // Match any admin type
                ) {
                    row.style.display = ""; // Show matching rows
                } else {
                    row.style.display = "none"; // Hide non-matching rows
                }
            });
        }


        function editProgram(id, image, first_name, middle_name, last_name, email, address, contact_no, department, rank, status) {
            const defaultImage = '../assets/img/CSSPE.png';
            document.getElementById('faculty_id').value = id;

            // Use default image if the provided image is empty
            document.getElementById('faculty_image').src = image && image.trim() !== '' ? image : defaultImage;
            document.getElementById('faculty_image').style.display = 'block';

            document.getElementById('first_name').value = first_name;
            document.getElementById('last_name').value = last_name;
            document.getElementById('middle_name').value = middle_name;
            document.getElementById('email').value = email;
            document.getElementById('address').value = address;
            document.getElementById('contact_no').value = contact_no;
            document.getElementById('department').value = department;
            document.getElementById('rank').value = rank;
            document.getElementById('status').value = status;

            document.querySelector('.editContainer').style.display = 'block';
        }

        function cancelContainer() {
            document.querySelector('.editContainer').style.display = 'none';
        }


        document.addEventListener("DOMContentLoaded", function() {
            <?php if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
                Swal.fire({
                    icon: "<?php echo $_SESSION['message_type']; ?>",
                    title: "<?php echo $_SESSION['message']; ?>",
                    showConfirmButton: false,
                    timer: 3000
                });
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
        });

        // Function to confirm user deletion
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

        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: "<?php echo $_SESSION['message_type']; ?>",
                title: "<?php echo $_SESSION['message']; ?>",
                showConfirmButton: false,
                timer: 3000
            });
            <?php unset($_SESSION['message'], $_SESSION['message_type'], $_SESSION['message_text']); ?>
        <?php endif; ?>
    </script>

</body>

</html>