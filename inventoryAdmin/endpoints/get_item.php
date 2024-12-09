<?php
require_once '../../conn/conn.php';

$response = ['success' => false];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT id, name, description, brand, quantity, image FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($item = $result->fetch_assoc()) {
        $response['success'] = true;
        $response['item'] = $item;
    }
}

echo json_encode($response);
?>