<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id']) || empty($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid item ID!']);
        exit;
    }

    $itemId = intval($data['id']);

    // Check if the item exists
    $query = "SELECT image FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Item not found!']);
        $stmt->close();
        $conn->close();
        exit;
    }

    $item = $result->fetch_assoc();
    $imagePath = '../../assets/uploads/' . $item['image'];

    // Delete the item
    $deleteQuery = "DELETE FROM items WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $itemId);

    if ($deleteStmt->execute()) {
        // Remove the image if it exists
        if (!empty($item['image']) && file_exists($imagePath)) {
            unlink($imagePath);
        }
        echo json_encode(['status' => 'success', 'message' => 'Item deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete item.']);
    }

    $deleteStmt->close();
    $stmt->close();
    $conn->close();
}
?>