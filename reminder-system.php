an<?php
// reminder-system.php - Include this at the top of admin-dashboard.php

// Load PHPMailer - use your existing setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Function to check and send reminders if needed
function checkAndSendReminders() {
    // Path to store the last run timestamp
    $last_run_file = __DIR__ . '/last_reminder_run.txt';
    
    // Check if we already ran today
    if (file_exists($last_run_file)) {
        $last_run = file_get_contents($last_run_file);
        if ($last_run == date('Y-m-d')) {
            return; // Already ran today, exit function
        }
    }
    
    // Only run at certain times (e.g., after 6 PM) to avoid multiple emails
    // You can remove this condition if you want to run it anytime an admin logs in
    $current_hour = (int)date('H');
    if ($current_hour < 18) {
        return; // Only run after 6 PM
    }
    
    // Database connection
    $conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
    if (!$conn) {
        error_log("Reminder system - DB connection failed: " . mysqli_connect_error());
        return;
    }
    
    // Get appointments scheduled for tomorrow
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $query = "SELECT b.*, u.first_name, u.last_name, u.email 
              FROM bookings b 
              JOIN users u ON b.user_id = u.id 
              WHERE b.date = '$tomorrow' 
              AND b.status = 'confirmed'";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Reminder system - Query error: " . mysqli_error($conn));
        mysqli_close($conn);
        return;
    }
    
    // If no appointments found, mark as run and exit
    if (mysqli_num_rows($result) == 0) {
        file_put_contents($last_run_file, date('Y-m-d'));
        mysqli_close($conn);
        return;
    }
    
    // Keep track of how many emails were sent
    $emails_sent = 0;
    
    // Send email to each client with an appointment tomorrow
    while ($appointment = mysqli_fetch_assoc($result)) {
        // Format time and date for display
        $appointment_time = date('g:i A', strtotime($appointment['time']));
        $appointment_date = date('l, F j, Y', strtotime($appointment['date']));
        
        // Format service name
        $service_name = ucfirst(str_replace('-', ' ', $appointment['service']));
        
        try {
            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);
            
            // Use the same SMTP settings from your sign-up.php
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jcalleja.k12043059@umak.edu.ph';
            $mail->Password = 'pjcu jxec zzbc rbso';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            
            // Recipients
            $mail->setFrom('jcalleja.k12043059@umak.edu.ph', 'Nail Architect');
            $mail->addAddress($appointment['email'], $appointment['first_name'] . ' ' . $appointment['last_name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "Reminder: Your Nail Architect Appointment Tomorrow";
            
            // Create HTML email body - matching your brand style from sign-up.php
            $body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e8d7d0; border-radius: 10px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #ae9389; margin: 0;">Nail Architect</h1>
                </div>
                
                <h2 style="color: #ae9389; text-align: center;">Appointment Reminder</h2>
                
                <p>Hello ' . $appointment['first_name'] . ',</p>
                
                <p>This is a friendly reminder about your appointment <strong>tomorrow</strong> at Nail Architect.</p>
                
                <div style="background-color: #f2e9e9; padding: 15px; border-radius: 10px; margin: 20px 0;">
                    <p><strong>Service:</strong> ' . $service_name . '</p>
                    <p><strong>Date:</strong> ' . $appointment_date . '</p>
                    <p><strong>Time:</strong> ' . $appointment_time . '</p>
                    <p><strong>Technician:</strong> ' . $appointment['technician'] . '</p>
                    <p><strong>Reference:</strong> #NAI-' . $appointment['reference_id'] . '</p>
                </div>
                
                <p>Please arrive 5-10 minutes before your scheduled appointment time. If you need to reschedule or cancel, please log in to your account or contact us as soon as possible.</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="localhost/nailarchitect/members-lounge.php" style="background-color: #d9bbb0; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;">View Your Appointment</a>
                </div>
                
                <p>We look forward to seeing you tomorrow!</p>
                
                <p>Warm regards,<br>The Nail Architect Team</p>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8d7d0; text-align: center; font-size: 12px; color: #666;">
                    <p>&copy; ' . date('Y') . ' Nail Architect. All rights reserved.</p>
                    <p>6 Osmena St., TS Cruz Subdivision Novaliches, Quezon City</p>
                </div>
            </div>';
            
            $mail->Body = $body;
            
            // Plain text alternative
            $mail->AltBody = "Hello " . $appointment['first_name'] . ",\n\n" .
                             "This is a reminder about your appointment tomorrow at Nail Architect.\n\n" .
                             "Service: " . $service_name . "\n" .
                             "Date: " . $appointment_date . "\n" .
                             "Time: " . $appointment_time . "\n" .
                             "Technician: " . $appointment['technician'] . "\n" .
                             "Reference: #NAI-" . $appointment['reference_id'] . "\n\n" .
                             "We look forward to seeing you!\n\n" .
                             "The Nail Architect Team";
            
            // Send the email
            $mail->send();
            $emails_sent++;
            
            // Log success
            error_log("Reminder sent to " . $appointment['email'] . " for appointment #NAI-" . $appointment['reference_id']);
        
        } catch (Exception $e) {
            // Log error
            error_log("Failed to send reminder to " . $appointment['email'] . ": " . $mail->ErrorInfo);
        }
    }
    
    // Mark as run today to prevent duplicate emails
    file_put_contents($last_run_file, date('Y-m-d'));
    
    // Close database connection
    mysqli_close($conn);
    
    // Optional: Return the number of emails sent (for debugging)
    return $emails_sent;
}

// Call the function to check and send reminders
$reminders_sent = checkAndSendReminders();

// Optionally display a message in the admin area (only if emails were sent)
if (isset($reminders_sent) && $reminders_sent > 0) {
    // Add this message to admin dashboard or store in session to display later
    $reminder_message = "$reminders_sent appointment reminder(s) sent for tomorrow's appointments.";
}
?>