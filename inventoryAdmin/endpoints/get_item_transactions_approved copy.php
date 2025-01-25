<?php
require_once '../../conn/conn.php';

header('Content-Type: application/json');

try {
    $query = "
        SELECT 
            it.transaction_id, 
            i.name AS item_name, 
            i.brand AS item_brand, 
            it.quantity_borrowed, 
            it.schedule_from, 
            it.schedule_to, 
            it.returned_at, 
            it.borrowed_at, 
            it.class_date,  
            it.return_date, 
            u.first_name, 
            u.last_name, 
            u.contact_no, 
            u.email,
            it.status_remark, 
            it.status 
        FROM item_transactions it
        JOIN items i ON it.item_id = i.id
        JOIN users u ON it.users_id = u.id
        WHERE it.status = 'Approved'
        ORDER BY it.borrowed_at DESC";

    $result = $conn->query($query);
    $transactions = [];

    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $transactions]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch transactions.']);
}
?>