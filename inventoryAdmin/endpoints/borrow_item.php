<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = intval($_POST['item_id']);
    $teacher_id = intval($_POST['teacher']);
    $quantity = intval($_POST['quantity']);
    $borrow_date = $_POST['borrow_date'];
    $return_date = $_POST['return_date'];
    $class_date = $_POST['class_date'];
    $schedule_from = $_POST['schedule_from'];
    $schedule_to = $_POST['schedule_to'];

    if (empty($item_id) || empty($teacher_id) || empty($quantity) || empty($borrow_date) || empty($return_date) || empty($class_date) || empty($schedule_from) || empty($schedule_to)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Check available quantity
        $query = "SELECT quantity, quantity_origin, name FROM items WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Item not found.');
        }

        $item = $result->fetch_assoc();
        if ($quantity > $item['quantity']) {
            throw new Exception('Insufficient quantity available.');
        }

        // Insert into `item_transactions`
        $borrowed_at = $borrow_date . ' ' . (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('H:i:s');
        $query = "INSERT INTO item_transactions (quantity_borrowed, borrowed_at, return_date, class_date, item_id, users_id, schedule_from, schedule_to) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isssisss', $quantity, $borrowed_at, $return_date, $class_date, $item_id, $teacher_id, $schedule_from, $schedule_to);
        if (!$stmt->execute()) {
            throw new Exception('Failed to record transaction.');
        }

        // Update `items` table
        $newQuantity = $item['quantity'] - $quantity;
        $query = "UPDATE items SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $newQuantity, $item_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update item quantity.');
        }

        // Check if the quantity is 20% or less of the original quantity
        $threshold = $item['quantity_origin'] * 0.2;
        if ($newQuantity <= $threshold) {
            // Insert a notification into `notif_items`
            $description = "{$item['name']} has critical stocks.";
            $query = "INSERT INTO notif_items (description) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $description);
            if (!$stmt->execute()) {
                throw new Exception('Failed to create notification.');
            }
        }

        // Check if the item is frequently borrowed
        $query = "SELECT COUNT(*) AS borrow_count FROM item_transactions WHERE item_id = ? AND borrowed_at > NOW() - INTERVAL 7 DAY";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $borrow_count = $row['borrow_count'];
        $frequency_threshold = 5;
        if ($borrow_count >= $frequency_threshold) {
            $description = "{$item['name']} is being frequently borrowed.";
            $query = "INSERT INTO notif_items (description) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $description);
            if (!$stmt->execute()) {
                throw new Exception('Failed to create frequent borrow notification.');
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Item borrowed successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>