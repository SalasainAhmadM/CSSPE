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
                    <input class="searchBar" type="text" placeholder="Search...">
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
                        <tbody>
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
                                        <button onclick="editItem(<?php echo $item['id']; ?>)" class="addButton"
                                            style="width: 5rem;">Edit</button>
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


    <form id="addItemForm">
        <div class="addContainer" style="display: none; background-color: none;">
            <div class="addContainer">
                <div class="subAddContainer">
                    <div class="titleContainer">
                        <p>Add Item</p>
                    </div>

                    <div class="sublogoutContainer">
                        <div class="uploadContainer">
                            <div class="subUploadContainer">
                                <div class="displayImage">
                                    <img class="image1" id="previewImage" src="../assets/img/CSSPE.png"
                                        alt="Preview Image"
                                        style="max-width: 100%; height: auto; border: 1px solid red;">
                                </div>
                            </div>
                            <div class="uploadButton">
                                <input id="imageUpload" type="file" accept="image/*" style="display: none;"
                                    onchange="previewImage()">
                                <button type="button" onclick="triggerImageUpload()" class="addButton"
                                    style="height: 2rem; width: 5rem;">Upload</button>
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
                            <textarea id="itemDescription" class="inputEmail" placeholder="Description"
                                required></textarea>
                        </div>

                        <form id="addItemForm">
                            <div class="inputContainer"
                                style="gap: 0.5rem; justify-content: right; padding-right: 0.9rem;">
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
                                <img class="image1" id="previewImage" src="" alt="Preview Image"
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
        function triggerImageUpload() {
            document.getElementById('imageUpload').click();
        }

        function previewImage() {
            const fileInput = document.getElementById('imageUpload');
            const previewImage = document.getElementById('previewImage');
            const file = fileInput.files[0]; // Get the first selected file

            if (file) {
                const reader = new FileReader(); // Initialize FileReader
                reader.onload = function (e) {
                    previewImage.src = e.target.result; // Set the image src to the file's data URL
                };
                reader.readAsDataURL(file); // Read the file data
            } else {
                previewImage.src = ""; // Clear the preview if no file is selected
            }
        }


        document.getElementById('addItemForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('name', document.getElementById('itemName').value.trim());
            formData.append('brand', document.getElementById('itemBrand').value.trim());
            formData.append('quantity', document.getElementById('itemQuantity').value);
            formData.append('description', document.getElementById('itemDescription').value.trim());

            const imageUpload = document.getElementById('imageUpload');
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
                        document.getElementById('previewImage').src = ""; // Reset image preview
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

        function cancelForm() {
            document.getElementById('addItemForm').reset();
            document.getElementById('previewImage').src = ""; // Clear preview
        }

        function triggerImageUpload() {
            document.getElementById('imageUpload').click();
        }

        function previewImage() {
            const fileInput = document.getElementById('imageUpload');
            const previewImage = document.getElementById('previewImage');
            const file = fileInput.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                previewImage.src = ""; // Clear the preview
            }
        }

        function editItem(itemId) {
            fetch(`./endpoints/get_item.php?id=${itemId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.editContainer').style.display = 'block';
                        document.querySelector('.editContainer input[placeholder="Item Name:"]').value = data.item.name;
                        document.querySelector('.editContainer input[placeholder="Brand:"]').value = data.item.brand;
                        document.querySelector('.editContainer input[placeholder="Quantity:"]').value = data.item.quantity;
                        document.querySelector('.editContainer textarea[placeholder="Description"]').value = data.item.description;
                        document.querySelector('#previewImage').src = `../assets/uploads/${data.item.image || 'default.jpg'}`;

                        // Set the Save button to call saveItem with the correct itemId
                        document.querySelector('.addButton').setAttribute('onclick', `saveItem(${itemId})`);
                    } else {
                        alert('Failed to load item data.');
                    }
                })
                .catch(error => console.error('Error fetching item:', error));
        }


        function saveItem(itemId) {
            const formData = new FormData();
            formData.append('id', itemId);
            formData.append('name', document.querySelector('.editContainer input[placeholder="Item Name:"]').value);
            formData.append('brand', document.querySelector('.editContainer input[placeholder="Brand:"]').value);
            formData.append('quantity', document.querySelector('.editContainer input[placeholder="Quantity:"]').value);
            formData.append('description', document.querySelector('.editContainer textarea[placeholder="Description"]').value);

            const imageFile = document.querySelector('#imageUpload').files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }

            fetch('./endpoints/edit_item.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success', 'Item updated successfully!', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', 'Failed to update item.', 'error');
                    }
                })
                .catch(error => console.error('Error saving item:', error));
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