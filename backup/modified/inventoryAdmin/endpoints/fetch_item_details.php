<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);

    $query = "SELECT brand, quantity FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $item]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>