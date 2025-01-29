<?php
header('Content-Type: application/json');
require_once '../../conn/conn.php';

try {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['transaction_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Transaction ID is required.']);
        exit;
    }

    // Extract input data
    $transaction_id = intval($data['transaction_id']);
    $return_quantity = intval($data['return_quantity']);
    $damaged = intval($data['damaged']);
    $lost = intval($data['lost']);
    $replaced = intval($data['replaced']);

    // Check if all quantities are zero
    if ($return_quantity <= 0 && $damaged <= 0 && $lost <= 0 && $replaced <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'No valid quantities provided.']);
        exit;
    }

    // Fetch transaction details
    $query = "SELECT item_id, quantity_borrowed, quantity_returned FROM item_transactions WHERE transaction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();

    if (!$transaction) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid transaction ID.']);
        exit;
    }

    $item_id = intval($transaction['item_id']);
    $quantity_borrowed = intval($transaction['quantity_borrowed']);
    $quantity_returned = intval($transaction['quantity_returned'] ?? 0);

    // Ensure return quantity doesn't exceed remaining borrowed quantity
    if ($return_quantity > ($quantity_borrowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Return quantity exceeds remaining borrowed quantity.']);
        exit;
    }

    // Calculate updated quantities
    $new_quantity_returned = $quantity_returned + $return_quantity;
    $new_quantity_borrowed = $quantity_borrowed - $return_quantity;

    // Determine the new status
    $new_status = ($new_quantity_borrowed === 0) ? 'Returned' : 'Approved';

    // Update the transaction
    $update_query = "
        UPDATE item_transactions
        SET quantity_borrowed = ?, 
            quantity_returned = ?, 
            returned_at = NOW(),
            status = ?
        WHERE transaction_id = ?
    ";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('iisi', $new_quantity_borrowed, $new_quantity_returned, $new_status, $transaction_id);
    $stmt->execute();

    // Update items table quantity
    $update_items_query = "
        UPDATE items
        SET quantity = quantity + ?
        WHERE id = ?
    ";
    $stmt = $conn->prepare($update_items_query);
    $stmt->bind_param('ii', $return_quantity, $item_id);
    $stmt->execute();

    // Insert records into returned_items table with unique_id_remark
    $statuses = [
        'Damaged' => $data['damaged_ids'] ?? [],
        'Lost' => $data['lost_ids'] ?? [],
        'Replaced' => $data['replaced_ids'] ?? []
    ];

    $all_non_good_ids = array_merge($statuses['Damaged'], $statuses['Lost'], $statuses['Replaced']);
    $good_ids = array_diff($data['good_ids'] ?? [], $all_non_good_ids); // Exclude already used IDs

    $statuses['Good'] = $good_ids;

    $insert_query = "
        INSERT INTO returned_items (transaction_id, item_id, quantity_returned, status, unique_id_remark, remarks)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insert_query);

    foreach ($statuses as $status => $ids) {
        foreach ($ids as $item_quantity_id) {
            // Fetch the unique ID for this item_quantity_id
            $unique_id_query = "SELECT unique_id FROM item_quantities WHERE id = ?";
            $unique_id_stmt = $conn->prepare($unique_id_query);
            $unique_id_stmt->bind_param('i', $item_quantity_id);
            $unique_id_stmt->execute();
            $unique_id_result = $unique_id_stmt->get_result();
            $unique_id_row = $unique_id_result->fetch_assoc();

            if ($unique_id_row) {
                $unique_id = $unique_id_row['unique_id'];
                $remarks = ($status === 'Good') ? '' : $status; // Remarks are empty for 'Good' status
                $single_quantity = 1; // Always insert quantity as 1

                // Insert into returned_items with the unique ID and status
                $stmt->bind_param('iiisss', $transaction_id, $item_id, $single_quantity, $status, $unique_id, $remarks);
                $stmt->execute();
            }
        }
    }

    // Insert into item_status_tracking table for damaged, lost, or replaced items
    $item_status_quantities = [
        'Damaged' => $data['damaged_ids'] ?? [],
        'Lost' => $data['lost_ids'] ?? [],
        'Replaced' => $data['replaced_ids'] ?? []
    ];

    $insert_tracking_query = "
        INSERT INTO item_status_tracking (item_quantity_id, remarks)
        VALUES (?, ?)
    ";
    $stmt = $conn->prepare($insert_tracking_query);

    foreach ($item_status_quantities as $status => $ids) {
        foreach ($ids as $item_quantity_id) {
            $remarks = $status;
            $stmt->bind_param('is', $item_quantity_id, $remarks);
            $stmt->execute();
        }
    }

    // Call the function to delete matching records from transaction_item_quantities
    deleteTransactionItemQuantities($conn, $transaction_id);

    echo json_encode(['status' => 'success', 'message' => 'Return recorded successfully.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

/**
 * Deletes matching records from the transaction_item_quantities table.
 *
 * @param mysqli $conn
 * @param int $transaction_id
 */
function deleteTransactionItemQuantities($conn, $transaction_id)
{
    try {
        $delete_query = "DELETE FROM transaction_item_quantities WHERE transaction_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('i', $transaction_id);
        $stmt->execute();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete records: ' . $e->getMessage()]);
        exit;
    }
}
?>