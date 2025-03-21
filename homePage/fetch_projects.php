<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);

if (isset($_GET['organization_id'])) {
    $organization_id = intval($_GET['organization_id']);
    $query = "SELECT * FROM projects WHERE organization_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $organization_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    echo json_encode($projects);
} else {
    echo json_encode([]);
}
?>