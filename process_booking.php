<?php
// Start session
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Get form data
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : NULL;
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $service = mysqli_real_escape_string($conn, $_POST['service']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : NULL;
    
    // Set defaults based on service
    $technician = "TBD";
    $duration = 0;
    $price = 0;
    
    // Set duration and price based on service
    switch ($service) {
        case 'soft-gel':
            $duration = 60;
            $price = 800;
            break;
        case 'press-ons':
            $duration = 45;
            $price = 300;
            break;
        case 'builder-gel':
            $duration = 60;
            $price = 750;
            break;
        case 'menicure':
            $duration = 45;
            $price = 400;
            break;
        case 'removal-fill':
            $duration = 30;
            $price = 150;
            break;
        case 'other':
            $duration = 60;
            $price = 500;
            break;
    }
    
    // Generate unique reference ID
    $reference_id = 'NAI-' . rand(1000000, 9999999);
    
    // Insert booking data
// Insert booking data
$query = "INSERT INTO bookings (user_id, name, email, phone, service, date, time, notes, technician, duration, price, reference_id, status) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($query);
$stmt->bind_param("issssssssiis", $user_id, $name, $email, $phone, $service, $date, $time, $notes, $technician, $duration, $price, $reference_id);

    if ($stmt->execute()) {
          $booking_id = $conn->insert_id;
        
        // Handle payment proof upload
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
            $upload_dir = "uploads/payments/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = $reference_id . '_' . basename($_FILES['payment_proof']['name']);
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
                // Insert payment proof record
                $payment_query = "INSERT INTO payment_proofs (booking_id, image_path) VALUES (?, ?)";
                $payment_stmt = $conn->prepare($payment_query);
                $payment_stmt->bind_param("is", $booking_id, $target_file);
                $payment_stmt->execute();
            }
        }
        
        // Handle inspiration images uploads
        if (isset($_FILES['nail_inspo']) && !empty($_FILES['nail_inspo']['name'][0])) {
            $upload_dir = "uploads/inspirations/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $total_files = count($_FILES['nail_inspo']['name']);
            
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['nail_inspo']['error'][$i] == 0) {
                    $file_name = $reference_id . '_' . $i . '_' . basename($_FILES['nail_inspo']['name'][$i]);
                    $target_file = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['nail_inspo']['tmp_name'][$i], $target_file)) {
                        // Insert inspiration image record
                        $image_query = "INSERT INTO booking_images (booking_id, image_path) VALUES (?, ?)";
                        $image_stmt = $conn->prepare($image_query);
                        $image_stmt->bind_param("is", $booking_id, $target_file);
                        $image_stmt->execute();
                    }
                }
            }
        }
        
        // Redirect to confirmation page
        $_SESSION['booking_success'] = true;
        $_SESSION['reference_id'] = $reference_id;
        header("Location: booking_confirmation.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    
    // Close database connection
    $conn->close();
}
?>