<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = intval($_POST['transaction_id']);
    $item_id = intval($_POST['item_id']);
    $teacher_id = intval($_POST['teacher']);
    $quantity = intval($_POST['quantity']);
    $borrow_date = $_POST['borrow_date'];
    $return_date = $_POST['return_date'];
    $class_date = $_POST['class_date'];
    $schedule_from = $_POST['schedule_from'];
    $schedule_to = $_POST['schedule_to'];

    if (empty($transaction_id) || empty($item_id) || empty($teacher_id) || empty($quantity) || empty($borrow_date) || empty($return_date) || empty($class_date) || empty($schedule_from) || empty($schedule_to)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Get current transaction details
        $query = "SELECT quantity_borrowed, item_id FROM item_transactions WHERE transaction_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Transaction not found.');
        }

        $transaction = $result->fetch_assoc();
        $original_quantity_borrowed = $transaction['quantity_borrowed'];
        $original_item_id = $transaction['item_id'];

        // Calculate the quantity adjustment
        if ($item_id === $original_item_id) {
            // If the same item is being edited, adjust based on the difference
            $quantity_diff = $quantity - $original_quantity_borrowed;
        } else {
            // If the item ID has changed, restore the original item's quantity
            $query = "UPDATE items SET quantity = quantity + ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ii', $original_quantity_borrowed, $original_item_id);
            $stmt->execute();

            // Deduct the quantity from the new item
            $quantity_diff = $quantity;
        }

        // Check if the new quantity is available
        $query = "SELECT quantity FROM items WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Item not found.');
        }

        $item = $result->fetch_assoc();
        if ($item['quantity'] < $quantity_diff) {
            throw new Exception('Insufficient quantity available.');
        }

        // Update the item's quantity
        $new_quantity = $item['quantity'] - $quantity_diff;
        $query = "UPDATE items SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $new_quantity, $item_id);
        $stmt->execute();

        // Update the transaction
        $query = "UPDATE item_transactions 
                  SET item_id = ?, users_id = ?, quantity_borrowed = ?, borrowed_at = ?, return_date = ?, class_date = ?, schedule_from = ?, schedule_to = ? 
                  WHERE transaction_id = ?";
        $borrowed_at = $borrow_date . ' ' . (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('H:i:s');
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiisssssi', $item_id, $teacher_id, $quantity, $borrowed_at, $return_date, $class_date, $schedule_from, $schedule_to, $transaction_id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Transaction updated successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

?>