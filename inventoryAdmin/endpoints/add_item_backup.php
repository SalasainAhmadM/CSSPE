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
    $query = "INSERT INTO items (name, description, brand, type, note, users_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssis", $name, $description, $type, $note, $inventoryAdminId, $image);

    if ($stmt->execute()) {
        $itemId = $stmt->insert_id;

        $datePart = date('dmy');
        $queryQuantities = "INSERT INTO item_quantities (item_id, unique_id) VALUES (?, ?)";
        $stmtQuantities = $conn->prepare($queryQuantities);

        $lastUniqueIdQuery = "SELECT unique_id FROM item_quantities WHERE item_id = ? ORDER BY unique_id DESC LIMIT 1";
        $lastUniqueIdStmt = $conn->prepare($lastUniqueIdQuery);
        $lastUniqueIdStmt->bind_param("i", $itemId);
        $lastUniqueIdStmt->execute();
        $lastUniqueIdStmt->bind_result($lastUniqueId);
        $lastUniqueIdStmt->fetch();
        $lastUniqueIdStmt->close();

        $incrementalNumber = 1;
        if ($lastUniqueId) {
            $lastIncrementalPart = intval(substr($lastUniqueId, -3));
            $incrementalNumber = $lastIncrementalPart + 1;
        }

        for ($i = 0; $i < $quantity; $i++) {

            $uniqueId = $datePart . str_pad($incrementalNumber, 3, '0', STR_PAD_LEFT);

            $stmtQuantities->bind_param("is", $itemId, $uniqueId);
            $stmtQuantities->execute();

            $incrementalNumber++;
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