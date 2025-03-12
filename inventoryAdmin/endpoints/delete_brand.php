<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $brandId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$brandId) {
        echo json_encode(['success' => false, 'message' => 'Invalid brand ID.']);
        exit;
    }

    // Start a transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // Step 1: Delete associated item quantities
        $deleteItemQuantitiesQuery = "DELETE FROM item_quantities WHERE brand_id = ?";
        $stmt = $conn->prepare($deleteItemQuantitiesQuery);
        $stmt->bind_param("i", $brandId);
        $stmt->execute();

        // Step 2: Delete the brand
        $deleteBrandQuery = "DELETE FROM brands WHERE id = ?";
        $stmt = $conn->prepare($deleteBrandQuery);
        $stmt->bind_param("i", $brandId);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Brand and associated item quantities deleted successfully.']);
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete brand and associated item quantities: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}