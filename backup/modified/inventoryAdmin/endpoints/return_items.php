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

    // Insert records into returned_items table
    $statuses = [
        'Good' => $return_quantity,
        'Damaged' => $damaged,
        'Lost' => $lost,
        'Replaced' => $replaced
    ];

    $insert_query = "
        INSERT INTO returned_items (transaction_id, item_id, quantity_returned, status, remarks)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insert_query);

    foreach ($statuses as $status => $quantity) {
        if ($quantity > 0) {
            $remarks = ''; // Add logic to handle remarks if necessary
            $stmt->bind_param('iiiss', $transaction_id, $item_id, $quantity, $status, $remarks);
            $stmt->execute();
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Return recorded successfully.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>