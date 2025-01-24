<?php
require_once '../../conn/conn.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['transaction_id']) || !isset($data['status_remark'])) {
        throw new Exception('Invalid input.');
    }

    $transaction_id = $data['transaction_id'];
    $status_remark = $data['status_remark'];

    $query = "UPDATE item_transactions SET status_remark = ? WHERE transaction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $status_remark, $transaction_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status remark updated successfully.']);
    } else {
        throw new Exception('Failed to update status remark.');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>