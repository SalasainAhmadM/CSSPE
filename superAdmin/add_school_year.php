<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole('super_admin');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['schoolYear'], $data['semester'], $data['startDate'], $data['endDate'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input. Please fill out all fields.']);
    exit;
}

$schoolYear = $data['schoolYear'];
$semester = $data['semester'];
$startDate = $data['startDate'];
$endDate = $data['endDate'];

// Insert into the database
$query = "INSERT INTO school_years (school_year, semester, start_date, end_date) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $schoolYear, $semester, $startDate, $endDate);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'School year added successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add school year.']);
}
?>