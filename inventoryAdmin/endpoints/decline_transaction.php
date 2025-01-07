<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $quantity_borrowed = $_POST['quantity_borrowed'];

    $conn->begin_transaction();

    try {
        // Fetch item_id using transaction_id
        $getItemIdQuery = "SELECT item_id FROM item_transactions WHERE transaction_id = ?";
        $getItemIdStmt = $conn->prepare($getItemIdQuery);

        if (!$getItemIdStmt) {
            throw new Exception("Failed to prepare statement for fetching item ID: " . $conn->error);
        }

        $getItemIdStmt->bind_param("i", $transaction_id);
        if (!$getItemIdStmt->execute()) {
            throw new Exception("Failed to execute query to fetch item ID: " . $getItemIdStmt->error);
        }

        $result = $getItemIdStmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("No item found for the provided transaction ID.");
        }

        $item = $result->fetch_assoc();
        $item_id = $item['item_id'];

        // Update item quantity
        $updateItemQuery = "UPDATE items SET quantity = quantity + ? WHERE id = ?";
        $updateItemStmt = $conn->prepare($updateItemQuery);

        $updateItemStmt->bind_param("ii", $quantity_borrowed, $item_id);
        if (!$updateItemStmt->execute()) {
            throw new Exception("Failed to execute item quantity update: " . $updateItemStmt->error);
        }

        // Delete transaction
        $deleteTransactionQuery = "DELETE FROM item_transactions WHERE transaction_id = ?";
        $deleteTransactionStmt = $conn->prepare($deleteTransactionQuery);

        $deleteTransactionStmt->bind_param("i", $transaction_id);
        if (!$deleteTransactionStmt->execute()) {
            throw new Exception("Failed to execute transaction deletion: " . $deleteTransactionStmt->error);
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Transaction declined and item quantity updated successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>