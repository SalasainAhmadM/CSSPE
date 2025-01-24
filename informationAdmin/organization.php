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
        $_SESSION['message'] = "Organization added successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error adding organization!";
        $_SESSION['message_type'] = "error";
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
        $_SESSION['message'] = "Organization updated successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error updating organization!";
        $_SESSION['message_type'] = "error";
    }
}

// delete request
if (isset($_GET['delete_id'])) {
    $organizations_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM organizations WHERE id = $organizations_id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Organization deleted successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = "Error deleting organization!";
        $_SESSION['message_type'] = "error";
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
                    <p class="text">Organizations</p>
                </div>

                <div class="searchContainer">
                    <input class="searchBar" id="search" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size" onclick="printTable()">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Organization</button>
                    </div>
                </div>

                <div class="tableContainer" style="height:475px">
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
                                        <a href="#" onclick="editProgram(<?php echo $row['id']; ?>, 
                    '<?php echo addslashes($row['organization_name']); ?>',
                    '<?php echo addslashes($row['image']); ?>',
                    '<?php echo addslashes($row['description']); ?>')">
                                            <button class="addButton1" style="width: 6rem;">Edit</button>
                                        </a>
                                        <a href="#" onclick="deleteProgram(<?php echo $row['id']; ?>)">
                                            <button class="addButton1" style="width: 6rem;">Delete</button>
                                        </a>
                                        <button
                                            onclick="popupMP(<?php echo $row['id']; ?>, '<?php echo addslashes($row['organization_name']); ?>')"
                                            class="addButton" style="width: 10rem;">Manage Project</button>
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
        <div class="addContainer" id="manageProjectModal" style="display: none; background-color: none;">
            <div class="addContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Manage Projects for <span id="organizationName"></span></p>
                    </div>

                    <div class="subLoginContainer">

                        <div class="uploadContainer">
                            <div class="subUploadContainer">
                                <div class="uploadContainer">
                                    <div class="subUploadContainer">
                                        <div class="displayImage">
                                            <img class="image1" id="projectImagePreview" src="" alt="Image Preview"
                                                style="max-width: 100%; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="uploadButton">
                                <input id="projectImageUpload" type="file" name="project_image" accept="image/*"
                                    style="display: none;" onchange="previewProjectImage()">
                                <button type="button" onclick="triggerProjectImageUpload()" class="addButton"
                                    style="height: 2rem; width: 5rem;">Upload</button>
                            </div>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" type="text" id="projectName" name="project_name"
                                placeholder="Project Name" required>
                        </div>

                        <div class="inputContainer" style="height: 10rem;">
                            <textarea class="inputEmail" id="projectDescription" name="project_description"
                                placeholder="Description" required></textarea>
                        </div>

                        <!-- Hidden Field for Organization ID -->
                        <input type="hidden" id="organizationId" name="organization_id" value="">

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="button" onclick="addProject2()" class="addButton"
                                style="width: 6rem;">Add</button>
                            <button type="button" onclick="closeProjectModal()" class="addButton1"
                                style="width: 6rem;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function popupMP(organizationId, organizationName) {
            Swal.fire({
                title: `Manage Projects for ${organizationName}`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Add Project',
                cancelButtonText: 'Show Projects',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Open the Add Project Modal
                    document.getElementById('organizationId').value = organizationId;
                    document.getElementById('organizationName').textContent = organizationName;
                    document.getElementById('manageProjectModal').style.display = 'block';
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Open the Show Projects Popup
                    showProjects(organizationId, organizationName);
                }
            });
        }

        function showProjects(organizationId, organizationName) {
            // Display the popup
            const manageProjectPopup = document.getElementById('manageProjectPopup');
            manageProjectPopup.style.display = 'block';

            // Fetch and display projects for the selected organization
            fetchProjects(organizationId);
        }

        function fetchProjects(organizationId) {
            fetch(`fetch_projects.php?organization_id=${organizationId}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('projectsTableBody');
                    tbody.innerHTML = ''; // Clear previous content
                    data.forEach(project => {
                        const imageUrl = project.image ? project.image : '../assets/img/CSSPE.png';

                        tbody.innerHTML += `
                    <tr>
                        <td>${project.project_name}</td>
                        <td><img class="image" src="${imageUrl}" alt="Image" style="width: 50px; height: 50px;"></td>
                        <td>${project.description}</td>
                       <td class="button">
            <button onclick="editProject2(${project.id})" class="addButton" style="width: 5rem;">Edit</button>
            <button onclick="deleteProject(${project.id})" class="addButton1" style="width: 5rem;">Delete</button>
        </td>
                    </tr>
                `;
                    });
                })
                .catch(error => {
                    console.error('Error fetching projects:', error);
                });
        }

        function editProject2(projectId) {
            // Fetch the project details
            fetch(`fetch_project_details.php?id=${projectId}`)
                .then(response => response.json())
                .then(project => {
                    // Debug: Log the fetched project details
                    console.log('Fetched project details:', project);

                    // Check if the project details are valid
                    if (project.error) {
                        Swal.fire('Error', project.error, 'error');
                        return;
                    }

                    // Show SweetAlert with current project details
                    Swal.fire({
                        title: 'Edit Project',
                        html: `
                    <div style="text-align: left; margin: 10px;">
                        <label for="projectName" style="font-weight: bold;">Project Details</label>
                        <input type="text" id="projectName2" class="swal2-input" placeholder="Enter project name" value="${project.project_name || ''}">
                        
                        <textarea id="projectDescription2" class="swal2-textarea" placeholder="Enter project description">${project.description || ''}</textarea>
                        
                        <input type="file" id="projectImage" class="swal2-file">
                        
                        ${project.image ? `<img src="${project.image}" alt="Project Image" style="width: 100%; margin-top: 10px; border-radius: 8px;">` : ''}
                    </div>
                `,
                        confirmButtonText: 'Save Changes',
                        showCancelButton: true,
                        focusConfirm: false,
                        preConfirm: () => {
                            const projectName = document.querySelector('#projectName2').value.trim();
                            const projectDescription = document.querySelector('#projectDescription2').value.trim();
                            const projectImage = document.querySelector('#projectImage').files[0];

                            // Debugging: Log the values to ensure they are being captured
                            console.log('Project Name:', projectName);
                            console.log('Project Description:', projectDescription);
                            console.log('Project Image:', projectImage);

                            // Validate input fields
                            if (!projectName) {
                                Swal.showValidationMessage('Project Name is required.');
                                return false;
                            }
                            if (!projectDescription) {
                                Swal.showValidationMessage('Project Description is required.');
                                return false;
                            }

                            // Construct FormData object
                            const formData = new FormData();
                            formData.append('id', projectId);
                            formData.append('project_name', projectName);
                            formData.append('description', projectDescription);

                            if (projectImage) {
                                formData.append('image', projectImage);
                            }

                            return formData;
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            // Send updated project data to the server
                            fetch('update_project.php', {
                                method: 'POST',
                                body: result.value
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            icon: "success",
                                            title: data.message || "Project updated successfully!",
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                        fetchProjects(project.organization_id);
                                    } else {
                                        Swal.fire('Error', data.error || 'Failed to update project.', 'error');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error updating project:', error);
                                    Swal.fire('Error', 'An error occurred while updating the project.', 'error');
                                });

                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching project details:', error);
                    Swal.fire('Error', 'Failed to fetch project details.', 'error');
                });
        }


        function deleteProject(projectId) {
            Swal.fire({
                title: 'Delete Project',
                text: "Are you sure you want to delete this project?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`delete_project.php?id=${projectId}`, {
                        method: 'DELETE',
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: data.message || "Project deleted successfully!",
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                fetchProjects(data.organization_id);
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: data.message || "An error occurred. Please try again.",
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting project:', error);
                            Swal.fire({
                                icon: "error",
                                title: "An error occurred. Please try again.",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        });
                }
            });
        }

        function closeProjectModal() {
            document.getElementById('manageProjectModal').style.display = 'none';
        }

        function triggerProjectImageUpload() {
            document.getElementById('projectImageUpload').click();
        }

        function previewProjectImage() {
            const file = document.getElementById('projectImageUpload').files[0];
            const preview = document.getElementById('projectImagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function addProject2() {
            const projectName = document.getElementById('projectName').value.trim();
            const projectDescription = document.getElementById('projectDescription').value.trim();
            const organizationId = document.getElementById('organizationId').value;
            const projectImageUpload = document.getElementById('projectImageUpload').files[0];

            if (!projectName || !projectDescription || !organizationId) {
                Swal.fire({
                    icon: "warning",
                    title: "Please fill out all fields",
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }

            // Assuming you want to handle the form via JavaScript (AJAX example)
            const formData = new FormData();
            formData.append("project_name", projectName);
            formData.append("project_description", projectDescription);
            formData.append("organization_id", organizationId);
            if (projectImageUpload) {
                formData.append("project_image", projectImageUpload);
            }

            fetch("add_project.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: data.message || "Project added successfully!",
                            showConfirmButton: false,
                            timer: 3000
                        });
                        closeProjectModal();
                        // Optionally refresh the project list
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: data.message || "An error occurred. Please try again.",
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: "error",
                        title: "An error occurred. Please try again.",
                        showConfirmButton: false,
                        timer: 3000
                    });
                    console.error("Error:", error);
                });
        }



    </script>

    <style>
        .textContainer,
        .textContainer2 {
            height: 4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: min(2rem, 1.5rem);
            font-weight: bold;
            width: 100%;
        }

        .tableContainer,
        .tableContainer2 {
            margin-top: 2rem;
            width: 90%;
            overflow: auto;
            background-color: rgb(223, 222, 222);
            box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.699);
            overflow: auto;
        }

        .tableContainer::-webkit-scrollbar,
        .tableContainer2::-webkit-scrollbar {
            overflow: auto;
            width: 10px;
            height: 10px;
            cursor: pointer;
        }

        .tableContainer::-webkit-scrollbar-thumb,
        .tableContainer2::-webkit-scrollbar-thumb {
            background-color: rgb(109, 18, 10);
            border-radius: 0.5rem;
        }

        .tableContainer::-webkit-scrollbar-track,
        .tableContainer2::-webkit-scrollbar-track {
            background-color: rgb(175, 158, 156);
        }
    </style>

    <div id="manageProjectPopup" class="popup" style="display: none;">
        <div class="popup">
            <div class="mainContainer" style="margin-left: 250px;">
                <div class="container">

                    <div class="textContainer2">
                        <p class="text">Manage Projects</p>
                    </div>

                    <div class="searchContainer">
                        <input class="searchBar" type="text" id="searchProjects" placeholder="Search...">

                        <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                            <button class="addButton size" onclick="printTable2()">Print</button>
                            <!-- <button onclick="openAddProjectModal()" class="addButton size">Add Project</button> -->
                        </div>
                    </div>

                    <div class="tableContainer2">
                        <table>
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Image</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody id="projectsTableBody">
                                <!-- Data will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchProjects').addEventListener('input', function () {
            const searchQuery = this.value.toLowerCase();  // Get the search query (converted to lowercase)
            const rows = document.querySelectorAll('#projectsTableBody tr'); // Get all table rows

            rows.forEach(row => {
                const projectName = row.querySelector('td:first-child').textContent.toLowerCase(); // Get project name
                if (projectName.includes(searchQuery)) {
                    row.style.display = ''; // Show row if it matches the search query
                } else {
                    row.style.display = 'none'; // Hide row if it doesn't match
                }
            });
        });


        function printTable2() {
            const tableContainer = document.querySelector('.tableContainer2');
            const tableHeader = document.querySelector('.textContainer2');

            // Temporarily hide the last column (Action column)
            const rows = tableContainer.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = 'none'; // Hide the last cell
                }
            });

            // Get the HTML for printing
            const printContent = tableContainer.outerHTML;
            const printTableHeader = tableHeader.outerHTML;

            // Open a new window to print the content
            const printWindow = window.open('', '', 'width=800, height=600');
            printWindow.document.write(`
    <html>
    <head>
        <title>Print Table</title>
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
            .table-header {
                text-align: center;
                font-size: 24px;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="table-header">Manage Projects</div>
        ${printTableHeader}
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();

            // Restore the visibility of the last column (Action column)
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = ''; // Restore visibility
                }
            });
        }
    </script>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="addContainer2" style="display: none; background-color: none;">
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
                                            <img class="image1" id="preview" src="" alt="Image Preview"
                                                style="max-width: 100%; display: none;">
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="uploadButton">
                                <input id="imageUpload" type="file" name="organization_image" accept="image/*"
                                    style="display: none;" onchange="previewImage()">
                                <button type="button" onclick="triggerImageUpload()" class="addButton"
                                    style="height: 2rem; width: 5rem;">Upload</button>
                            </div>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="organization_name"
                                placeholder="Organization Name" required>
                        </div>

                        <div class="inputContainer" style="height: 10rem;">
                            <textarea class="inputEmail" name="organization_description" placeholder="Description"
                                required></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" name="add_organization" class="addButton"
                                style="width: 6rem;">Add</button>
                            <button onclick="addProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>




    <form method="POST" action="" enctype="multipart/form-data">
        <div class="addProject" style="display: none; background-color: none;">
            <div class="addContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Add Project</p>
                    </div>

                    <div class="subLoginContainer">

                        <!-- <div class="uploadContainer">
                            <div class="subUploadContainer">
                                <div class="uploadContainer">
                                    <div class="subUploadContainer">
                                        <div class="displayImage">
                                            <img class="image1" id="preview" src="" alt="Image Preview"
                                                style="max-width: 100%; display: none;">
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="uploadButton">
                                <input id="imageUpload" type="file" name="organization_image" accept="image/*"
                                    style="display: none;" onchange="previewImage()">
                                <button type="button" onclick="triggerImageUpload()" class="addButton"
                                    style="height: 2rem; width: 5rem;">Upload</button>
                            </div>
                        </div> -->

                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="project_name" placeholder="Project Name">
                        </div>

                        <div class="inputContainer" style="height: 10rem;">
                            <textarea class="inputEmail" name="project_description"
                                placeholder="Description"></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" name="add_organization" class="addButton"
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
                        <p>Edit Organization</p>
                    </div>

                    <div class="subLoginContainer">

                        <!-- Hidden input to store event id -->
                        <input type="hidden" name="organization_id" id="organization_id">

                        <div class="uploadContainer">
                            <div class="subUploadContainer">
                                <div class="uploadContainer">
                                    <div class="subUploadContainer">
                                        <div class="displayImage">
                                            <img class="image1" id="organization_image" src="" alt="Image Preview"
                                                style="max-width: 100%; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="uploadButton">
                                <input type="file" name="organization_image" accept="image/*">
                            </div>
                        </div>

                        <div class="inputContainer">
                            <input class="inputEmail" type="text" name="organization_name" id="organization_name"
                                placeholder="Organization Name">
                        </div>

                        <div class="inputContainer" style="height: 10rem;">
                            <textarea class="inputEmail" name="organization_description" id="organization_description"
                                placeholder="Description"></textarea>
                        </div>

                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" name="update_organization" class="addButton"
                                style="width: 6rem;">Add</button>
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
        function editProgram(id, name, image, description) {
            document.getElementById('organization_id').value = id;
            document.getElementById('organization_name').value = name;

            document.getElementById('organization_image').src = image;
            document.getElementById('organization_image').style.display = 'block';

            document.getElementById('organization_description').value = description;

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

            reader.onloadend = function () {
                const image = document.getElementById('preview');
                image.src = reader.result;
                image.style.display = 'block'; // Display the image after loading
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }

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