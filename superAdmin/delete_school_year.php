<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('super_admin');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. ID is required.']);
    exit;
}

$id = intval($_GET['id']);

// Delete from the database
$query = "DELETE FROM school_years WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'School year deleted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete school year.']);
}
?>