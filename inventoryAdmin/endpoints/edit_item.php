<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $name = htmlspecialchars(trim($_POST['name']));
    $brand = htmlspecialchars(trim($_POST['brand']));
    $quantity = intval($_POST['quantity']);
    $description = htmlspecialchars(trim($_POST['description']));
    $image = null;

    if (empty($id) || empty($name) || empty($brand) || empty($quantity) || empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required!']);
        exit;
    }

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

    $query = "UPDATE items SET name = ?, description = ?, brand = ?, quantity = ?";
    if ($image) {
        $query .= ", image = ?";
    }
    $query .= " WHERE id = ?";

    $stmt = $conn->prepare($query);
    if ($image) {
        $stmt->bind_param("sssisi", $name, $description, $brand, $quantity, $image, $id);
    } else {
        $stmt->bind_param("sssii", $name, $description, $brand, $quantity, $id);
    }

    if ($stmt->execute()) {
        // Update similar items
        updateSimilarItems($name, $id);

        echo json_encode(['status' => 'success', 'message' => 'Item updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update item.']);
    }

    $stmt->close();
    $conn->close();
}

function updateSimilarItems($newName, $currentItemId)
{
    global $conn;

    // Find items with similar names
    $query = "SELECT id FROM items WHERE name LIKE ? AND id != ?";
    $stmt = $conn->prepare($query);
    $likePattern = "%" . $newName . "%";
    $stmt->bind_param("si", $likePattern, $currentItemId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $updateQuery = "UPDATE items SET name = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $newName, $row['id']);
        $updateStmt->execute();
        $updateStmt->close();
    }

    $stmt->close();
}
?>