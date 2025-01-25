<?php
require_once '../../conn/conn.php';

if (isset($_GET['school_year']) && isset($_GET['semester'])) {
    $schoolYear = $_GET['school_year'];
    $semester = $_GET['semester'];

    $query = "SELECT start_date, end_date FROM school_years WHERE school_year = ? AND semester = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $schoolYear, $semester);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['status' => 'error', 'message' => 'School year not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
}
?>