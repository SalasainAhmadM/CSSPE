<?php
require_once '../../conn/conn.php';

// Get the selected origin item ID
$originId = isset($_GET['origin_id']) ? intval($_GET['origin_id']) : 0;

$response = [];
if ($originId > 0) {
    // Get the origin item's details
    $originQuery = "SELECT name, brand FROM items WHERE id = ? AND type = 'origin'";
    $stmt = $conn->prepare($originQuery);
    $stmt->bind_param("i", $originId);
    $stmt->execute();
    $originResult = $stmt->get_result();

    if ($originResult->num_rows > 0) {
        $origin = $originResult->fetch_assoc();
        $originName = $origin['name'];
        $currentBrand = $origin['brand'];

        // Add the current brand as the first option
        $response[] = [
            'id' => $originId,
            'name' => $originName,
            'brand' => $currentBrand
        ];

        // Fetch other brand items with the same name
        $brandQuery = "SELECT id, name, brand FROM items WHERE name = ? AND type = 'brand'";
        $stmt = $conn->prepare($brandQuery);
        $stmt->bind_param("s", $originName);
        $stmt->execute();
        $brandResult = $stmt->get_result();

        while ($brand = $brandResult->fetch_assoc()) {
            $response[] = [
                'id' => $brand['id'],
                'name' => $brand['name'],
                'brand' => $brand['brand']
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>