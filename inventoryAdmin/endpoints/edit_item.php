<?php
require_once '../../conn/conn.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (!$id || empty($name) || empty($brand) || $quantity <= 0 || empty($description)) {
        $response['error'] = 'All fields are required, and quantity must be greater than 0.';
        echo json_encode($response);
        exit;
    }

    // Handle file upload
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../../assets/uploads/";
        $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $response['error'] = 'Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.';
            echo json_encode($response);
            exit;
        }

        // Move file to target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $fileName;
        } else {
            $response['error'] = 'Failed to upload image.';
            echo json_encode($response);
            exit;
        }
    }

    // Construct SQL query
    $query = "UPDATE items SET name = ?, brand = ?, quantity = ?, description = ?";
    $params = [$name, $brand, $quantity, $description];
    $types = "sssi";

    if ($image) {
        $query .= ", image = ?";
        $params[] = $image;
        $types .= "s";
    }

    $query .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    // Execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to update item.';
    }

    $stmt->close();
}

echo json_encode($response);
?>