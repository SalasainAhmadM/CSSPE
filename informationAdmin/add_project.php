<?php
// Start the session to manage flash messages
session_start();

require_once '../conn/conn.php';

// Check database connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = trim($_POST['project_name']);
    $projectDescription = trim($_POST['project_description']);
    $organizationId = intval($_POST['organization_id']);

    // Validate fields
    if (empty($projectName) || empty($projectDescription) || empty($organizationId)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Handle file upload
    $uploadDir = '../assets/img/'; // Directory to store uploaded images
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $imagePath = '';
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['project_image']['tmp_name'];
        $fileName = basename($_FILES['project_image']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.']);
            exit;
        }

        // Generate a unique file name
        $newFileName = uniqid('project_', true) . '.' . $fileExtension;
        $imagePath = $uploadDir . $newFileName;

        // Move the file to the target directory
        if (!move_uploaded_file($fileTmpPath, $imagePath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload the image.']);
            exit;
        }
    }

    // Insert project into the database
    $stmt = $conn->prepare("INSERT INTO projects (project_name, description, image, organization_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('sssi', $projectName, $projectDescription, $imagePath, $organizationId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Project added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add project.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>