<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);

    // Fetch brand and quantity from the brands table
    $query = "SELECT b.id, b.name AS brand, b.quantity 
              FROM brands b
              WHERE b.item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $brands = [];
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $brands]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No brands found for this item.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>