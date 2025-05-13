<?php
// get-inquiry-details.php - API endpoint to fetch inquiry details for modal
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Invalid request');
}

$inquiry_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get inquiry details
$inquiry_query = "SELECT * FROM inquiries WHERE id = '$inquiry_id'";
$inquiry_result = mysqli_query($conn, $inquiry_query);
$inquiry = mysqli_fetch_assoc($inquiry_result);

if (!$inquiry) {
    http_response_code(404);
    exit('Inquiry not found');
}

// Format date
$inquiry_date = new DateTime($inquiry['created_at']);
$formatted_date = $inquiry_date->format('F j, Y g:i A');
?>

<div class="inquiry-detail">
    <div>
        <div class="detail-label">Name:</div>
        <div class="detail-value"><?php echo htmlspecialchars($inquiry['first_name'] . ' ' . $inquiry['last_name']); ?></div>
    </div>
    
    <div>
        <div class="detail-label">Email:</div>
        <div class="detail-value"><?php echo htmlspecialchars($inquiry['email']); ?></div>
    </div>
    
    <?php if (!empty($inquiry['phone'])): ?>
    <div>
        <div class="detail-label">Phone:</div>
        <div class="detail-value"><?php echo htmlspecialchars($inquiry['phone']); ?></div>
    </div>
    <?php endif; ?>
    
    <div>
        <div class="detail-label">Subject:</div>
        <div class="detail-value"><?php echo htmlspecialchars($inquiry['subject']); ?></div>
    </div>
    
    <div>
        <div class="detail-label">Date Received:</div>
        <div class="detail-value"><?php echo $formatted_date; ?></div>
    </div>
    
    <div>
        <div class="detail-label">Status:</div>
        <div class="detail-value">
            <span class="status-badge <?php echo $inquiry['status']; ?>">
                <?php echo ucfirst($inquiry['status']); ?>
            </span>
        </div>
    </div>
    
    <div>
        <div class="detail-label">Message:</div>
        <div class="message-box"><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></div>
    </div>
</div>

<div style="margin-top: 25px; text-align: center;">
    <a href="mailto:<?php echo $inquiry['email']; ?>?subject=Re: <?php echo urlencode($inquiry['subject']); ?>&body=Dear <?php echo urlencode($inquiry['first_name']); ?>,%0A%0AThank you for your inquiry about <?php echo urlencode($inquiry['subject']); ?>.%0A%0A" 
       class="action-btn email" 
       style="background-color: #e8f5e9; color: #2e7d32; padding: 10px 25px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;"
       onclick="if(window.parent.markAsResponded) window.parent.markAsResponded(<?php echo $inquiry['id']; ?>)">
        <i class="fas fa-envelope"></i> Reply via Gmail
    </a>
</div>