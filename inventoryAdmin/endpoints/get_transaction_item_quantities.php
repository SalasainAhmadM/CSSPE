<?php
header('Content-Type: application/json');
require_once '../../conn/conn.php'; // Include your database connection file

// Check if transaction_id is provided
if (!isset($_GET['transaction_id']) || empty($_GET['transaction_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Transaction ID is required.']);
    exit;
}

$transaction_id = mysqli_real_escape_string($conn, $_GET['transaction_id']); // Sanitize input

// Query to fetch unique IDs associated with the transaction
$query = "
    SELECT iq.unique_id
    FROM transaction_item_quantities tiq
    INNER JOIN item_quantities iq ON tiq.item_quantity_id = iq.id
    WHERE tiq.transaction_id = '$transaction_id'
";

$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        echo json_encode(['status' => 'success', 'items' => $items]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No items found for the given transaction ID.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . mysqli_error($conn)]);
}
