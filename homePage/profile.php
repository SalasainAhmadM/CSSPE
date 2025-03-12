<?php
session_start();
require_once '../conn/conn.php';
require_once '../conn/auth.php';

validateSessionRole(['instructor', 'information_admin', 'inventory_admin']);

// Fetch user data
$userid = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, middle_name, email, address, contact_no, rank, role, image, department 
        FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

// Fetch borrowed history
$sql_borrow_history = "SELECT t.transaction_id, t.quantity_borrowed, t.borrowed_at, t.return_date, t.status, 
                              i.name AS item_name, i.brand AS item_brand
                       FROM item_transactions t
                       JOIN items i ON t.item_id = i.id
                       WHERE t.users_id = ?
                       ORDER BY t.borrowed_at DESC";
$stmt_borrow_history = $conn->prepare($sql_borrow_history);
$stmt_borrow_history->bind_param("i", $userid);
$stmt_borrow_history->execute();
$borrow_history_result = $stmt_borrow_history->get_result();

// Fetch lost, damaged, and replaced items
$sql_item_status = "SELECT 
                        status, COUNT(*) AS count 
                    FROM returned_items 
                    WHERE transaction_id IN (
                        SELECT transaction_id 
                        FROM item_transactions 
                        WHERE users_id = ?
                    ) 
                    GROUP BY status";
$stmt_item_status = $conn->prepare($sql_item_status);
$stmt_item_status->bind_param("i", $userid);
$stmt_item_status->execute();
$item_status_result = $stmt_item_status->get_result();

$statuses = [
    'Lost' => 0,
    'Damaged' => 0,
    'Replaced' => 0,
];

while ($row = $item_status_result->fetch_assoc()) {
    $statuses[$row['status']] = $row['count'];
}

$stmt_item_status->close();


// Fetch Lost Items for Logged-in User
$lostQuery = "
    SELECT 
        r.return_id,
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        CONCAT(u.first_name, ' ', u.last_name) AS fullname,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Lost' AND u.id = ?
";
$lostStmt = $conn->prepare($lostQuery);
$lostStmt->bind_param("i", $userid);
$lostStmt->execute();
$lostItems = $lostStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Damaged Items for Logged-in User
$damagedQuery = "
    SELECT 
        r.return_id,
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        CONCAT(u.first_name, ' ', u.last_name) AS fullname,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Damaged' AND u.id = ?
";
$damagedStmt = $conn->prepare($damagedQuery);
$damagedStmt->bind_param("i", $userid);
$damagedStmt->execute();
$damagedItems = $damagedStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Replaced Items for Logged-in User
$replacedQuery = "
    SELECT 
        r.return_id,
        i.name AS item_name,
        i.brand,
        r.quantity_returned,
        r.returned_at,
        r.remarks,
        CONCAT(u.first_name, ' ', u.last_name) AS fullname,
        u.contact_no,
        u.email
    FROM returned_items r
    JOIN items i ON r.item_id = i.id
    JOIN item_transactions t ON r.transaction_id = t.transaction_id
    JOIN users u ON t.users_id = u.id
    WHERE r.status = 'Replaced' AND u.id = ?
";
$replacedStmt = $conn->prepare($replacedQuery);
$replacedStmt->bind_param("i", $userid);
$replacedStmt->execute();
$replacedItems = $replacedStmt->get_result()->fetch_all(MYSQLI_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_SESSION['user_id'];

    // Update profile details
    $firstName = $_POST['first_name'] ?? $user['first_name'];
    $lastName = $_POST['last_name'] ?? $user['last_name'];
    $middleName = $_POST['middle_name'] ?? $user['middle_name'];
    $email = $_POST['email'] ?? $user['email'];
    $address = $_POST['address'] ?? $user['address'];
    $contactNo = $_POST['contact_no'] ?? $user['contact_no'];
    $rank = $_POST['rank'] ?? $user['rank'];
    $department = $_POST['department'] ?? $user['department'];

    $sql = "UPDATE users SET first_name = ?, last_name = ?, middle_name = ?, email = ?, address = ?, contact_no = ?, rank = ?, department = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $firstName, $lastName, $middleName, $email, $address, $contactNo, $rank, $department, $userid);

    if ($stmt->execute()) {
        $stmt->close();

        // Handle password change
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userid);
            $stmt->execute();
            $stmt->close();
        }

        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_image']['tmp_name'];
            $fileName = $_FILES['profile_image']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = $userid . '_profile.' . $fileExtension;
                $uploadFileDir = '../assets/img/';
                $destPath = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $sql = "UPDATE users SET image = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $newFileName, $userid);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $_SESSION['message'] = "Profile updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to update profile!";
        $_SESSION['message_type'] = "error";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$query_notifications = "SELECT COUNT(*) AS notification_count FROM notifications WHERE is_read = 0";
$result_notifications = mysqli_query($conn, $query_notifications);
$notificationCount = 0;

if ($result_notifications && $row_notifications = mysqli_fetch_assoc($result_notifications)) {
    $notificationCount = $row_notifications['notification_count'];
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Base styles */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            scroll-behavior: smooth;
            overflow-x: hidden;
            width: 100%;
        }

        /* Theme colors */
        :root {
            --primary: #6B0D0D;
            --primary-dark: #540A0A;
            --secondary: #6c757d;
            --secondary-dark: #5a6268;
            --light: #f9f9f9;
            --lighter: #f5f5f5;
            --border: #f0f0f0;
            --text: #333;
            --text-light: #666;
            --white: #fff;
            --gray: #ddd;
            --gray-dark: #aaa;
        }

        /* Layout */
        .sidebar {
            position: fixed;
            width: 250px;
            height: 100%;
            background-color: var(--primary);
            color: var(--white);
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        /* Button styling */
        .btn {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            white-space: nowrap;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            box-shadow: 0 2px 4px rgba(107, 13, 13, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 3px 6px rgba(107, 13, 13, 0.3);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: var(--white);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }

        /* Mobile navigation */
        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 101;
            display: none;
            padding: 8px;
            border-radius: 6px;
            background-color: var(--primary);
            color: var(--white);
            font-size: 18px;
            border: none;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .toggle-btn:hover {
            background-color: var(--primary-dark);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 99;
            backdrop-filter: blur(2px);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Header styling */
        .page-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-header img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .system-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* Sidebar styling */
        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header img {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 50%;
        }

        .sidebar-header span {
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-link {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: rgba(255, 255, 255, 0.5);
        }

        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }

        .sidebar-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }

        .sidebar-footer {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            position: absolute;
            bottom: 0;
            width: 100%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-footer i {
            width: 20px;
            margin-right: 10px;
        }

        /* Page content */
        .page-content {
            padding: 25px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--primary);
            display: flex;
            align-items: center;
            border-bottom: 2px solid var(--border);
            padding-bottom: 15px;
        }

        /* Dashboard Stats */
        .stats-container {
            margin: 20px 0 30px 0;
            background-color: var(--white);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 200px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(107, 13, 13, 0.15);
        }

        .stat-title {
            background-color: var(--primary);
            color: var(--white);
            padding: 12px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-value {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100px;
            font-size: 36px;
            font-weight: bold;
            color: var(--primary);
        }

        /* Profile container */
        .profile-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-sidebar {
            flex: 0 0 250px;
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .profile-image-container {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 4px solid var(--lighter);
        }

        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .edit-profile-btn {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 15px;
            box-shadow: 0 2px 5px rgba(107, 13, 13, 0.2);
        }

        .edit-profile-btn:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 10px rgba(107, 13, 13, 0.3);
        }

        .profile-details {
            flex: 1;
            min-width: 300px;
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .detail-group {
            display: flex;
            flex-direction: column;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
        }

        .detail-group:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 500;
            color: var(--text);
        }

        /* Borrow History */
        .borrow-history {
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
        }

        .borrow-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-container {
            padding: 15px 20px;
            background-color: var(--lighter);
            border-bottom: 1px solid var(--border);
        }

        .search-bar {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-bar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.1);
        }

        .table-container {
            padding: 0;
            overflow-x: auto;
        }

        .borrow-table {
            width: 100%;
            border-collapse: collapse;
        }

        .borrow-table th,
        .borrow-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .borrow-table th {
            background-color: var(--lighter);
            font-weight: 600;
            color: var(--text);
            position: sticky;
            top: 0;
        }

        .borrow-table tr:last-child td {
            border-bottom: none;
        }

        .borrow-table tr:hover {
            background-color: var(--lighter);
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-body {
            padding: 20px;
        }

        .close-btn {
            background: none;
            border: none;
            color: var(--white);
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .close-btn:hover {
            opacity: 0.8;
        }

        /* Table modal styles */
        .table-modal .modal-body {
            padding: 0;
        }

        .table-modal-content {
            width: 95%;
            max-width: 1000px;
        }

        .table-modal .borrow-table th,
        .table-modal .borrow-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .table-modal .modal-actions {
            padding: 15px 20px;
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid var(--border);
            background-color: var(--lighter);
            position: sticky;
            bottom: 0;
        }

        /* Form styles */
        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-sidebar {
            flex: 0 0 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-image-container {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 3px solid var(--lighter);
        }

        .form-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .file-input {
            margin-bottom: 10px;
        }

        .form-details {
            flex: 1;
            min-width: 300px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(107, 13, 13, 0.1);
        }

        .password-field {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-dark);
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
                width: calc(100% - 220px);
            }

            .profile-sidebar {
                flex: 0 0 220px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }

            .stat-card {
                width: 180px;
            }

            .profile-sidebar {
                flex: 0 0 200px;
            }

            .profile-image-container {
                width: 140px;
                height: 140px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                left: -280px;
                box-shadow: none;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .toggle-btn {
                display: block !important;
            }

            .page-header {
                padding-left: 60px;
            }

            .profile-container {
                flex-direction: column;
            }

            .profile-sidebar {
                flex: 0 0 auto;
                width: 100%;
            }

            .page-content {
                padding: 15px;
            }

            .stats-grid {
                justify-content: space-evenly;
            }

            .stat-card {
                width: 160px;
            }

            .form-container {
                flex-direction: column;
            }

            .form-sidebar {
                flex: 0 0 auto;
                width: 100%;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                width: 140px;
            }

            .stat-title {
                font-size: 14px;
                padding: 10px;
            }

            .stat-value {
                height: 80px;
                font-size: 28px;
            }

            .profile-image-container {
                width: 120px;
                height: 120px;
            }

            .detail-group {
                padding: 12px 15px;
            }

            .detail-label {
                font-size: 13px;
            }

            .detail-value {
                font-size: 15px;
            }

            .borrow-header {
                padding: 12px 15px;
                font-size: 16px;
            }

            .search-container {
                padding: 12px 15px;
            }

            .borrow-table th,
            .borrow-table td {
                padding: 10px 12px;
                font-size: 13px;
            }

            .form-image-container {
                width: 150px;
                height: 150px;
            }
        }

        @media (max-width: 400px) {
            .page-content {
                padding: 10px;
            }

            .stats-grid {
                gap: 10px;
            }

            .stat-card {
                width: 130px;
            }

            .profile-image-container {
                width: 110px;
                height: 110px;
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--lighter);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>

<body>
    <!-- Toggle Sidebar Button -->
    <button class="toggle-btn" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay (shown on mobile when sidebar is open) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/img/<?= !empty($image) ? htmlspecialchars($image) : 'CSSPE.png' ?>" alt="Profile">
            <span><?php echo $fullName; ?></span>
        </div>

        <div style="display: flex; flex-direction: column; padding: 10px 0;">
            <?php if ($_SESSION['user_role'] === 'inventory_admin'): ?>
                <a href="../inventoryAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Inventory Admin Panel
                </a>
            <?php elseif ($_SESSION['user_role'] === 'information_admin'): ?>
                <a href="../informationAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Information Admin Panel
                </a>
            <?php elseif ($_SESSION['user_role'] === 'super_admin'): ?>
                <a href="../superAdmin/index.php" class="sidebar-link">
                    <i class="fas fa-arrow-left"></i> Back to Super Admin Panel
                </a>
            <?php endif; ?>

            <a href="../homePage/profile.php" class="sidebar-link active">
                <i class="fas fa-user"></i> Profile
            </a>

            <a href="../homePage/" class="sidebar-link">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>

            <a href="../homePage/borrowing.php" class="sidebar-link">
                <i class="fas fa-boxes"></i> Inventories
            </a>

            <a href="../homePage/memorandumHome.php" class="sidebar-link">
                <i class="fas fa-file-alt"></i> Memorandums
            </a>

            <a href="../homePage/events.php" class="sidebar-link">
                <i class="fas fa-calendar-alt"></i> Events
            </a>

            <a href="../homePage/members.php" class="sidebar-link">
                <i class="fas fa-users"></i> Faculty Members
            </a>

            <a href="../homePage/organization.php" class="sidebar-link">
                <i class="fas fa-sitemap"></i> Organizations
            </a>

            <a href="../homePage/notification.php" class="sidebar-link">
                <i class="fas fa-bell"></i> Notifications
            </a>
        </div>

        <a href="../logout.php" class="sidebar-footer">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="page-header">
            <img src="../assets/img/CSSPE.png" alt="Logo">
            <h1 class="system-title">CSSPE Inventory & Information System</h1>
        </header>

        <!-- Content -->
        <div class="page-content">
            <!-- Stats Dashboard -->
            <div class="stats-container">
                <div class="stats-grid">
                    <div class="stat-card" onclick="lost()">
                        <div class="stat-title">Lost</div>
                        <div class="stat-value"><?php echo $statuses['Lost']; ?></div>
                    </div>

                    <div class="stat-card" onclick="damage()">
                        <div class="stat-title">Damaged</div>
                        <div class="stat-value"><?php echo $statuses['Damaged']; ?></div>
                    </div>

                    <div class="stat-card" onclick="replace1()">
                        <div class="stat-title">Replaced Items</div>
                        <div class="stat-value"><?php echo $statuses['Replaced']; ?></div>
                    </div>
                </div>
            </div>

            <h2 class="page-title">
                <i class="fas fa-user-circle" style="margin-right: 12px;"></i> User Profile
            </h2>

            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-image-container">
                        <?php
                        $profileImage = !empty($user['image']) ? "../assets/img/" . htmlspecialchars($user['image']) : "../assets/img/CSSPE.png";
                        ?>
                        <img class="profile-image" src="<?php echo $profileImage; ?>" alt="Profile Picture">
                    </div>

                    <button class="edit-profile-btn" onclick="profile()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>

                <div class="profile-details">
                    <div class="detail-group">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value"><?= htmlspecialchars($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name']) ?></div>
                    </div>

                    <div class="detail-group">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>

                    <div class="detail-group">
                        <div class="detail-label">Contact Number</div>
                        <div class="detail-value"><?= htmlspecialchars($user['contact_no']) ?></div>
                    </div>

                    <div class="detail-group">
                        <div class="detail-label">Address</div>
                        <div class="detail-value"><?= htmlspecialchars($user['address']) ?></div>
                    </div>

                    <div class="detail-group">
                        <div class="detail-label">Position</div>
                        <div class="detail-value"><?= htmlspecialchars($user['rank']) ?></div>
                    </div>

                    <div class="detail-group">
                        <div class="detail-label">Department</div>
                        <div class="detail-value"><?= htmlspecialchars($user['department']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Borrow History -->
            <div class="borrow-history">
                <div class="borrow-header">
                    <span><i class="fas fa-history"></i> Borrow History</span>
                </div>

                <div class="search-container">
                    <input id="searchBar" class="search-bar" type="text" placeholder="Search by item name...">
                </div>

                <div class="table-container">
                    <?php if ($borrow_history_result->num_rows > 0): ?>
                        <table class="borrow-table" id="borrowHistoryTable">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Item Name</th>
                                    <th>Brand</th>
                                    <th>Quantity</th>
                                    <th>Borrow Date</th>
                                    <th>Expected Return Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($row = $borrow_history_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                                        <td><?= htmlspecialchars($row['item_brand']) ?></td>
                                        <td><?= htmlspecialchars($row['quantity_borrowed']) ?></td>
                                        <td><?= htmlspecialchars($row['borrowed_at']) ?></td>
                                        <td><?= htmlspecialchars($row['return_date'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['status']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 30px;">
                            <p>No borrow history available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Lost Items Modal -->
    <div class="modal-overlay table-modal" id="lostItemsModal">
        <div class="modal-content table-modal-content">
            <div class="modal-header">
                <span><i class="fas fa-exclamation-triangle"></i> Lost Items</span>
                <button class="close-btn" onclick="lost()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="table-container">
                    <table class="borrow-table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Date Lost</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lostItems)): ?>
                                <?php foreach ($lostItems as $item): ?>
                                    <tr>
                                        <td><?php echo $item['return_id']; ?></td>
                                        <td><?php echo $item['item_name']; ?></td>
                                        <td><?php echo $item['brand']; ?></td>
                                        <td><?php echo $item['quantity_returned']; ?></td>
                                        <td><?php echo $item['returned_at']; ?></td>
                                        <td><?php echo $item['contact_no']; ?></td>
                                        <td><?php echo $item['email']; ?></td>
                                        <td><?php echo $item['remarks']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No lost items.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="lost()">Close</button>
            </div>
        </div>
    </div>

    <!-- Damaged Items Modal -->
    <div class="modal-overlay table-modal" id="damagedItemsModal">
        <div class="modal-content table-modal-content">
            <div class="modal-header">
                <span><i class="fas fa-tools"></i> Damaged Items</span>
                <button class="close-btn" onclick="damage()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="table-container">
                    <table class="borrow-table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Date Returned</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($damagedItems)): ?>
                                <?php foreach ($damagedItems as $item): ?>
                                    <tr>
                                        <td><?php echo $item['return_id']; ?></td>
                                        <td><?php echo $item['item_name']; ?></td>
                                        <td><?php echo $item['brand']; ?></td>
                                        <td><?php echo $item['quantity_returned']; ?></td>
                                        <td><?php echo $item['returned_at']; ?></td>
                                        <td><?php echo $item['contact_no']; ?></td>
                                        <td><?php echo $item['email']; ?></td>
                                        <td><?php echo $item['remarks']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No damaged items.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="damage()">Close</button>
            </div>
        </div>
    </div>

    <!-- Replaced Items Modal -->
    <div class="modal-overlay table-modal" id="replacedItemsModal">
        <div class="modal-content table-modal-content">
            <div class="modal-header">
                <span><i class="fas fa-exchange-alt"></i> Replaced Items</span>
                <button class="close-btn" onclick="replace1()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="table-container">
                    <table class="borrow-table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Date Replaced</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($replacedItems)): ?>
                                <?php foreach ($replacedItems as $item): ?>
                                    <tr>
                                        <td><?php echo $item['return_id']; ?></td>
                                        <td><?php echo $item['item_name']; ?></td>
                                        <td><?php echo $item['brand']; ?></td>
                                        <td><?php echo $item['quantity_returned']; ?></td>
                                        <td><?php echo $item['returned_at']; ?></td>
                                        <td><?php echo $item['contact_no']; ?></td>
                                        <td><?php echo $item['email']; ?></td>
                                        <td><?php echo $item['remarks']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No replaced items.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-primary" onclick="replace1()">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="profileModal">
        <div class="modal-content">
            <div class="modal-header">
                <span><i class="fas fa-user-edit"></i> Edit Profile</span>
                <button class="close-btn" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-container">
                        <div class="form-sidebar">
                            <div class="form-image-container">
                                <img id="profileImagePreview" class="form-image"
                                    src="<?= '../assets/img/' . (!empty($user['image']) && file_exists('../assets/img/' . $user['image']) ? htmlspecialchars($user['image']) : 'CSSPE.png') ?>"
                                    alt="Profile Picture">
                            </div>
                            <div class="image-upload">
                                <input type="file" name="profile_image" id="profileImageInput" accept="image/*" class="file-input">
                                <button type="submit" class="btn btn-primary">Upload Image</button>
                            </div>
                        </div>

                        <div class="form-details">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" value="<?= htmlspecialchars($user['middle_name']) ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_no" value="<?= htmlspecialchars($user['contact_no']) ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Position</label>
                                <input type="text" name="rank" value="<?= htmlspecialchars($user['rank']) ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" value="<?= htmlspecialchars($user['department']) ?>" class="form-control">
                            </div>

                            <div class="form-group password-field">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="New Password (leave blank if unchanged)">
                                <button type="button" id="togglePassword" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const lostItemsModal = document.getElementById('lostItemsModal');
        const damagedItemsModal = document.getElementById('damagedItemsModal');
        const replacedItemsModal = document.getElementById('replacedItemsModal');
        const profileModal = document.getElementById('profileModal');
        const profileImageInput = document.getElementById('profileImageInput');
        const profileImagePreview = document.getElementById('profileImagePreview');
        const passwordField = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const searchBar = document.getElementById('searchBar');

        // Profile image preview
        profileImageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Search functionality
        searchBar.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#borrowHistoryTable tbody tr');

            rows.forEach(row => {
                const itemName = row.cells[1].textContent.toLowerCase();
                if (itemName.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Check if on mobile and show toggle button
        function checkMobile() {
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
                sidebar.classList.remove('active');
                mainContent.style.marginLeft = '0';

                // Check if sidebar is showing and hide it
                if (sidebar.style.left === '0px') {
                    sidebar.style.left = '-280px';
                    sidebarOverlay.classList.remove('active');
                }
            } else {
                toggleBtn.style.display = 'none';
                sidebar.style.left = '0';
                mainContent.style.marginLeft = sidebar.offsetWidth + 'px';
                mainContent.style.width = `calc(100% - ${sidebar.offsetWidth}px)`;
            }
        }

        // Toggle sidebar on mobile
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');

            if (sidebar.classList.contains('active')) {
                sidebar.style.left = '0';
                sidebarOverlay.classList.add('active');
            } else {
                sidebar.style.left = '-280px';
                sidebarOverlay.classList.remove('active');
            }
        });

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebar.style.left = '-280px';
            sidebarOverlay.classList.remove('active');
        });

        // Modal functions
        function lost() {
            lostItemsModal.style.display = lostItemsModal.style.display === 'flex' ? 'none' : 'flex';
            document.body.style.overflow = lostItemsModal.style.display === 'flex' ? 'hidden' : '';
        }

        function damage() {
            damagedItemsModal.style.display = damagedItemsModal.style.display === 'flex' ? 'none' : 'flex';
            document.body.style.overflow = damagedItemsModal.style.display === 'flex' ? 'hidden' : '';
        }

        function replace1() {
            replacedItemsModal.style.display = replacedItemsModal.style.display === 'flex' ? 'none' : 'flex';
            document.body.style.overflow = replacedItemsModal.style.display === 'flex' ? 'hidden' : '';
        }

        function profile() {
            profileModal.style.display = profileModal.style.display === 'flex' ? 'none' : 'flex';
            document.body.style.overflow = profileModal.style.display === 'flex' ? 'hidden' : '';
        }

        function closeModal() {
            profileModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Event listeners
        window.addEventListener('load', checkMobile);
        window.addEventListener('resize', checkMobile);

        // Handle SweetAlert messages
        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: "<?php echo $_SESSION['message_type']; ?>",
                title: "<?php echo $_SESSION['message']; ?>",
                showConfirmButton: false,
                timer: 3000
            });
            <?php unset($_SESSION['message'], $_SESSION['message_type'], $_SESSION['message_text']); ?>
        <?php endif; ?>

        // Close modals if clicking outside content area
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
</body>

</html>