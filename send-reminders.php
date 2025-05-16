<?php
// send-reminders.php - Script to send reminders for tomorrow's appointments
// Set up as a cron job to run once daily, e.g., at midnight

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
error_log("Running reminder script at " . date('Y-m-d H:i:s'));

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    error_log("Reminder system - DB connection failed: " . mysqli_connect_error());
    die("Database connection failed");
}

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Get appointments scheduled for tomorrow
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$query = "SELECT b.*, u.first_name, u.last_name, u.email 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          WHERE b.date = '$tomorrow' 
          AND b.status = 'confirmed'";

$result = mysqli_query($conn, $query);

if (!$result) {
    error_log("Query error: " . mysqli_error($conn));
    die("Query error");
}

$count = mysqli_num_rows($result);
error_log("Found $count appointments for tomorrow ($tomorrow)");

// Send reminders for each appointment
$emails_sent = 0;
while ($appointment = mysqli_fetch_assoc($result)) {
    // Send reminder email
    if (sendReminderEmail($appointment)) {
        $emails_sent++;
    }
}

error_log("Sent $emails_sent reminder emails successfully");

// Function to send reminder email
function sendReminderEmail($appointment) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nailarchitect.glamhub@gmail.com';
        $mail->Password = 'xvft ygzc fijz vmth';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        // Recipients
        $mail->setFrom('nailarchitect.glamhub@gmail.com', 'Nail Architect');
        $mail->addAddress($appointment['email'], $appointment['first_name'] . ' ' . $appointment['last_name']);
        
        // Format appointment details
        $appointment_date = date('l, F j, Y', strtotime($appointment['date']));
        $appointment_time = date('g:i A', strtotime($appointment['time']));
        $service_name = ucfirst(str_replace('-', ' ', $appointment['service']));
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Reminder: Your Nail Architect Appointment Tomorrow";
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
            </div>
            
            <h2 style="color: #ae9389; text-align: center;">Appointment Reminder</h2>
            
            <p>Hello ' . $appointment['first_name'] . ',</p>
            
            <p>This is a friendly reminder that you have an appointment <strong>tomorrow</strong> at Nail Architect.</p>
            
            <div style="background-color: #f2e9e9; padding: 15px; border-radius: 10px; margin: 20px 0;">
                <p><strong>Service:</strong> ' . $service_name . '</p>
                <p><strong>Date:</strong> ' . $appointment_date . ' (tomorrow)</p>
                <p><strong>Time:</strong> ' . $appointment_time . '</p>
                <p><strong>Technician:</strong> ' . $appointment['technician'] . '</p>
                <p><strong>Reference:</strong> #' . $appointment['reference_id'] . '</p>
            </div>
            
            <p>Please arrive 5-10 minutes before your scheduled appointment time. If you need to reschedule or cancel, please contact us as soon as possible.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="http://localhost/nailarchitect/members-lounge.php" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">View Your Appointment</a>
            </div>
            
            <p>We look forward to seeing you tomorrow!</p>
            
            <p>Warm regards,<br>The Nail Architect Team</p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
            </div>
        </div>';
        
        $mail->AltBody = "Hello " . $appointment['first_name'] . ",\n\n" .
                       "This is a friendly reminder that you have an appointment tomorrow at Nail Architect.\n\n" .
                       "Service: " . $service_name . "\n" .
                       "Date: " . $appointment_date . " (tomorrow)\n" .
                       "Time: " . $appointment_time . "\n" .
                       "Technician: " . $appointment['technician'] . "\n" .
                       "Reference: #" . $appointment['reference_id'] . "\n\n" .
                       "Please arrive 5-10 minutes before your scheduled time.\n\n" .
                       "We look forward to seeing you tomorrow!\n\n" .
                       "The Nail Architect Team";
        
        $mail->send();
        
        // Log success
        error_log("Reminder email sent to " . $appointment['email'] . " for appointment #" . $appointment['reference_id']);
        
        // Update database to track that reminder was sent
        global $conn;
        $appointment_id = $appointment['id'];
        $update_query = "UPDATE bookings SET reminder_sent = 1 WHERE id = $appointment_id";
        mysqli_query($conn, $update_query);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to send reminder email to " . $appointment['email'] . ": " . $mail->ErrorInfo);
        return false;
    }
}

// Close database connection
mysqli_close($conn);

echo "Reminder process completed. $emails_sent reminder emails sent.";
?>