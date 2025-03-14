<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $quantity_borrowed = $_POST['quantity_borrowed'];

    $conn->begin_transaction();

    try {
        $getItemQuery = "SELECT item_id, brand_id FROM item_transactions WHERE transaction_id = ?";
        $getItemStmt = $conn->prepare($getItemQuery);

        if (!$getItemStmt) {
            throw new Exception("Failed to prepare statement for fetching item and brand ID: " . $conn->error);
        }

        $getItemStmt->bind_param("i", $transaction_id);
        if (!$getItemStmt->execute()) {
            throw new Exception("Failed to execute query to fetch item and brand ID: " . $getItemStmt->error);
        }

        $result = $getItemStmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("No item found for the provided transaction ID.");
        }

        $item = $result->fetch_assoc();
        $item_id = $item['item_id'];
        $brand_id = $item['brand_id'];

        $updateBrandQuery = "UPDATE brands SET quantity = quantity + ? WHERE id = ?";
        $updateBrandStmt = $conn->prepare($updateBrandQuery);

        if (!$updateBrandStmt) {
            throw new Exception("Failed to prepare brand quantity update statement: " . $conn->error);
        }

        $updateBrandStmt->bind_param("ii", $quantity_borrowed, $brand_id);
        if (!$updateBrandStmt->execute()) {
            throw new Exception("Failed to execute brand quantity update: " . $updateBrandStmt->error);
        }

        // Delete transaction from item_transactions
        $deleteTransactionQuery = "DELETE FROM item_transactions WHERE transaction_id = ?";
        $deleteTransactionStmt = $conn->prepare($deleteTransactionQuery);

        if (!$deleteTransactionStmt) {
            throw new Exception("Failed to prepare transaction deletion statement: " . $conn->error);
        }

        $deleteTransactionStmt->bind_param("i", $transaction_id);
        if (!$deleteTransactionStmt->execute()) {
            throw new Exception("Failed to execute transaction deletion: " . $deleteTransactionStmt->error);
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Transaction declined and quantity returned to the brand successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>