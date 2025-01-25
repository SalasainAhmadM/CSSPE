<?php
require_once '../../conn/conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Notification ID is required.']);
        exit;
    }

    $notifId = intval($data['id']);

    $query = "DELETE FROM notif_items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $notifId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Notification deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete notification.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>