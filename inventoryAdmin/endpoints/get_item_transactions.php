<?php
require_once '../../conn/conn.php';

$sql = "
    SELECT 
        t.transaction_id, 
        i.name AS item_name, 
        i.id AS item_id, 
        b.name AS item_brand,  
        t.quantity_borrowed, 
        t.class_date, 
        t.schedule_from, 
        t.schedule_to, 
        t.borrowed_at, 
        t.return_date, 
        u.first_name, 
        u.last_name, 
        u.contact_no, 
        u.email, 
        t.status_remark,
        GROUP_CONCAT(iq.unique_id SEPARATOR ', ') AS unique_ids
    FROM item_transactions t
    INNER JOIN items i ON t.item_id = i.id
    INNER JOIN brands b ON t.brand_id = b.id  -- Join brands table to fetch brand name
    INNER JOIN users u ON t.users_id = u.id
    LEFT JOIN transaction_item_quantities tiq ON t.transaction_id = tiq.transaction_id
    LEFT JOIN item_quantities iq ON tiq.item_quantity_id = iq.id
    WHERE t.status = 'Pending'
    GROUP BY t.transaction_id
";

$result = $conn->query($sql);

$response = [];
if ($result && $result->num_rows > 0) {
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $response['status'] = 'success';
    $response['data'] = $transactions;
} else {
    $response['status'] = 'error';
    $response['message'] = 'No transactions found';
}

header('Content-Type: application/json');
echo json_encode($response);
?>