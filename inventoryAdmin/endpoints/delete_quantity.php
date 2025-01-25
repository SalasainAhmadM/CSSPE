<?php
require_once '../../conn/conn.php';

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the `item_id` and related `transaction_id` through the `transaction_item_quantities` table
    $fetchDetailsSql = "
        SELECT iq.item_id, tiq.transaction_id, it.quantity_borrowed 
        FROM item_quantities iq
        LEFT JOIN transaction_item_quantities tiq ON iq.id = tiq.item_quantity_id
        LEFT JOIN item_transactions it ON tiq.transaction_id = it.transaction_id
        WHERE iq.id = ?";
    $stmt = $conn->prepare($fetchDetailsSql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($item_id, $transaction_id, $quantity_borrowed);
    $stmt->fetch();
    $stmt->close();

    if ($item_id) {
        // Delete the record from the `item_quantities` table
        $deleteSql = "DELETE FROM item_quantities WHERE id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            // Update the `quantity` and `quantity_origin` in the `items` table
            $updateItemSql = "
                UPDATE items 
                SET quantity = GREATEST(quantity - 1, 0), 
                    quantity_origin = GREATEST(quantity_origin - 1, 0) 
                WHERE id = ?";
            $updateStmt = $conn->prepare($updateItemSql);
            $updateStmt->bind_param('i', $item_id);
            if ($updateStmt->execute()) {
                // If a `transaction_id` exists, update `quantity_borrowed` in `item_transactions`
                if ($transaction_id) {
                    $updateTransactionSql = "
                        UPDATE item_transactions 
                        SET quantity_borrowed = GREATEST(quantity_borrowed - 1, 0) 
                        WHERE transaction_id = ?";
                    $transactionStmt = $conn->prepare($updateTransactionSql);
                    $transactionStmt->bind_param('i', $transaction_id);
                    if ($transactionStmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Record deleted and quantities updated successfully.';
                    } else {
                        $response['message'] = 'Error updating transaction quantities: ' . $conn->error;
                    }
                    $transactionStmt->close();
                } else {
                    $response['success'] = true;
                    $response['message'] = 'Record deleted and item quantities updated successfully.';
                }
            } else {
                $response['message'] = 'Error updating item quantities: ' . $conn->error;
            }
            $updateStmt->close();
        } else {
            $response['message'] = 'Error deleting record: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Item ID not found for the given record.';
    }
} else {
    $response['message'] = 'No record ID provided.';
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>