<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get date from request
if (!isset($_GET['date'])) {
    die(json_encode(['success' => false, 'message' => 'Missing date parameter']));
}

$date = mysqli_real_escape_string($conn, $_GET['date']);

// Define all possible time slots
$all_time_slots = [
    '9:00' => '9:00 AM',
    '10:00' => '10:00 AM',
    '11:00' => '11:00 AM',
    '12:00' => '12:00 PM',
    '13:00' => '1:00 PM',
    '14:00' => '2:00 PM',
    '15:00' => '3:00 PM',
    '16:00' => '4:00 PM',
    '17:00' => '5:00 PM',
    '18:00' => '6:00 PM'
];

// Get booked time slots for the selected date
$query = "SELECT time FROM bookings WHERE date = ? AND status != 'cancelled'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

// Create array of booked times
$booked_times = [];
while ($row = $result->fetch_assoc()) {
    $booked_times[] = $row['time'];
}

// Filter out booked times
$available_times = [];
foreach ($all_time_slots as $time_value => $time_label) {
    if (!in_array($time_value, $booked_times)) {
        $available_times[$time_value] = $time_label;
    }
}

// Return available time slots
echo json_encode([
    'success' => true, 
    'date' => $date,
    'available_times' => $available_times
]);

// Close database connection
mysqli_close($conn);
?>