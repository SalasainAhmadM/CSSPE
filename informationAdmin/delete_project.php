<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

$project_id = $_GET['id'];

// Delete project
$query = "DELETE FROM projects WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $project_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete project']);
}
?>