<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $transactionId = intval($_GET['id']);

    $query = "SELECT t.*, 
             i.name AS item_name, 
             i.brand, 
             u.first_name, 
             u.last_name,
             DATE(t.borrowed_at) AS borrow_date,
             t.return_date,
             t.class_date
      FROM item_transactions t
      INNER JOIN items i ON t.item_id = i.id
      INNER JOIN users u ON t.users_id = u.id
      WHERE t.transaction_id = ?";


    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transactionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>