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
$appointments_result = mysqli_query($conn, $appointments_query);

// Group appointments by date
$grouped_appointments = [];
while ($appointment = mysqli_fetch_assoc($appointments_result)) {
    $date = $appointment['date'];
    if (!isset($grouped_appointments[$date])) {
        $grouped_appointments[$date] = [];
    }
    $grouped_appointments[$date][] = $appointment;
}

// Include FPDF directly
require('fpdf/fpdf186/fpdf.php');

// Create custom PDF class for adding logo
class PDF extends FPDF {
    function Header() {
        // Look for logo image - works both locally and on web server
        $logo_path = '';
        
        // First, try relative paths (these work best for both local and web)
        if (file_exists('../Assets/logo.png')) {
            $logo_path = '../Assets/logo.png';
        } elseif (file_exists('./Assets/logo.png')) {
            $logo_path = './Assets/logo.png';
        } elseif (file_exists('Assets/logo.png')) {
            $logo_path = 'Assets/logo.png';
        } 
        // For web server, use document root
        elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/Assets/logo.png')) {
            $logo_path = $_SERVER['DOCUMENT_ROOT'] . '/Assets/logo.png';
        } elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/nailarchitect/Assets/logo.png')) {
            $logo_path = $_SERVER['DOCUMENT_ROOT'] . '/nailarchitect/Assets/logo.png';
        } 
        // Use dirname to get parent directory
        elseif (file_exists(dirname(__FILE__) . '/../Assets/logo.png')) {
            $logo_path = dirname(__FILE__) . '/../Assets/logo.png';
        }
        // Fallback to local development paths
        elseif (file_exists('D:/xampp/htdocs/nailarchitect/Assets/logo.png')) {
            $logo_path = 'D:/xampp/htdocs/nailarchitect/Assets/logo.png';
        } elseif (file_exists('/xampp/htdocs/nailarchitect/Assets/logo.png')) {
            $logo_path = '/xampp/htdocs/nailarchitect/Assets/logo.png';
        }
        
        // If logo is found, display it
        if ($logo_path != '') {
            // Get image dimensions
            list($width, $height) = getimagesize($logo_path);
            $ratio = $width / $height;
            
            // Set logo dimensions (height = 40, width proportional)
            $logo_height = 40;
            $logo_width = $logo_height * $ratio;
            
            // Limit width to prevent overflow
            if ($logo_width > 100) {
                $logo_width = 100;
                $logo_height = $logo_width / $ratio;
            }
            
            // Center the logo horizontally
            $x_position = ($this->GetPageWidth() - $logo_width) / 2;
            
            // Add the logo
            $this->Image($logo_path, $x_position, 10, $logo_width, $logo_height);
            
            // Move cursor below logo
            $this->Ln($logo_height + 15);
        } else {
            // Fallback: drawn logo if image not found
            $this->SetFillColor(224, 197, 183); // #e0c5b7
            $this->Rect(85, 10, 40, 40, 'F');
            
            // Add a smaller circle offset for design effect
            $this->SetFillColor(220, 220, 220); // #dcdcdc
            $this->Rect(115, 40, 20, 20, 'F');
            
            // Add spa icon representation
            $this->SetFont('Arial', 'B', 20);
            $this->SetTextColor(51, 51, 51);
            $this->SetXY(90, 20);
            $this->Cell(30, 30, '~', 0, 0, 'C');
            
            // Company name
            $this->SetFont('Arial', 'B', 24);
            $this->SetTextColor(51, 51, 51);
            $this->SetY(60);
            $this->Cell(0, 10, 'Nail Architect', 0, 1, 'C');
            
            // Tagline
            $this->SetFont('Arial', '', 14);
            $this->SetTextColor(102, 102, 102);
            $this->Cell(0, 10, 'Professional Nail Services', 0, 1, 'C');
            
            // Move down for content
            $this->Ln(10);
        }
        
        // Reset text color
        $this->SetTextColor(0, 0, 0);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' - Generated on ' . date('F j, Y'), 0, 0, 'C');
    }
}

// Create PDF file
$pdf = new PDF();
$pdf->AddPage();

// Report title
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(0, 10, 'Appointments Report', 0, 1, 'C');

// Filter information
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 8, 'Filter: ' . ucfirst($status_filter) . ' Appointments', 0, 1, 'C');
$pdf->Ln(10);

// Reset text color
$pdf->SetTextColor(0, 0, 0);

// Initialize statistics
$total_appointments = 0;
$total_revenue = 0;
$status_counts = [
    'confirmed' => 0,
    'pending' => 0,
    'cancelled' => 0,
    'completed' => 0
];

// Process appointments by date
foreach ($grouped_appointments as $date => $appointments_on_date) {
    // Check if need new page
    if ($pdf->GetY() > 240) {
        $pdf->AddPage();
    }
    
    // Date header
    $pdf->SetFillColor(217, 187, 176); // #D9BBB0
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, date('l, F j, Y', strtotime($date)), 0, 1, 'C', true);
    $pdf->Ln(2);
    
    // Table header
    $pdf->SetFillColor(232, 215, 208); // #E8D7D0
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 8, 'Time', 1, 0, 'C', true);
    $pdf->Cell(60, 8, 'Client', 1, 0, 'C', true);
    $pdf->Cell(60, 8, 'Service', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Status', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Ref #', 1, 1, 'C', true);
    
    // Data rows
    $pdf->SetFont('Arial', '', 9);
    foreach ($appointments_on_date as $appointment) {
        // Check for page break
        if ($pdf->GetY() > 260) {
            $pdf->AddPage();
            
            // Repeat date header on new page
            $pdf->SetFillColor(217, 187, 176);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, date('l, F j, Y', strtotime($date)) . ' (continued)', 0, 1, 'C', true);
            $pdf->Ln(2);
            
            // Repeat table header
            $pdf->SetFillColor(232, 215, 208);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(25, 8, 'Time', 1, 0, 'C', true);
            $pdf->Cell(60, 8, 'Client', 1, 0, 'C', true);
            $pdf->Cell(60, 8, 'Service', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Status', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Ref #', 1, 1, 'C', true);
            $pdf->SetFont('Arial', '', 9);
        }
        
        // Update statistics
        $total_appointments++;
        if (isset($status_counts[strtolower($appointment['status'])])) {
            $status_counts[strtolower($appointment['status'])]++;
        }
        if ($appointment['status'] != 'cancelled') {
            $total_revenue += $appointment['price'];
        }
        
        // Format data
        $time = date('g:i A', strtotime($appointment['time']));
        $client = $appointment['user_id'] ? 
                  $appointment['first_name'] . ' ' . $appointment['last_name'] : 
                  $appointment['name'];
        $service = ucfirst(str_replace('-', ' ', $appointment['service']));
        $status = ucfirst($appointment['status']);
        $reference = $appointment['reference_id'] ? '#' . $appointment['reference_id'] : 'N/A';
        
        // Set status color
        $pdf->SetFillColor(255, 255, 255);
        
        // Add row with consistent cell widths
        $pdf->Cell(25, 7, $time, 1, 0, 'L');
        $pdf->Cell(60, 7, substr($client, 0, 30), 1, 0, 'L');
        $pdf->Cell(60, 7, substr($service, 0, 30), 1, 0, 'L');
        
        // Status cell with color
        switch(strtolower($appointment['status'])) {
            case 'confirmed':
                $pdf->SetTextColor(46, 125, 50);
                break;
            case 'pending':
                $pdf->SetTextColor(245, 127, 23);
                break;
            case 'cancelled':
                $pdf->SetTextColor(198, 40, 40);
                break;
            case 'completed':
                $pdf->SetTextColor(97, 97, 97);
                break;
            default:
                $pdf->SetTextColor(0, 0, 0);
        }
        $pdf->Cell(30, 7, $status, 1, 0, 'C');
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->Cell(25, 7, substr($reference, 0, 13), 1, 1, 'L');
    }
    
    $pdf->Ln(5);
}

// Summary section
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Report Summary', 0, 1, 'C');
$pdf->Ln(5);

// Summary box
$pdf->SetFillColor(232, 215, 208); // #E8D7D0
$pdf->Rect(30, $pdf->GetY(), 150, 90, 'F');

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetXY(40, $pdf->GetY() + 10);
$pdf->Cell(65, 8, 'Total Appointments:', 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(65, 8, $total_appointments, 0, 1, 'R');

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetX(40);
$pdf->Cell(65, 8, 'Confirmed:', 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(65, 8, $status_counts['confirmed'], 0, 1, 'R');

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetX(40);
$pdf->Cell(65, 8, 'Pending:', 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(65, 8, $status_counts['pending'], 0, 1, 'R');

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetX(40);
$pdf->Cell(65, 8, 'Cancelled:', 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(65, 8, $status_counts['cancelled'], 0, 1, 'R');

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetX(40);
$pdf->Cell(65, 8, 'Completed:', 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(65, 8, $status_counts['completed'], 0, 1, 'R');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetX(40);
$pdf->Cell(65, 10, 'Total Revenue (excl. cancelled):', 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(65, 10, 'P' . number_format($total_revenue, 2), 0, 1, 'R');

// Note at bottom
$pdf->SetY(-40);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 5, "Note: Revenue calculation excludes cancelled appointments.\nThis report was generated from the Nail Architect management system.", 0, 'C');

// Output PDF
$filename = 'Nail_Architect_Appointments_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);

// Close database connection
mysqli_close($conn);
?>