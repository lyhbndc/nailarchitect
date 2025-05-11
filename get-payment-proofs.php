<?php
// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No appointment ID provided']);
    exit;
}

$appointment_id = intval($_GET['id']);

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get payment proofs
$query = "SELECT image_path FROM payment_proofs WHERE booking_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row['image_path'];
}

// Close connection
mysqli_close($conn);

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'images' => $images]);
?>