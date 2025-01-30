<?php
require_once '../../conn/conn.php';

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['return_id'], $data['new_remarks'], $data['new_status'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    exit;
}

$return_id = $conn->real_escape_string($data['return_id']);
$new_remarks = $conn->real_escape_string($data['new_remarks']);
$new_status = $conn->real_escape_string($data['new_status']);

// Start transaction
$conn->begin_transaction();

try {
    // Fetch item_id from returned_items table
    $fetchItemQuery = "SELECT item_id FROM returned_items WHERE return_id = '$return_id'";
    $itemResult = $conn->query($fetchItemQuery);

    if ($itemResult->num_rows > 0) {
        $itemRow = $itemResult->fetch_assoc();
        $item_id = $itemRow['item_id'];

        // Update the returned_items table
        $updateQuery = "UPDATE returned_items 
                        SET status = '$new_status', remarks = '$new_remarks' 
                        WHERE return_id = '$return_id'";
        $conn->query($updateQuery);

        // Increase item quantity by 1 in the items table
        $updateItemQuery = "UPDATE items SET quantity = quantity + 1 WHERE id = '$item_id'";
        $conn->query($updateItemQuery);

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Item status updated and quantity increased.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed to update item status: ' . $e->getMessage()]);
}

$conn->close();
?>