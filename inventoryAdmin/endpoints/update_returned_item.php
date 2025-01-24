<?php
require_once '../../conn/conn.php';

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['return_id'], $data['new_remarks'], $data['new_status'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    exit;
}

$return_id = $conn->real_escape_string($data['return_id']);
$new_remarks = $conn->real_escape_string($data['new_remarks']);
$new_status = $conn->real_escape_string($data['new_status']);

// Update the returned_items table
$query = "UPDATE returned_items 
          SET status = '$new_status', remarks = '$new_remarks' 
          WHERE return_id = '$return_id'";

if ($conn->query($query) === TRUE) {
    echo json_encode(['status' => 'success', 'message' => 'Item status updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update item status.']);
}

$conn->close();
?>