<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = intval($_POST['transaction_id']);
    $status = $_POST['status'];

    if (empty($transaction_id) || !in_array($status, ['Approved', 'Declined'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    $query = "UPDATE item_transactions SET status = ? WHERE transaction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $status, $transaction_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Transaction updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update transaction.']);
    }

    $stmt->close();
    $conn->close();
}
?>