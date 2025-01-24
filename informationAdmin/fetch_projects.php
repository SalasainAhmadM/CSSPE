<?php
require_once '../conn/conn.php';

$organization_id = $_GET['organization_id'];

$query = "SELECT id, project_name, description, image FROM projects WHERE organization_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $organization_id);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

echo json_encode($projects);
?>