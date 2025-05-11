<?php
// update-appointment-status.php - for admin use to update appointment status

// Start session 
session_start();

// Simple admin check (you may have a more robust check)
// TODO: Add proper admin authentication here
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit();
}

// Load PHPMailer for email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Validate input data
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$appointment_id = mysqli_real_escape_string($conn, $_POST['id']);
$new_status = mysqli_real_escape_string($conn, $_POST['status']);

// Validate status 
$valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Get the booking details first (needed for email)
$booking_query = "SELECT b.*, u.first_name, u.last_name, u.email 
                  FROM bookings b 
                  JOIN users u ON b.user_id = u.id 
                  WHERE b.id = ?";
$stmt = $conn->prepare($booking_query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    exit();
}

$booking_data = $result->fetch_assoc();

// Update appointment status
$update_query = "UPDATE bookings SET status = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("si", $new_status, $appointment_id);

if ($update_stmt->execute()) {
    // If the appointment is confirmed, send confirmation email to client
    if ($new_status == 'confirmed') {
        sendConfirmationEmail($booking_data);
    } elseif ($new_status == 'cancelled') {
        sendCancellationEmail($booking_data);
    } elseif ($new_status == 'completed') {
        sendCompletionEmail($booking_data);
    }
    
    echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update appointment status: ' . $update_stmt->error]);
}

// Function to send confirmation email
function sendConfirmationEmail($booking_data) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jcalleja.k12043059@umak.edu.ph';
        $mail->Password = 'pjcu jxec zzbc rbso';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('jcalleja.k12043059@umak.edu.ph', 'Nail Architect');
        $mail->addAddress($booking_data['email']);
        $mail->isHTML(true);
        
        // Format date and time
        $appointment_date = date('l, F j, Y', strtotime($booking_data['date']));
        $appointment_time = date('g:i A', strtotime($booking_data['time']));
        $service_name = ucfirst(str_replace('-', ' ', $booking_data['service']));
        
        // Calculate days until appointment
        $date1 = new DateTime('now');
        $date2 = new DateTime($booking_data['date']);
        $interval = $date1->diff($date2);
        $days_until = $interval->days;
        
        $mail->Subject = "Your Nail Architect Appointment is Confirmed";
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
            </div>
            
            <h2 style="color: #ae9389; text-align: center;">Appointment Confirmation</h2>
            
            <p>Hello ' . $booking_data['first_name'] . ',</p>
            
            <p>Great news! Your appointment at Nail Architect has been <strong>confirmed</strong>.</p>
            
            <div style="background-color: #f2e9e9; padding: 15px; border-radius: 10px; margin: 20px 0;">
                <p><strong>Service:</strong> ' . $service_name . '</p>
                <p><strong>Date:</strong> ' . $appointment_date . ' (' . $days_until . ' days from now)</p>
                <p><strong>Time:</strong> ' . $appointment_time . '</p>
                <p><strong>Technician:</strong> ' . $booking_data['technician'] . '</p>
                <p><strong>Reference:</strong> #' . $booking_data['reference_id'] . '</p>
            </div>
            
            <p>Please arrive 5-10 minutes before your scheduled time. If you need to reschedule or cancel, please do so at least 24 hours in advance.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="http://localhost/nailarchitect/members-lounge.php" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">View Your Appointment</a>
            </div>
            
            <p>We look forward to seeing you!</p>
            
            <p>Warm regards,<br>The Nail Architect Team</p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
            </div>
        </div>';
        
        $mail->AltBody = "Hello " . $booking_data['first_name'] . ",\n\n" .
                      "Great news! Your appointment at Nail Architect has been confirmed.\n\n" .
                      "Service: " . $service_name . "\n" .
                      "Date: " . $appointment_date . " (" . $days_until . " days from now)\n" .
                      "Time: " . $appointment_time . "\n" .
                      "Technician: " . $booking_data['technician'] . "\n" .
                      "Reference: #" . $booking_data['reference_id'] . "\n\n" .
                      "Please arrive 5-10 minutes before your scheduled time.\n\n" .
                      "We look forward to seeing you!\n\n" .
                      "The Nail Architect Team";
        
        $mail->send();
        error_log("Confirmation email sent to " . $booking_data['email'] . " for booking #" . $booking_data['reference_id']);
        return true;
    } catch (Exception $e) {
        error_log("Failed to send confirmation email to " . $booking_data['email'] . ": " . $mail->ErrorInfo);
        return false;
    }
}

// Function to send cancellation email
function sendCancellationEmail($booking_data) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jcalleja.k12043059@umak.edu.ph';
        $mail->Password = 'pjcu jxec zzbc rbso';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('jcalleja.k12043059@umak.edu.ph', 'Nail Architect');
        $mail->addAddress($booking_data['email']);
        $mail->isHTML(true);
        
        // Format date and time
        $appointment_date = date('l, F j, Y', strtotime($booking_data['date']));
        $appointment_time = date('g:i A', strtotime($booking_data['time']));
        $service_name = ucfirst(str_replace('-', ' ', $booking_data['service']));
        
        $mail->Subject = "Your Nail Architect Appointment Status Update";
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
            </div>
            
            <h2 style="color: #ae9389; text-align: center;">Appointment Cancelled</h2>
            
            <p>Hello ' . $booking_data['first_name'] . ',</p>
            
            <p>We regret to inform you that your appointment has been <strong>cancelled</strong>.</p>
            
            <div style="background-color: #f2e9e9; padding: 15px; border-radius: 10px; margin: 20px 0;">
                <p><strong>Service:</strong> ' . $service_name . '</p>
                <p><strong>Date:</strong> ' . $appointment_date . '</p>
                <p><strong>Time:</strong> ' . $appointment_time . '</p>
                <p><strong>Reference:</strong> #' . $booking_data['reference_id'] . '</p>
            </div>
            
            <p>If you would like to book a new appointment, please visit our website or contact us.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="http://localhost/nailarchitect/booking.php" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">Book New Appointment</a>
            </div>
            
            <p>We apologize for any inconvenience this may have caused.</p>
            
            <p>Warm regards,<br>The Nail Architect Team</p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
            </div>
        </div>';
        
        $mail->send();
        error_log("Cancellation email sent to " . $booking_data['email'] . " for booking #" . $booking_data['reference_id']);
        return true;
    } catch (Exception $e) {
        error_log("Failed to send cancellation email to " . $booking_data['email'] . ": " . $mail->ErrorInfo);
        return false;
    }
}

// Function to send completion email
function sendCompletionEmail($booking_data) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jcalleja.k12043059@umak.edu.ph';
        $mail->Password = 'pjcu jxec zzbc rbso';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('jcalleja.k12043059@umak.edu.ph', 'Nail Architect');
        $mail->addAddress($booking_data['email']);
        $mail->isHTML(true);
        
        // Format date and time
        $appointment_date = date('l, F j, Y', strtotime($booking_data['date']));
        $appointment_time = date('g:i A', strtotime($booking_data['time']));
        $service_name = ucfirst(str_replace('-', ' ', $booking_data['service']));
        
        $mail->Subject = "Thank You for Visiting Nail Architect";
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
            </div>
            
            <h2 style="color: #ae9389; text-align: center;">Thank You for Your Visit</h2>
            
            <p>Hello ' . $booking_data['first_name'] . ',</p>
            
            <p>Thank you for visiting Nail Architect! We hope you enjoyed your service with us.</p>
            
            <div style="background-color: #f2e9e9; padding: 15px; border-radius: 10px; margin: 20px 0;">
                <p><strong>Service:</strong> ' . $service_name . '</p>
                <p><strong>Date:</strong> ' . $appointment_date . '</p>
                <p><strong>Reference:</strong> #' . $booking_data['reference_id'] . '</p>
            </div>
            
            <p>We would love to hear about your experience. If you have a moment, please rate your visit or share your feedback with us.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="http://localhost/nailarchitect/booking.php" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">Book Another Appointment</a>
            </div>
            
            <p>We look forward to seeing you again soon!</p>
            
            <p>Warm regards,<br>The Nail Architect Team</p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
            </div>
        </div>';
        
        $mail->send();
        error_log("Completion email sent to " . $booking_data['email'] . " for booking #" . $booking_data['reference_id']);
        return true;
    } catch (Exception $e) {
        error_log("Failed to send completion email to " . $booking_data['email'] . ": " . $mail->ErrorInfo);
        return false;
    }
}

// Close database connection
mysqli_close($conn);
?>