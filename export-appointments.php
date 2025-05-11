<?php
// Start session and database connection
session_start();
$conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get filter parameter if any
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the query based on filter
$where_clause = "";
if ($status_filter != 'all') {
    $status_filter = mysqli_real_escape_string($conn, $status_filter);
    $where_clause = "WHERE b.status = '$status_filter'";
}

// Get appointments with user information
$appointments_query = "SELECT b.*, u.first_name, u.last_name 
                       FROM bookings b 
                       LEFT JOIN users u ON b.user_id = u.id 
                       $where_clause
                       ORDER BY b.date ASC, b.time ASC";
$appointments = mysqli_query($conn, $appointments_query);

// Include FPDF directly
require('fpdf/fpdf186/fpdf.php');

// Create PDF file
$pdf = new FPDF();
$pdf->AddPage();

// Set up page header
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(0, 10, 'Nail Architect - Appointments Report', 0, 1, 'C');
$pdf->Cell(0, 10, 'Generated on ' . date('F j, Y'), 0, 1, 'C');
$pdf->Ln(10);

// Add headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Client', 1);
$pdf->Cell(40, 10, 'Service', 1);
$pdf->Cell(40, 10, 'Date & Time', 1);
$pdf->Cell(30, 10, 'Status', 1);
$pdf->Cell(20, 10, 'Price', 1);
$pdf->Ln();

// Add data rows
$pdf->SetFont('Arial', '', 10);
while ($appointment = mysqli_fetch_assoc($appointments)) {
    // Format client name
    $client = $appointment['user_id'] ? 
              $appointment['first_name'] . ' ' . $appointment['last_name'] : 
              $appointment['name'];
    
    // Format service name
    $service = ucfirst(str_replace('-', ' ', $appointment['service']));
    
    // Format date and time
    $date_time = date('M j, Y', strtotime($appointment['date'])) . ' - ' .
                 date('g:i A', strtotime($appointment['time']));
    
    // Format status
    $status = ucfirst($appointment['status']);
    
    // Add row to PDF
    $pdf->Cell(60, 10, $client, 1);
    $pdf->Cell(40, 10, $service, 1);
    $pdf->Cell(40, 10, $date_time, 1);
    $pdf->Cell(30, 10, $status, 1);
    $pdf->Cell(20, 10, 'P' . number_format($appointment['price'], 2), 1);
    $pdf->Ln();
}

// Add page footer
$pdf->SetY(-15);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, 'Page ' . $pdf->PageNo(), 0, 0, 'C');

// Output PDF
$pdf->Output('D', 'Nail_Architect_Appointments.pdf');

// Close database connection
mysqli_close($conn);
?>