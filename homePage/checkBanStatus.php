<?php
session_start();
require_once '../conn/conn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['userId'] ?? null;

    if ($userId) {
        $stmt = $conn->prepare("SELECT ban FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($banStatus);
        $stmt->fetch();
        $stmt->close();

        echo json_encode(['banned' => $banStatus === 1]);
    } else {
        echo json_encode(['error' => 'Invalid user ID']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
