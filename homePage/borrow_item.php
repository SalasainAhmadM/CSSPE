<?php
require_once '../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = intval($_POST['item_id']);
    $teacher_id = intval($_POST['teacher']);
    $quantity = intval($_POST['quantity']);
    $return_date = $_POST['return_date'];

    if (empty($item_id) || empty($teacher_id) || empty($quantity) || empty($return_date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Check available quantity
        $query = "SELECT quantity, quantity_origin, name, brand FROM items WHERE id = ?";
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

        // Get the full name of the teacher
        $query = "SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('User not found.');
        }

        $user = $result->fetch_assoc();
        $full_name = $user['full_name'];

        // Get current date and time in Asia/Manila timezone
        $borrowed_at = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');

        // Insert into `item_transactions`
        $query = "INSERT INTO item_transactions (quantity_borrowed, borrowed_at, return_date, item_id, users_id) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('issis', $quantity, $borrowed_at, $return_date, $item_id, $teacher_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to record transaction.');
        }

        $transaction_id = $conn->insert_id;

        // Fetch available item quantities ensuring uniqueness
        $query = "SELECT id FROM item_quantities WHERE item_id = ? AND id NOT IN (
                    SELECT item_quantity_id FROM transaction_item_quantities
                 ) LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $item_id, $quantity);
        $stmt->execute();
        $result = $stmt->get_result();

        $item_quantities = [];
        while ($row = $result->fetch_assoc()) {
            $item_quantities[] = $row['id'];
        }

        if (count($item_quantities) < $quantity) {
            throw new Exception('Not enough unique item quantities available.');
        }

        // Add to `transaction_item_quantities`
        $query = "INSERT INTO transaction_item_quantities (transaction_id, item_quantity_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        foreach ($item_quantities as $item_quantity_id) {
            $stmt->bind_param('ii', $transaction_id, $item_quantity_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to record item quantities for transaction.');
            }
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

        // Insert a notification for the borrowed item
        $borrowNotif = "{$item['name']} ({$item['brand']}) with quantity of {$quantity} was borrowed by {$full_name}.";
        $query = "INSERT INTO notif_items (description) VALUES (?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $borrowNotif);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create borrowed item notification.');
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