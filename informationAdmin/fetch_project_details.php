<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    echo json_encode(['error' => 'Project ID is missing.']);
    exit;
}

// Prepare the SQL query
$query = "SELECT id, project_name, description, image, organization_id FROM projects WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $projectId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the project details
if ($project = $result->fetch_assoc()) {
    echo json_encode($project);
} else {
    echo json_encode(['error' => 'Project not found.']);
}
?>