<?php
require_once '../../conn/conn.php';

if (isset($_GET['transaction_id'])) {
    $transaction_id = intval($_GET['transaction_id']);

    // Query to fetch transaction details and associated unique IDs
    $query = "
    SELECT 
        t.transaction_id, 
        i.name AS item_name, 
        i.brand AS item_brand, 
        t.quantity_borrowed, 
        t.borrowed_at, 
        u.first_name, 
        u.last_name,
        GROUP_CONCAT(CONCAT(iq.id, ':', iq.unique_id)) AS unique_ids
    FROM item_transactions t
    JOIN items i ON t.item_id = i.id
    JOIN users u ON t.users_id = u.id
    JOIN transaction_item_quantities tiq ON t.transaction_id = tiq.transaction_id
    JOIN item_quantities iq ON tiq.item_quantity_id = iq.id
    WHERE t.transaction_id = ?
    GROUP BY t.transaction_id
";


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