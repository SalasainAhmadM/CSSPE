<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $transactionId = $data['transactionId'];
    $damagedItems = $data['damagedItems'];

    foreach ($damagedItems as $itemId) {
        $query = "INSERT INTO returned_items (transaction_id, item_id, quantity_returned, status, remarks) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $quantityReturned = 1; // Each damaged item is counted as one
        $status = 'Damaged';
        $remarks = 'Item marked as damaged';

        $stmt->bind_param("iiiss", $transactionId, $itemId, $quantityReturned, $status, $remarks);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false, "message" => "Error tracking damaged item: " . $stmt->error]);
            exit;
        }
    }

    echo json_encode(["success" => true, "message" => "Damaged items tracked successfully."]);
}
?>