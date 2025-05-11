<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <title>Nail Architect - Thank You for Booking</title>
    <style>
          @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #f2e9e9;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 800px;
            width: 100%;
            flex: 1;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-height: 80vh;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            width: 100%;
        }
        
        .thank-you-container {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            animation: scaleIn 0.6s ease-out forwards;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #d9bbb0;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            animation: scaleIn 0.7s ease-out forwards;
        }
        
        .thank-you-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        .thank-you-message {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            animation: fadeIn 0.9s ease-out forwards;
        }
        
        .booking-details {
            background-color: #f0f0f0;
            border-radius: 10px;
            padding: 20px;
            text-align: left;
            margin-bottom: 30px;
            animation: slideUp 1s ease-out forwards;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        
        .detail-value {
            text-align: right;
        }
        
        .next-steps {
            margin-bottom: 30px;
            animation: fadeIn 1.1s ease-out forwards;
        }
        
        .next-steps-title {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .steps-list {
            text-align: left;
            padding-left: 20px;
        }
        
        .steps-list li {
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            animation: fadeIn 1.2s ease-out forwards;
        }
        
        .action-button {
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .primary-button {
            background-color: #d9bbb0;
            color: #000;
        }
        
        .primary-button:hover {
            background-color: #ae9389;
            transform: translateY(-2px);
        }
        
        .secondary-button {
            background-color: transparent;
            border: 1px solid #d9bbb0;
            color: #000;
        }
        
        .secondary-button:hover {
            background-color: #d9bbb0;
            transform: translateY(-2px);
        }
        
        .footer-message {
            margin-top: 30px;
            font-size: 14px;
            color: #666;
            animation: fadeIn 1.3s ease-out forwards;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .thank-you-container {
                padding: 30px 20px;
            }
            
            .button-group {
                flex-direction: column;
                width: 100%;
            }
            
            .action-button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-container">
                <div class="logo">
                    <img src="Assets/logo.png" alt="Nail Architect Logo">
                </div>
            </div>
            <div class="nav-links">
                <div class="nav-link">Services</div>
                <div class="book-now">Book Now</div>
                <div class="login-icon"></div>
            </div>
        </header>
        
        <div class="thank-you-container">
            <div class="success-icon">✓</div>
            
            <div class="thank-you-title">Thank You for Your Booking Request!</div>
            
            <div class="thank-you-message">
                We've received your appointment request and are processing it. Our team will review your preferred schedule and contact you shortly to confirm your appointment.
            </div>
            
            <div class="booking-details">
                <div class="detail-row">
                    <div class="detail-label">Booking Reference:</div>
                    <div class="detail-value">#NAI-2025042</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Service:</div>
                    <div class="detail-value">Gel Manicure</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Requested Date:</div>
                    <div class="detail-value">April 8, 2025</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Requested Time:</div>
                    <div class="detail-value">2:00 PM</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Deposit:</div>
                    <div class="detail-value">P500 (Paid)</div>
                </div>
            </div>
            
            <div class="next-steps">
                <div class="next-steps-title">What Happens Next?</div>
                <ol class="steps-list">
                    <li>Our team will check availability for your requested date and time.</li>
                    <li>You'll receive a confirmation email and text message within 2 hours during business hours.</li>
                    <li>If your requested time is not available, we'll contact you with alternative options.</li>
                    <li>Your deposit is fully transferable to another date if needed.</li>
                </ol>
            </div>
            
            <div class="button-group">
                <a href="members-lounge.html" class="action-button primary-button">View Your Booking</a>
                <a href="index.html" class="action-button secondary-button">Return to Home</a>
            </div>
            
            <div class="footer-message">
                If you have any questions, please contact us at (0932) 432 3142 or email hello@nailarchitect.com
            </div>
        </div>
    </div>
</body>
</html>
