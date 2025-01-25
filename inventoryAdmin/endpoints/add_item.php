<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $quantity = intval($_POST['quantity']);
    $type = trim($_POST['type']);
    $note = trim($_POST['note']);
    $description = trim($_POST['description']);
    $image = null;

    if (empty($name) || empty($brand) || empty($quantity) || empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required!']);
        exit;
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../../assets/uploads/';
        $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid image format!']);
            exit;
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $fileName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
            exit;
        }
    }

    // Insert item into `items` table
    $query = "INSERT INTO items (name, description, brand, quantity, type, note, quantity_origin, users_id, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssis", $name, $description, $brand, $quantity, $type, $note, $quantity, $inventoryAdminId, $image);

    if ($stmt->execute()) {
        $itemId = $stmt->insert_id; // Get the last inserted ID for the `items` table

        // Generate 6-digit unique IDs for each quantity and insert into `item_quantities`
        $queryQuantities = "INSERT INTO item_quantities (item_id, unique_id) VALUES (?, ?)";
        $stmtQuantities = $conn->prepare($queryQuantities);

        for ($i = 0; $i < $quantity; $i++) {
            $uniqueId = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Ensure the unique ID does not already exist in the database
            $checkQuery = "SELECT COUNT(*) FROM item_quantities WHERE unique_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $uniqueId);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($count > 0) {
                $i--; // Retry this iteration with a new ID
                continue;
            }

            $stmtQuantities->bind_param("is", $itemId, $uniqueId);
            $stmtQuantities->execute();
        }

        $stmtQuantities->close();

        echo json_encode(['status' => 'success', 'message' => 'Item and quantities added successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add item.']);
    }

    $stmt->close();
    $conn->close();
}
?>