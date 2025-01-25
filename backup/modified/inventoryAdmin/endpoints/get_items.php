<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT id, name, brand, quantity FROM items";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $items]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No items found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>