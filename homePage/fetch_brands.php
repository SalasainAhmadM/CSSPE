<?php
require_once '../conn/conn.php';

if (isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);

    $query = "SELECT b.id, b.name, b.quantity 
              FROM brands b
              WHERE b.item_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $brands = [];
    while ($row = $result->fetch_assoc()) {
        $brands[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'quantity' => $row['quantity']
        ];
    }

    echo json_encode($brands);
} else {
    echo json_encode([]);
}
?>