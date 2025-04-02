<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);

$userid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input['action'] === 'mark_as_read' && isset($input['status'])) {
        $status = $input['status'];

        // Validate status parameter
        $validStatuses = ['Pending', 'Approved'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
            exit();
        }

        // Update notifications for the specific status
        $sql = "UPDATE item_transactions 
                SET notif = 1 
                WHERE users_id = ? 
                AND status = ?
                AND notif = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userid, $status);

        if ($stmt->execute()) {
            // Get updated notification counts
            $getCounts = function ($status) use ($conn, $userid) {
                $sql = "SELECT COUNT(*) AS count 
                        FROM item_transactions 
                        WHERE users_id = ? 
                        AND status = ? 
                        AND notif = 0";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $userid, $status);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc()['count'];
            };

            echo json_encode([
                'success' => true,
                'pendingNotif' => $getCounts('Pending'),
                'borrowedNotif' => $getCounts('Approved')
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }

        $stmt->close();
        exit();
    }
}