<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');

// Parse JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Extract fields
$inventoryAdminId = $_SESSION['user_id'];
$fullName = $data['fullName'];
$email = $data['email'];
$contactNo = $data['contactNo'];
$address = $data['address'];
$position = $data['position'];
$department = $data['department'];
$role = $data['role'];

// Split full name into components
$nameParts = explode(' ', $fullName);
$firstName = $nameParts[0] ?? '';
$middleName = $nameParts[1] ?? '';
$lastName = $nameParts[2] ?? '';

// Update query
$query = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, address = ?, contact_no = ?, rank = ?, department = ?, role = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssssssssi", $firstName, $middleName, $lastName, $email, $address, $contactNo, $position, $department, $role, $inventoryAdminId);

$response = ['success' => false];

if ($stmt->execute()) {
    $response['success'] = true;
}

echo json_encode($response);
?>