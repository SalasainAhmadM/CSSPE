<?php
session_start();
require_once '../../conn/conn.php';
require_once '../../conn/auth.php';

validateSessionRole('inventory_admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $name = htmlspecialchars(trim($_POST['name']));
    $note = htmlspecialchars(trim($_POST['note']));
    $type = htmlspecialchars(trim($_POST['type']));
    $description = htmlspecialchars(trim($_POST['description']));
    $brands = json_decode($_POST['brands'], true); // Array of brands and quantities
    $image = null;

    if (empty($id) || empty($name) || empty($type) || empty($description) || empty($brands)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required!']);
        exit;
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../../assets/uploads/';
        $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($imageFileType, $allowedTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid image format!']);
            exit;
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $fileName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
            exit;
        }
    }

    // Update `items` table
    $query = "UPDATE items SET name = ?, type = ?, note = ?, description = ?";
    if ($image) {
        $query .= ", image = ?";
    }
    $query .= " WHERE id = ?";

    $stmt = $conn->prepare($query);
    if ($image) {
        $stmt->bind_param("sssssi", $name, $type, $note, $description, $image, $id);
    } else {
        $stmt->bind_param("ssssi", $name, $type, $note, $description, $id);
    }

    if ($stmt->execute()) {
        // Handle brands and their quantities
        // Handle brands and their quantities
        foreach ($brands as $brand) {
            $brandId = intval($brand['brand_id']);
            $brandName = $brand['name'];
            $quantity = intval($brand['quantity']);

            // Check if the brand exists in the database
            $stmtBrand = $conn->prepare("SELECT id, quantity FROM brands WHERE name = ? AND item_id = ?");
            $stmtBrand->bind_param("si", $brandName, $id);
            $stmtBrand->execute();
            $resultBrand = $stmtBrand->get_result();
            $currentBrand = $resultBrand->fetch_assoc();
            $stmtBrand->close();

            if ($currentBrand) {
                // Update existing brand
                $currentBrandQuantity = intval($currentBrand['quantity']);
                $quantityChange = $quantity - $currentBrandQuantity;

                $brandQuery = "UPDATE brands SET quantity = ?, origin_quantity = ? WHERE id = ?";
                $brandStmt = $conn->prepare($brandQuery);
                $brandStmt->bind_param("iii", $quantity, $quantity, $currentBrand['id']);
                $brandStmt->execute();
                $brandStmt->close();

                // Handle `item_quantities` adjustments for the brand
                if ($quantityChange > 0) {
                    // Add new `item_quantities` rows
                    $stmtInsert = $conn->prepare("INSERT INTO item_quantities (item_id, unique_id, brand_id) VALUES (?, ?, ?)");

                    // Fetch the last unique_id for the item
                    $lastUniqueIdQuery = "SELECT unique_id FROM item_quantities WHERE item_id = ? ORDER BY unique_id DESC LIMIT 1";
                    $lastUniqueIdStmt = $conn->prepare($lastUniqueIdQuery);
                    $lastUniqueIdStmt->bind_param("i", $id);
                    $lastUniqueIdStmt->execute();
                    $lastUniqueIdStmt->bind_result($lastUniqueId);
                    $lastUniqueIdStmt->fetch();
                    $lastUniqueIdStmt->close();

                    // Extract the incremental part from the last unique_id
                    $incrementalNumber = 1; // Default to 1 if no previous unique_id exists
                    if ($lastUniqueId) {
                        $incrementalPart = intval(substr($lastUniqueId, -3)); // Extract the last 3 digits
                        $incrementalNumber = $incrementalPart + 1; // Increment by 1
                    }

                    // Generate the date part (e.g., 250421 for April 25, 2021)
                    $datePart = date('dmy'); // Current date in dmy format

                    // Insert new rows with unique_id
                    for ($i = 0; $i < $quantityChange; $i++) {
                        $uniqueId = $datePart . str_pad($incrementalNumber, 3, '0', STR_PAD_LEFT); // Combine date and incremental parts
                        $stmtInsert->bind_param("iss", $id, $uniqueId, $currentBrand['id']);
                        $stmtInsert->execute();
                        $incrementalNumber++; // Increment for the next row
                    }
                    $stmtInsert->close();
                } elseif ($quantityChange < 0) {
                    // Remove excess `item_quantities` rows
                    $quantityToDelete = abs($quantityChange);

                    // Fetch IDs to delete
                    $stmtSelect = $conn->prepare("SELECT id FROM item_quantities WHERE item_id = ? AND brand_id = ? ORDER BY id DESC LIMIT ?");
                    $stmtSelect->bind_param("isi", $id, $currentBrand['id'], $quantityToDelete);
                    $stmtSelect->execute();
                    $resultSelect = $stmtSelect->get_result();

                    $idsToDelete = [];
                    while ($row = $resultSelect->fetch_assoc()) {
                        $idsToDelete[] = $row['id'];
                    }
                    $stmtSelect->close();

                    if (!empty($idsToDelete)) {
                        $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                        $stmtDelete = $conn->prepare("DELETE FROM item_quantities WHERE id IN ($placeholders)");
                        $stmtDelete->bind_param(str_repeat('i', count($idsToDelete)), ...$idsToDelete);
                        $stmtDelete->execute();
                        $stmtDelete->close();
                    }
                }
            } else {
                // Insert new brand
                $brandQuery = "INSERT INTO brands (name, quantity, origin_quantity, item_id) VALUES (?, ?, ?, ?)";
                $brandStmt = $conn->prepare($brandQuery);
                $brandStmt->bind_param("siii", $brandName, $quantity, $quantity, $id);
                $brandStmt->execute();
                $newBrandId = $brandStmt->insert_id; // Get the ID of the newly inserted brand
                $brandStmt->close();

                if ($newBrandId && $quantity > 0) {
                    // Prepare statement for inserting into `item_quantities`
                    $stmtInsert = $conn->prepare("INSERT INTO item_quantities (item_id, unique_id, brand_id) VALUES (?, ?, ?)");

                    // Fetch the last `unique_id` for the given `item_id`
                    $lastUniqueIdQuery = "SELECT unique_id FROM item_quantities WHERE item_id = ? ORDER BY unique_id DESC LIMIT 1";
                    $lastUniqueIdStmt = $conn->prepare($lastUniqueIdQuery);
                    $lastUniqueIdStmt->bind_param("i", $id);
                    $lastUniqueIdStmt->execute();
                    $lastUniqueIdStmt->bind_result($lastUniqueId);
                    $lastUniqueIdStmt->fetch();
                    $lastUniqueIdStmt->close();

                    // Extract the last 3 digits and determine the next incremental number
                    $incrementalNumber = 1; // Default to 001 if no previous record
                    if ($lastUniqueId) {
                        $incrementalPart = intval(substr($lastUniqueId, -3)); // Get last 3 digits
                        $incrementalNumber = $incrementalPart + 1;
                    }

                    // Generate the date part (e.g., 110324 for March 11, 2025)
                    $datePart = date('dmy'); // Current date in dmy format

                    // Insert new rows with unique `unique_id`
                    for ($i = 0; $i < $quantity; $i++) {
                        $uniqueId = $datePart . str_pad($incrementalNumber, 3, '0', STR_PAD_LEFT); // Format as dmyXXX
                        $stmtInsert->bind_param("iss", $id, $uniqueId, $newBrandId);
                        $stmtInsert->execute();
                        $incrementalNumber++; // Increment for the next row
                    }

                    $stmtInsert->close();
                }

            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Item updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update item.']);
    }

    $stmt->close();
    $conn->close();
}
?>