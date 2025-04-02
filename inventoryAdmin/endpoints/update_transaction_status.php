<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = intval($_POST['transaction_id']);
    $status = $_POST['status'];
    $status_remark = isset($_POST['status_remark']) ? trim($_POST['status_remark']) : '';
    $notif = '0';

    if (empty($transaction_id) || !in_array($status, ['Approved', 'Declined'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit;
    }

    if (empty($status_remark)) {
        $status_remark = 'N/A';
    }

    $query = "UPDATE item_transactions SET status = ?, notif = ?, status_remark = ? WHERE transaction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sisi', $status, $notif, $status_remark, $transaction_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Transaction updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update transaction.']);
    }

    $stmt->close();
    $conn->close();
}
?>