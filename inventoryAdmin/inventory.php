<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

$query = "SELECT first_name, middle_name, last_name, image FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $inventoryAdminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullName = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $image = $row['image'];
} else {
    $fullName = "User Not Found";
}

$limit = 6;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$itemQuery = "SELECT id, name, description, brand, quantity, type, note, image FROM items LIMIT $limit OFFSET $offset";
$itemStmt = $conn->prepare($itemQuery);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

$totalitemsQuery = "SELECT COUNT(*) AS total FROM items";
$totalitemsResult = $conn->query($totalitemsQuery);
$totalRow = mysqli_fetch_assoc($totalitemsResult);
$totalItems = $totalRow['total'];

$totalPages = ceil($totalItems / $limit);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="../assets/css/inventory.css">
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
                            <img class="logo" src="../assets/img/CSSPE.png" alt="">
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
                    <select name="" class="addButton size" id="rankFilter">
                        <option value="">Choose type</option>
                        <option value="Sport">Sport</option>
                        <option value="Gadget">Gadget</option>
                    </select>
                    <input id="searchBar" class="searchBar" type="text" placeholder="Search...">
                    <div class="printButton" style="gap: 1rem; display: flex; width: 90%;">
                        <button class="addButton size" onclick="printTable()">Print</button>
                        <button onclick="addProgram()" class="addButton size">Add Item</button>
                    </div>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Type</th>
                                <th>Warning Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php while ($item = $itemResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['id']); ?></td>
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
                                    <td>
                                        <?php
                                        echo htmlspecialchars($item['type'] === 'sport' ? 'Sport' : ($item['type'] === 'gadgets' ? 'Gadget' : 'Unknown'));
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['note']); ?></td>
                                    <td class="button">
                                        <button onclick="editItem(this)" class="addButton" style="width: 5rem;"
                                            data-id="<?php echo $item['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($item['description']); ?>"
                                            data-brand="<?php echo htmlspecialchars($item['brand']); ?>"
                                            data-note="<?php echo htmlspecialchars($item['note']); ?>"
                                            data-type="<?php echo htmlspecialchars($item['type']); ?>"
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

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="prev">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>"
                            class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="next">Next</a>
                    <?php endif; ?>
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
                                    alt="Preview Image"
                                    style="max-width: 100%; height: auto; border: 1px solid red; display: none;" />

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
                    <div class="inputContainer">
                        <select id="itemType" class="inputEmail" required>
                            <option value="" disabled selected hidden>Type:</option>
                            <option value="sport">Sport</option>
                            <option value="gadgets">Gadget</option>
                        </select>
                    </div>
                    <div class="inputContainer">
                        <input id="itemNote" class="inputEmail" type="text" placeholder="Note: (Optional)" required>
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
            <div class="subAddContainer"
                style="background-color: white; padding: 20px; border-radius: 10px;transform: scale(0.65);">
                <div class="titleContainer">
                    <p>Edit Item</p>
                </div>

                <div class="subLoginContainer">
                    <div class="uploadContainer">
                        <div class="subUploadContainer">
                            <div class="displayImage">
                                <img class="image" id="previewImage" src="../assets/img/CSSPE.png"
                                    style="max-width: 100%; height: auto;">
                            </div>
                        </div>
                        <div class="uploadButton">
                            <input id="imageUpload" type="file" accept="image/*" style="display: none;"
                                onchange="previewImage('imageUpload', 'previewImage')" />

                            <button type="button" onclick="triggerImageUpload('imageUpload')" class="addButton"
                                style="height: 2rem; width: 5rem;">
                                Upload
                            </button>
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

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for="type"
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">
                            Type:
                        </label>
                        <select id="type" name="type" class="inputEmail">
                            <option value="sport">Sport</option>
                            <option value="gadgets">Gadget</option>
                        </select>
                    </div>

                    <div class="inputContainer" style="flex-direction: column; height: 5rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Note:</label>
                        <input class="inputEmail" type="text" placeholder="Note:">
                    </div>
                    <!--  -->
                    <div class="inputContainer" style="flex-direction: column; height: 5rem;  min-height: 12rem;">
                        <label for=""
                            style="justify-content: left; display: flex; width: 100%; margin-left: 10%; font-size: 1.2rem;">Description:</label>
                        <textarea style="min-height: 10rem;" class="inputEmail" name="" id="description"
                            placeholder="Edit Description:"></textarea>
                    </div>

                    <div class="inputContainer" style="gap: 0.5rem; justify-content: right; padding-right: 1rem;">
                        <button class="addButton" style="width: 6rem;" onclick="saveItem()">Save</button>


                        <button onclick="editProgram()" class="addButton1" style="width: 6rem;">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Add this script at the bottom of your HTML or in a separate JS file -->
    <script>
        // Function to filter the table based on selected type
        document.getElementById('rankFilter').addEventListener('change', function () {
            filterTableByType();
        });

        function filterTableByType() {
            // Get the selected value from the dropdown
            var selectedType = document.getElementById('rankFilter').value.toLowerCase();

            // Get all table rows
            var rows = document.querySelectorAll('#tableBody tr');

            // Loop through all rows and show/hide based on type match
            rows.forEach(function (row) {
                var typeCell = row.cells[6]; // 'Type' column (index starts from 0)
                var type = typeCell ? typeCell.textContent.toLowerCase() : '';

                // Check if the selected type matches the type in the row, or if 'All' is selected
                if (selectedType === '' || type.includes(selectedType)) {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
            });
        }
    </script>


    <script>
        // Get references to the search bar and table body
        const searchBar = document.getElementById('searchBar');
        const tableBody = document.getElementById('tableBody');

        // Add an input event listener to the search bar
        searchBar.addEventListener('input', function () {
            const searchTerm = searchBar.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');


            for (const row of rows) {
                const cells = row.getElementsByTagName('td');
                let match = false;

                for (const cell of cells) {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        match = true;
                        break;
                    }
                }

                row.style.display = match ? '' : 'none';
            }
        });

        // Trigger file input click dynamically
        function previewImage(inputId, previewId) {
            const fileInput = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result; // Update the image preview
                    preview.style.display = 'block'; // Make the preview visible
                };
                reader.readAsDataURL(fileInput.files[0]);
            } else {
                preview.style.display = 'none'; // Hide the preview if no file is selected
            }
        }


        // Trigger file input click for Add and Edit functionality
        function triggerImageUpload(inputId) {
            document.getElementById(inputId).click();
        }


        // Add Item Form Submission
        document.getElementById('addItemForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('name', document.getElementById('itemName').value.trim());
            formData.append('brand', document.getElementById('itemBrand').value.trim());
            formData.append('quantity', document.getElementById('itemQuantity').value);
            formData.append('description', document.getElementById('itemDescription').value.trim());
            formData.append('type', document.getElementById('itemType').value.trim());
            formData.append('note', document.getElementById('itemNote').value.trim());

            const imageUpload = document.getElementById('addImageUpload');
            if (imageUpload.files.length > 0) {
                formData.append('image', imageUpload.files[0]);
            }

            fetch('./endpoints/add_item.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            timer: 3000,
                            showConfirmButton: false,
                        }).then(() => {
                            location.reload(); // Reload the page after success
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            timer: 3000,
                            showConfirmButton: false,
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong!',
                        timer: 3000,
                        showConfirmButton: false,
                    });
                });
        });


        // Edit Item Function
        function editItem(button) {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const brand = button.getAttribute('data-brand');
            const note = button.getAttribute('data-note');
            const quantity = button.getAttribute('data-quantity');
            const type = button.getAttribute('data-type');
            const image = button.getAttribute('data-image');

            document.querySelector('.editContainer').style.display = 'block';

            document.querySelector('.editContainer input[placeholder="Item Name:"]').value = name;
            document.querySelector('.editContainer input[placeholder="Brand:"]').value = brand;
            document.querySelector('.editContainer input[placeholder="Note:"]').value = note;
            document.querySelector('.editContainer input[placeholder="Quantity:"]').value = quantity;

            const typeDropdown = document.querySelector('.editContainer select[name="type"]');
            typeDropdown.value = type || 'sport';

            document.querySelector('.editContainer textarea[placeholder="Edit Description:"]').value = description || '';


            // Handle the preview image

            const previewImage = document.getElementById('previewImage');

            // Handle the preview image
            if (image && image.trim()) {
                previewImage.src = image;
                previewImage.style.display = 'block';
            } else {
                // Hide the image element when no image is available
                previewImage.style.display = 'none';
            }

            previewImage.setAttribute('data-id', id);
        }



        // Save Edited Item Function
        function saveItem() {
            const id = document.getElementById('previewImage').getAttribute('data-id');
            const name = document.querySelector('.editContainer input[placeholder="Item Name:"]').value.trim();
            const brand = document.querySelector('.editContainer input[placeholder="Brand:"]').value.trim();
            const note = document.querySelector('.editContainer input[placeholder="Note:"]').value.trim();
            const quantity = document.querySelector('.editContainer input[placeholder="Quantity:"]').value.trim();
            const type = document.querySelector('.editContainer select[name="type"]').value.trim();
            const description = document.querySelector('.editContainer textarea[placeholder="Edit Description:"]').value.trim();

            if (!id || !name || !brand || !quantity || !type || !description) {
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
            formData.append('note', note);
            formData.append('quantity', quantity);
            formData.append('type', type);
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
                            title: 'The item has been updated successfully!',
                            timer: 3000,
                            showConfirmButton: false,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: data.message || 'An error occurred while updating the item.',
                            timer: 3000,
                            showConfirmButton: false,
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again later.',
                        timer: 3000,
                        showConfirmButton: false,
                    });
                });
        }


        // Delete Item Function
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
                        body: JSON.stringify({
                            id: itemId
                        })
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    timer: 3000,
                                    showConfirmButton: false,
                                }).then(() => {
                                    // Reload the page or remove the row from the table
                                    location.reload(); // or remove the row dynamically
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message,
                                    timer: 3000,
                                    showConfirmButton: false,
                                });
                            }
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An unexpected error occurred.',
                                timer: 3000,
                                showConfirmButton: false,
                            });
                        });
                }
            });
        }


        function printTable() {
            const tableContainer = document.querySelector('.tableContainer');
            const rows = tableContainer.querySelectorAll('tr');

            // Hide the last column (Actions)
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = 'none'; // Hide last column
                }
            });

            // Prepare the print content
            const printContent = tableContainer.outerHTML;
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
            th {
                background-color: #f4f4f4;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
        </style>
    </head>
    <body>
        ${printContent}
    </body>
    </html>
    `);
            printWindow.document.close();
            printWindow.print();

            // Restore visibility of the last column
            rows.forEach(row => {
                const cells = row.children;
                if (cells.length > 0) {
                    cells[cells.length - 1].style.display = ''; // Restore last column visibility
                }
            });
        }
    </script>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script src="../assets/js/sidebar.js"></script>
    <script>
        function addProgram() {
            const addProgramButton = document.querySelector('.addContainer');

            if (addProgramButton.style.display === 'none') {
                addProgramButton.style.display = 'block';
            } else {
                addProgramButton.style.display = 'none'
            }
        }

        function editProgram() {
            const editProgramButton = document.querySelector('.editContainer');

            if (editProgramButton.style.display === 'none') {
                editProgramButton.style.display = 'block';
            } else {
                editProgramButton.style.display = 'none'
            }
        }
    </script>
    <!-- <script src="../assets/js/uploadImage.js"></script> -->
</body>

</html>