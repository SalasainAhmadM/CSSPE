<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $inventoryAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
} else {
    $fullName = "User Not Found";
}

// Fetch items for the current user
$itemQuery = "SELECT id, name, description, brand, quantity, image FROM items WHERE users_id = ?";
$itemStmt = $conn->prepare($itemQuery);
$itemStmt->bind_param("i", $inventoryAdminId);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/dionSe/assets/css/inventory.css">
    <link rel="stylesheet" href="/dionSe/assets/css/sidebar.css">
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

                        <a href="../inventoryAdmin/homePage/announcement.php">
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
                            <img class="logo" src="/dionSe/assets/img/CSSPE.png" alt="">
                        </div>

                        <div class="collegeNameContainer">
                            <p>CSSPE Inventory & Information System</p>
                        </div>
                    </div>
                </div>

                <div class="textContainer">
                    <p class="text">Inventories</p>
                </div>

                <div class="searchContainer">
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Item</button>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php while ($item = $itemResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                        <?php if (!empty($item['image'])): ?>
                                            <img class="image"
                                                src="../assets/uploads/<?php echo htmlspecialchars($item['image']); ?>"
                                                alt="Item Image" style="width: 50px; height: 50px;">
                                        <?php else: ?>
                                            <span>No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td><?php echo htmlspecialchars($item['brand']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="button">
                                        <button onclick="editItem(this)" class="addButton" style="width: 5rem;"
                                            data-id="<?php echo $item['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($item['description']); ?>"
                                            data-brand="<?php echo htmlspecialchars($item['brand']); ?>"
                                            data-quantity="<?php echo htmlspecialchars($item['quantity']); ?>"
                                            data-image="../assets/uploads/<?php echo htmlspecialchars($item['image']); ?>">Edit</button>
                                        <button onclick="deleteItem(<?php echo $item['id']; ?>)" class="addButton1"
                                            style="width: 5rem;">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <form id="addItemForm" method="POST" enctype="multipart/form-data">
        <div class="addContainer" style="display: none; background-color: none;">
            <div class="addContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Add Item</p>
                    </div>

                    <div class="sublogoutContainer">
                        <div class="subUploadContainer">
                            <div class="displayImage">
                                <img class="image1" id="addPreviewImage" src="../assets/img/CSSPE.png"
                                    alt="Preview Image" style="max-width: 100%; height: auto; border: 1px solid red;" />
                            </div>
                        </div>
                        <div class="uploadButton">
                            <input id="addImageUpload" type="file" accept="image/*" style="display: none;"
                                onchange="previewImage('addImageUpload', 'addPreviewImage')" />
                            <button type="button" onclick="triggerImageUpload('addImageUpload')" class="addButton"
                                style="height: 2rem; width: 5rem;">
                                Upload
                            </button>
                        </div>
                    </div>


                    <div class="inputContainer">
                        <input id="itemName" class="inputEmail" type="text" placeholder="Item Name:" required>
                    </div>

                    <div class="inputContainer">
                        <input id="itemBrand" class="inputEmail" type="text" placeholder="Brand:" required>
                    </div>

                    <div class="inputContainer">
                        <input id="itemQuantity" class="inputEmail" type="number" placeholder="Quantity:" required>
                    </div>

                    <div class="inputContainer" style="height: 10rem;">
                        <textarea id="itemDescription" class="inputEmail" placeholder="Description" required></textarea>
                    </div>

                    <form id="addItemForm">
                        <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
                            <button type="submit" class="addButton" style="width: 6rem;">Add</button>
                            <button type="button" onclick="addProgram()" class="addButton1"
                                style="width: 6rem;">Cancel</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        </div>
    </form>



    <div class="editContainer" style="display: none; background-color: none;">
        <div class="editContainer">
            <div class="subAddContainer">
                <div class="titleContainer">
                    <p>Edit Item</p>
                </div>

                <div class="subLoginContainer">
                    <div class="uploadContainer">
                        <div class="subUploadContainer">
                            <div class="displayImage">
                                <img class="image1" id="previewImage" src="../assets/img/CSSPE.png" alt="Preview Image"
                                    style="max-width: 100%; height: auto;">
                            </div>
                        </div>
                        <div class="uploadButton">
                            <input id="imageUpload" type="file" accept="image/*" onchange="previewImage()"
                                style="display: none;">

                            <button type="button" onclick="triggerImageUpload()" class="addButton"
                                style="height: 2rem; width: 5rem;">Upload</button>
                        </div>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Item
                            Name:</label>
                        <input class="inputEmail" type="text" placeholder="Item Name:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Brand:</label>
                        <input class="inputEmail" type="text" placeholder="Brand:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Quantity:</label>
                        <input class="inputEmail" type="number" placeholder="Quantity:">
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem; min-height: 12rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Description:</label>
                        <textarea style="min-height: 10rem;" class="inputEmail" name="" id=""
                            placeholder="Description"></textarea>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                        <button class="addButton" style="width: 6rem;" onclick="saveItem()">Save</button>


                        <button onclick="editProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get references to the search bar and table body
        const searchBar = document.getElementById('searchBar');
        const tableBody = document.getElementById('tableBody');

        // Add an input event listener to the search bar
        searchBar.addEventListener('input', function () {
            const searchTerm = searchBar.value.toLowerCase(); // Get the input value in lowercase
            const rows = tableBody.getElementsByTagName('tr'); // Get all table rows

            // Loop through all rows and filter them
            for (const row of rows) {
                const cells = row.getElementsByTagName('td'); // Get all cells in the row
                let match = false;

                // Check if any cell in the row contains the search term
                for (const cell of cells) {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        match = true;
                        break;
                    }
                }

                // Show or hide the row based on the match result
                row.style.display = match ? '' : 'none';
            }
        });

        // Trigger file input click
        function triggerImageUpload(inputId) {
            document.querySelector(`#${inputId}`).click();
        }

        // Preview uploaded image
        function previewImage(inputId, previewId) {
            const fileInput = document.querySelector(`#${inputId}`);
            const preview = document.querySelector(`#${previewId}`);

            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                };

                reader.readAsDataURL(fileInput.files[0]);
            }
        }

        document.getElementById('addItemForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('name', document.getElementById('itemName').value.trim());
            formData.append('brand', document.getElementById('itemBrand').value.trim());
            formData.append('quantity', document.getElementById('itemQuantity').value);
            formData.append('description', document.getElementById('itemDescription').value.trim());

            const imageUpload = document.getElementById('addImageUpload');
            if (imageUpload.files.length > 0) {
                formData.append('image', imageUpload.files[0]);
            }

            fetch('./endpoints/add_item.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message
                        });
                        document.getElementById('addItemForm').reset();
                        document.getElementById('addPreviewImage').src = "../assets/img/CSSPE.png";
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong!'
                    });
                });
        });

        // Function to preview the selected image
        function previewImage() {
            const fileInput = document.getElementById('imageUpload');
            const preview = document.getElementById('previewImage');
            const file = fileInput.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result; // Update the image preview
                };
                reader.readAsDataURL(file);
            }
        }

        // Function to trigger the file input
        function triggerImageUpload() {
            document.getElementById('imageUpload').click();
        }
        // Edit Item Function
        function editItem(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const brand = button.getAttribute('data-brand');
            const quantity = button.getAttribute('data-quantity');
            const image = button.getAttribute('data-image');

            // Show the edit container
            document.querySelector('.editContainer').style.display = 'block';

            // Populate fields
            document.querySelector('.editContainer input[placeholder="Item Name:"]').value = name;
            document.querySelector('.editContainer input[placeholder="Brand:"]').value = brand;
            document.querySelector('.editContainer input[placeholder="Quantity:"]').value = quantity;
            document.querySelector('.editContainer textarea[placeholder="Description"]').value = description;

            const previewImage = document.getElementById('previewImage');
            previewImage.src = image ? image : ''; // Update preview or reset to default
            previewImage.setAttribute('data-id', id); // Store the ID for saving
        }

        // Save Item Function
        function saveItem() {
            const id = document.getElementById('previewImage').getAttribute('data-id');
            const name = document.querySelector('.editContainer input[placeholder="Item Name:"]').value.trim();
            const brand = document.querySelector('.editContainer input[placeholder="Brand:"]').value.trim();
            const quantity = document.querySelector('.editContainer input[placeholder="Quantity:"]').value.trim();
            const description = document.querySelector('.editContainer textarea[placeholder="Description"]').value.trim();

            if (!id || !name || !brand || !quantity || !description) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill all fields before saving!',
                });
                return;
            }

            const formData = new FormData();
            formData.append('id', id);
            formData.append('name', name);
            formData.append('brand', brand);
            formData.append('quantity', quantity);
            formData.append('description', description);

            const imageInput = document.getElementById('imageUpload');
            if (imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }

            fetch('./endpoints/edit_item.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Item Updated',
                            text: 'The item has been updated successfully!',
                        }).then(() => {
                            location.reload(); // Reload to reflect changes
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: data.message || 'An error occurred while updating the item.',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again later.',
                    });
                });
        }
        function deleteItem(itemId) {
            // Use SweetAlert to confirm deletion
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action will permanently delete the item!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with deletion
                    fetch('./endpoints/delete_item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: itemId })
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', data.message, 'success').then(() => {
                                    // Reload the page or remove the row from the table
                                    location.reload(); // or remove the row dynamically
                                });
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch((error) => {
                            Swal.fire('Error!', 'An unexpected error occurred.', 'error');
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
    <script src="/dionSe/assets/js/program.js"></script>
    <!-- <script src="/dionSe/assets/js/uploadImage.js"></script> -->
</body>

</html>