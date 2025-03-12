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

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete brands associated with this item
        $deleteBrandsQuery = "DELETE FROM brands WHERE item_id = ?";
        $deleteBrandsStmt = $conn->prepare($deleteBrandsQuery);
        $deleteBrandsStmt->bind_param("i", $itemId);
        $deleteBrandsStmt->execute();
        $deleteBrandsStmt->close();

        // Delete the item
        $deleteItemQuery = "DELETE FROM items WHERE id = ?";
        $deleteItemStmt = $conn->prepare($deleteItemQuery);
        $deleteItemStmt->bind_param("i", $itemId);
        $deleteItemStmt->execute();
        $deleteItemStmt->close();

        // Commit transaction
        $conn->commit();

        // Remove the image if it exists
        if (!empty($item['image']) && file_exists($imagePath)) {
            unlink($imagePath);
        }

        echo json_encode(['status' => 'success', 'message' => 'Item and associated brands deleted successfully!']);
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction if any error occurs
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete item and brands.']);
    }

    $stmt->close();
    $conn->close();
}
?>