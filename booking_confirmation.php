<?php
session_start();

// Check if booking was successful
if (!isset($_SESSION['booking_success']) || !$_SESSION['booking_success']) {
    header("Location: booking.php");
    exit();
}

$reference_id = $_SESSION['reference_id'];

// Clear the session variables
unset($_SESSION['booking_success']);
unset($_SESSION['reference_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="icon" type="image/png" href="Assets/favicon1.png">
    <title>Booking Confirmation - Nail Architect</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }
        
        body {
            background-color: #f2e9e9;
            padding: 20px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        
        .confirmation-card {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .success-icon {
            font-size: 60px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }
        
        p {
            font-size: 16px;
            margin-bottom: 15px;
            color: #555;
        }
        
        .reference {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            padding: 10px 20px;
            background-color: #f2e9e9;
            display: inline-block;
            border-radius: 10px;
        }
        
        .buttons {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .button {
            padding: 12px 24px;
            background-color: #d9bbb0;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .button:hover {
            background-color: #ae9389;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-card">
            <div class="success-icon">✓</div>
            <h1>Booking Confirmed!</h1>
            <p>Thank you for your booking. We have received your request and payment.</p>
            <p>Your booking is currently <strong>pending</strong> and will be confirmed by our staff shortly.</p>
            <p>Your booking reference is:</p>
            <div class="reference"><?php echo $reference_id; ?></div>
            <p>Please save this reference number for your records. You can also view your booking details in your account.</p>
            <div class="buttons">
                <a href="index.php" class="button">Return to Home</a>
                <a href="members-lounge.php" class="button">View My Bookings</a>
            </div>
        </div>
    </div>
</body>
</html>