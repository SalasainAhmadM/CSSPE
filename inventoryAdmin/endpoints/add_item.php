<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');
$inventoryAdminId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $note = trim($_POST['note']);
    $description = trim($_POST['description']);
    $brands = $_POST['brands'];
    $quantities = $_POST['quantities'];
    $image = null;

    if (empty($name) || empty($description) || empty($brands) || empty($quantities)) {
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
    $query = "INSERT INTO items (name, description, type, note, users_id, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssis", $name, $description, $type, $note, $inventoryAdminId, $image);

    if ($stmt->execute()) {
        $itemId = $stmt->insert_id;

        // Insert brands and quantities
        foreach ($brands as $index => $brand) {
            $quantity = $quantities[$index];

            // Insert brand into `brands` table
            $brandQuery = "INSERT INTO brands (name, quantity, origin_quantity, item_id) VALUES (?, ?, ?, ?)";
            $brandStmt = $conn->prepare($brandQuery);
            $brandStmt->bind_param("sssi", $brand, $quantity, $quantity, $itemId);
            $brandStmt->execute();
            $brandId = $brandStmt->insert_id;
            $brandStmt->close();

            // Fetch the last unique_id for the given item_id
            $lastUniqueIdQuery = "SELECT unique_id FROM item_quantities ORDER BY id DESC LIMIT 1";
            $lastUniqueIdStmt = $conn->prepare($lastUniqueIdQuery);
            $lastUniqueIdStmt->execute();
            $lastUniqueIdStmt->bind_result($lastUniqueId);
            $lastUniqueIdStmt->fetch();
            $lastUniqueIdStmt->close();

            $datePart = date('dmy');
            $incrementalNumber = 1;

            if ($lastUniqueId) {
                $lastIncrementalPart = intval(substr($lastUniqueId, -3));
                $incrementalNumber = $lastIncrementalPart + 1;
            }

            $stmtQuantities = $conn->prepare("INSERT INTO item_quantities (item_id, unique_id, brand_id) VALUES (?, ?, ?)");

            for ($i = 0; $i < $quantity; $i++) {
                $uniqueId = $datePart . str_pad($incrementalNumber, 3, '0', STR_PAD_LEFT);
                $stmtQuantities->bind_param("iss", $itemId, $uniqueId, $brandId);
                $stmtQuantities->execute();
                $incrementalNumber++;
            }

            $stmtQuantities->close();
        }

        echo json_encode(['status' => 'success', 'message' => 'Item and quantities added successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add item.']);
    }

    $stmt->close();
    $conn->close();
}
