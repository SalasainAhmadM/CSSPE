<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $quantity = intval($_POST['quantity']);
    $description = trim($_POST['description']);
    $image = null;

    // Validate input fields
    if (empty($id) || empty($name) || empty($brand) || empty($quantity) || empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required!']);
        exit;
    }

    // Check if a new image is uploaded
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

    // Update query
    $query = "UPDATE items SET name = ?, description = ?, brand = ?, quantity = ?" .
        ($image ? ", image = ?" : "") .
        " WHERE id = ?";

    // Prepare and bind parameters
    if ($image) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssisi", $name, $description, $brand, $quantity, $image, $id);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssii", $name, $description, $brand, $quantity, $id);
    }

    // Execute and respond
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Item updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update item.']);
    }

    $stmt->close();
    $conn->close();
}
?>