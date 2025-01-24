<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

// Ensure the user has the appropriate role to access this functionality
validateSessionRole('super_admin');

// Check if the 'id' parameter is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. ID is required.']);
    exit;
}

$id = intval($_GET['id']);

// Fetch the school year record from the database
$query = "SELECT id, school_year, semester, start_date, end_date FROM school_years WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $schoolYear = $result->fetch_assoc();
        echo json_encode(['success' => true, 'schoolYear' => $schoolYear]);
    } else {
        echo json_encode(['success' => false, 'message' => 'School year not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch school year details.']);
}

$stmt->close();
$conn->close();
?>