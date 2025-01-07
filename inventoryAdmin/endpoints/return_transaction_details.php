<?php
require_once '../../conn/conn.php';

if (isset($_GET['transaction_id'])) {
    $transaction_id = intval($_GET['transaction_id']);

    $query = "SELECT t.transaction_id, i.name AS item_name, i.brand AS item_brand, 
                     t.quantity_borrowed, t.borrowed_at, u.first_name, u.last_name 
              FROM item_transactions t
              JOIN items i ON t.item_id = i.id
              JOIN users u ON t.users_id = u.id
              WHERE t.transaction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'success', 'data' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Transaction ID is required.']);
}
?>