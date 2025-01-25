<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $name = htmlspecialchars(trim($_POST['name']));
    $brand = htmlspecialchars(trim($_POST['brand']));
    $note = htmlspecialchars(trim($_POST['note']));
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

    // Handle item_quantities adjustments
    if ($quantityChange > 0) {
        // Add new `item_quantities` rows
        $stmtInsert = $conn->prepare("INSERT INTO item_quantities (item_id, unique_id) VALUES (?, ?)");
        for ($i = 0; $i < $quantityChange; $i++) {
            $uniqueId = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $stmtInsert->bind_param("is", $id, $uniqueId);
            $stmtInsert->execute();
        }
        $stmtInsert->close();
    } elseif ($quantityChange < 0) {
        // Remove excess `item_quantities` rows
        $quantityToDelete = abs($quantityChange);

        // Fetch IDs to delete
        $stmtSelect = $conn->prepare("SELECT id FROM item_quantities WHERE item_id = ? ORDER BY id DESC LIMIT ?");
        $stmtSelect->bind_param("ii", $id, $quantityToDelete);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        $idsToDelete = [];
        while ($row = $result->fetch_assoc()) {
            $idsToDelete[] = $row['id'];
        }
        $stmtSelect->close();

        if (!empty($idsToDelete)) {
            $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
            $stmtDelete = $conn->prepare("DELETE FROM item_quantities WHERE id IN ($placeholders)");
            $stmtDelete->bind_param(str_repeat('i', count($idsToDelete)), ...$idsToDelete);
            $stmtDelete->execute();
            $stmtDelete->close();
        }
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

    // Update `items` table
    $query = "UPDATE items SET name = ?, type = ?, note = ?, description = ?, brand = ?, quantity = ?, quantity_origin = ?";
    if ($image) {
        $query .= ", image = ?";
    }
    $query .= " WHERE id = ?";

    $stmt = $conn->prepare($query);
    if ($image) {
        $stmt->bind_param("sssssissi", $name, $type, $note, $description, $brand, $quantity, $newQuantityOrigin, $image, $id);
    } else {
        $stmt->bind_param("sssssiii", $name, $type, $note, $description, $brand, $quantity, $newQuantityOrigin, $id);
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