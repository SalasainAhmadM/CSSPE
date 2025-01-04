<?php
require_once '../../conn/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['brand_item'];
    $teacher_id = $_POST['teacher'];
    $quantity = $_POST['quantity'];
    $borrow_date = $_POST['borrow_date'];
    $schedule_from = $_POST['schedule_from'];
    $schedule_to = $_POST['schedule_to'];

    // Validate required inputs
    if (empty($item_id) || empty($teacher_id) || empty($quantity) || empty($borrow_date) || empty($schedule_from) || empty($schedule_to)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Get current datetime in Philippine timezone
    $dateTime = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $currentDateTime = $dateTime->format('Y-m-d H:i:s');

    // Combine borrow_date with current time
    $borrowed_at = $borrow_date . ' ' . $dateTime->format('H:i:s');

    // Insert into `item_transactions` table
    $query = "INSERT INTO item_transactions (
                quantity_borrowed, 
                borrowed_at, 
                item_id, 
                users_id, 
                schedule_from, 
                schedule_to
              ) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        'isiiis',
        $quantity,
        $borrowed_at,
        $item_id,
        $teacher_id,
        $schedule_from,
        $schedule_to
    );

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Item borrowed successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to borrow the item.']);
    }
}
?>