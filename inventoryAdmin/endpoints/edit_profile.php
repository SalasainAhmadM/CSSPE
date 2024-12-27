<?php
session_start();
require_once '../../conn/conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract user inputs
    $userId = $_SESSION['user_id'];
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $middleName = htmlspecialchars(trim($_POST['middle_name'])); // Nullable
    $lastName = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $contactNo = htmlspecialchars(trim($_POST['contact_no']));
    $address = htmlspecialchars(trim($_POST['address']));
    $rank = htmlspecialchars(trim($_POST['rank']));
    $department = htmlspecialchars(trim($_POST['department']));
    $role = htmlspecialchars(trim($_POST['role']));

    // Prepare SQL
    $query = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, address = ?, contact_no = ?, rank = ?, department = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssi", $firstName, $middleName, $lastName, $email, $address, $contactNo, $rank, $department, $role, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
    }
    $stmt->close();
    exit;
}
?>