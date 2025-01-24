<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('super_admin');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'], $data['schoolYear'], $data['semester'], $data['startDate'], $data['endDate'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input. Please fill out all fields.']);
    exit;
}

$id = $data['id'];
$schoolYear = $data['schoolYear'];
$semester = $data['semester'];
$startDate = $data['startDate'];
$endDate = $data['endDate'];

// Update the database
$query = "UPDATE school_years SET school_year = ?, semester = ?, start_date = ?, end_date = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssi", $schoolYear, $semester, $startDate, $endDate, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'School year updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update school year.']);
}
?>