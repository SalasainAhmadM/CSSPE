<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $transactionId = $data['transactionId'];
    $replacedItems = $data['replacedItems'];

    foreach ($replacedItems as $itemId) {
        $query = "INSERT INTO returned_items (transaction_id, item_id, quantity_returned, status, remarks) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $quantityReturned = 1; // Each replaced item is counted as one
        $status = 'Replaced';
        $remarks = 'Item replaced successfully';

        $stmt->bind_param("iiiss", $transactionId, $itemId, $quantityReturned, $status, $remarks);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false, "message" => "Error tracking replaced item: " . $stmt->error]);
            exit;
        }
    }

    echo json_encode(["success" => true, "message" => "Replaced items tracked successfully."]);
}
?>