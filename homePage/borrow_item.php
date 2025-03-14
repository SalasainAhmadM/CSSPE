<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = intval($_POST['item_id']);
    $brand_id = intval($_POST['brand_id']); // Get selected brand ID
    $teacher_id = intval($_POST['teacher']);
    $quantity = intval($_POST['quantity']);
    $student = trim($_POST['student']);
    $return_date = $_POST['return_date'];

    if (empty($item_id) || empty($brand_id) || empty($teacher_id) || empty($quantity) || empty($return_date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Check available quantity from the `brands` table
        $query = "SELECT quantity, origin_quantity, name FROM brands WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $brand_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Brand not found.');
        }

        $brand = $result->fetch_assoc();
        if ($quantity > $brand['quantity']) {
            throw new Exception('Insufficient quantity available for this brand.');
        }

        // Insert into `item_transactions`
        $borrowed_at = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
        $query = "INSERT INTO item_transactions (
                    quantity_borrowed, 
                    borrowed_at, 
                    assigned_student, 
                    return_date, 
                    item_id, 
                    brand_id, 
                    users_id
                  ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            'issssii',
            $quantity,
            $borrowed_at,
            $student,
            $return_date,
            $item_id,
            $brand_id,
            $teacher_id
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to record transaction.');
        }

        $transaction_id = $conn->insert_id;

        // Fetch available item quantities ensuring uniqueness
        $query = "SELECT id FROM item_quantities WHERE item_id = ? AND brand_id = ? AND id NOT IN (
                    SELECT item_quantity_id FROM transaction_item_quantities
                 ) LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iii', $item_id, $brand_id, $quantity);
        $stmt->execute();
        $result = $stmt->get_result();

        $item_quantities = [];
        while ($row = $result->fetch_assoc()) {
            $item_quantities[] = $row['id'];
        }

        if (count($item_quantities) < $quantity) {
            throw new Exception('Not enough unique item quantities available for this brand.');
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

        // Update `brands` table quantity
        $newQuantity = $brand['quantity'] - $quantity;
        $query = "UPDATE brands SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $newQuantity, $brand_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update brand quantity.');
        }

        // Check if the quantity is 20% or less of the original quantity
        $threshold = $brand['origin_quantity'] * 0.2;
        if ($newQuantity <= $threshold) {
            // Insert a notification into `notif_items`
            $description = "{$brand['name']} has critical stocks.";
            $query = "INSERT INTO notif_items (description) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $description);
            if (!$stmt->execute()) {
                throw new Exception('Failed to create notification.');
            }
        }

        // Check if the item is frequently borrowed
        $query = "SELECT COUNT(*) AS borrow_count FROM item_transactions WHERE item_id = ? AND brand_id = ? AND borrowed_at > NOW() - INTERVAL 7 DAY";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $item_id, $brand_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $borrow_count = $row['borrow_count'];
        $frequency_threshold = 5;
        if ($borrow_count >= $frequency_threshold) {
            $description = "{$brand['name']} is being frequently borrowed.";
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