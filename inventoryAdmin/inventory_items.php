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

// Validate and fetch the item ID from the URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$itemId) {
    die("Invalid item ID.");
}
// Fetch the item name based on the item ID
$itemQuery = "SELECT name FROM items WHERE id = ?";
$stmt = $conn->prepare($itemQuery);
$stmt->bind_param("i", $itemId);
$stmt->execute();
$itemResult = $stmt->get_result();

if ($itemResult->num_rows > 0) {
    $itemRow = $itemResult->fetch_assoc();
    $itemName = $itemRow['name'];
} else {
    die("Item not found.");
}

$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of records for pagination specific to the item
$totalQuery = "SELECT COUNT(*) AS total FROM item_quantities WHERE item_id = ?";
$stmt = $conn->prepare($totalQuery);
$stmt->bind_param("i", $itemId);
$stmt->execute();
$totalResult = $stmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated records with status
$sql = "
    SELECT 
        iq.id, 
        iq.unique_id, 
        COALESCE(it.status, 'Available') AS status
    FROM item_quantities iq
    LEFT JOIN transaction_item_quantities tiq ON iq.id = tiq.item_quantity_id
    LEFT JOIN item_transactions it ON tiq.transaction_id = it.transaction_id
    WHERE iq.item_id = ?
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $itemId, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all item IDs and their unique IDs
$itemQuantities = [];
while ($row = $result->fetch_assoc()) {
    $itemQuantities[$row['unique_id']] = [
        'id' => $row['id'],
        'unique_id' => $row['unique_id'],
        'status' => $row['status'] // Default status from item_transactions
    ];
}

// Fetch lost or damaged statuses from returned_items
$uniqueIds = array_keys($itemQuantities);
if (!empty($uniqueIds)) {
    $placeholders = implode(',', array_fill(0, count($uniqueIds), '?'));
    $statusQuery = "
        SELECT unique_id_remark, status 
        FROM returned_items 
        WHERE unique_id_remark IN ($placeholders) AND status IN ('Lost', 'Damaged')
    ";

    $stmt = $conn->prepare($statusQuery);
    $stmt->bind_param(str_repeat('s', count($uniqueIds)), ...$uniqueIds);
    $stmt->execute();
    $statusResult = $stmt->get_result();

    while ($statusRow = $statusResult->fetch_assoc()) {
        $uniqueId = $statusRow['unique_id_remark'];
        if (isset($itemQuantities[$uniqueId])) {
            $itemQuantities[$uniqueId]['status'] = $statusRow['status']; // Override with Lost or Damaged
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                    <p class="text">Item Quantities for: <?php echo htmlspecialchars($itemName); ?></p>
                </div>

                <div class="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Unique ID</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (!empty($itemQuantities)): ?>
                                <?php foreach ($itemQuantities as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['unique_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td>
                                            <button class="addButton"
                                                onclick="deleteQuantity(<?php echo $row['id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?id=<?php echo $itemId; ?>&page=<?php echo $page - 1; ?>" class="prev">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?id=<?php echo $itemId; ?>&page=<?php echo $i; ?>"
                            class="<?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?id=<?php echo $itemId; ?>&page=<?php echo $page + 1; ?>" class="next">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>




    <!-- Add this script at the bottom of your HTML or in a separate JS file -->
    <script>
        function deleteQuantity(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Make an AJAX request to delete the record
                    fetch(`./endpoints/delete_quantity.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    timer: 3000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload(); // Reload the page after success
                                });
                                document.querySelector(`tr[data-id="${id}"]`).remove();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message
                                });
                            }
                        });
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