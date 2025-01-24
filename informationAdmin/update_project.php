<?php
header('Content-Type: application/json');
require_once '../conn/conn.php';

$uploadDir = '../assets/img/';
$data = $_POST;

// Check if a file was uploaded
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExt, $allowedExtensions)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed types: jpg, jpeg, png, gif.']);
        exit;
    }

    $newFileName = uniqid() . '.' . $fileExt;
    $uploadFilePath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $uploadFilePath)) {
        echo json_encode(['success' => false, 'error' => 'Failed to upload image.']);
        exit;
    }

    $imagePath = $uploadFilePath;
}

// Update project details
$project_id = $data['id'];
$project_name = $data['project_name'];
$description = $data['description'];

// Use the existing image if a new one is not provided
$query = "UPDATE projects SET project_name = ?, description = ?, image = IFNULL(?, image) WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssi", $project_name, $description, $imagePath, $project_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update project.']);
}
?>