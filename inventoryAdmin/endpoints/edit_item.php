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
    $type = htmlspecialchars(trim($_POST['type']));
    $description = htmlspecialchars(trim($_POST['description']));
    $image = null;

    if (empty($id) || empty($name) || empty($brand) || empty($quantity) || empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required!']);
        exit;
    }

    // Fetch current quantity
    $stmt = $conn->prepare("SELECT quantity, quantity_origin FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if (!$item) {
        echo json_encode(['status' => 'error', 'message' => 'Item not found!']);
        exit;
    }

    $currentQuantity = intval($item['quantity']);
    $quantityOrigin = intval($item['quantity_origin']);

    // Calculate quantity_origin update
    $quantityChange = $quantity - $currentQuantity;
    $newQuantityOrigin = $quantityOrigin + $quantityChange;

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

    $query = "UPDATE items SET name = ?, type = ?, description = ?, brand = ?, quantity = ?, quantity_origin = ?";
    if ($image) {
        $query .= ", image = ?";
    }
    $query .= " WHERE id = ?";

    $stmt = $conn->prepare($query);
    if ($image) {
        $stmt->bind_param("ssssissi", $name, $type, $description, $brand, $quantity, $newQuantityOrigin, $image, $id);
    } else {
        $stmt->bind_param("ssssiii", $name, $type, $description, $brand, $quantity, $newQuantityOrigin, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Item updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update item.']);
    }

    $stmt->close();
    $conn->close();
}
?>