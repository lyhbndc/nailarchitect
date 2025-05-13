<?php
// Start session
session_start();

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);

// If logged in, get the first letter of the user's first name for the avatar
if ($logged_in) {
    $first_letter = substr($_SESSION['user_name'], 0, 1);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="bg-gradient.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Nail Architect - Services</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        html,
        body {
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
            max-width: 1500px;
            width: 100%;
            flex: 1;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
        }

        .page-title {
            font-size: 24px;
            margin-bottom: 10px;
            animation: fadeIn 0.5s ease-out forwards;
        }

        .page-subtitle {
            font-size: 16px;
            margin-bottom: 25px;
            color: #666;
            animation: fadeIn 0.6s ease-out forwards;
        }

        .services-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            animation: fadeIn 0.7s ease-out forwards;
        }

        .service-card {
            background: linear-gradient(to right, rgb(245, 213, 213), rgb(239, 180, 180));
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .service-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .service-content {
            padding: 25px;
        }

        .service-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .service-description {
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            color: #333;
        }

        .service-price {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .service-time {
            font-size: 14px;
            color: #666;
        }

        .back-button {
            display: inline-block;
            margin-top: 30px;
            font-size: 14px;
            cursor: pointer;
            position: relative;
            animation: fadeIn 0.9s ease-out forwards;
            color: #000;
        }

        .back-button:after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: #000;
            transition: width 0.6s ease;
        }

        .back-button:hover:after {
            width: 100%;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .services-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="gradient-overlay"></div>
    <div class="background-pattern"></div>
    <div class="swirl-pattern"></div>
    <div class="polish-drips"></div>
    <div class="container">
        <header>
            <div class="logo-container">
                <div class="logo">
                    <a href="index.php">
                        <img src="Assets/logo.png" alt="Nail Architect Logo">
                    </a>
                </div>
            </div>
            <div class="nav-links">
                <div class="nav-link">Services</div>
                <div class="book-now">Book Now</div>
                <?php if ($logged_in): ?>
                    <div class="user-initial"><?php echo $first_letter; ?></div>
                <?php else: ?>
                    <div class="login-icon"><i class="fa fa-user"></i></div>
                <?php endif; ?>
            </div>
        </header>

        <a href="index.php">
            <div class="back-button">← Back</div>
        </a>
        <div class="page-title">Our Services</div>
        <div class="page-subtitle">Choose from our range of premium nail care services</div>

        <div class="services-container">
            <div class="service-card">
                <img src="Assets/softgel.jpg" alt="Soft Gel" class="service-image">
                <div class="service-content">
                    <div class="service-title">Soft Gel</div>
                    <div class="service-description">Flexible, natural-looking enhancement that adds strength without damage to natural nails.</div>
                    <div class="service-price">starts at P800</div>
                    <div class="service-time">30 mins - 1 hour</div>
                </div>
            </div>

            <div class="service-card">
                <img src="Assets/pressons.jpg" alt="Press Ons" class="service-image">
                <div class="service-content">
                    <div class="service-title">Press Ons</div>
                    <div class="service-description">Quick-apply artificial nails in various designs for instant style transformation.</div>
                    <div class="service-price">starts at P300</div>
                    <div class="service-time"></div>
                </div>
            </div>

            <div class="service-card">
                <img src="Assets/builder.jpg" alt="Builder Gel" class="service-image">
                <div class="service-content">
                    <div class="service-title">Builder Gel</div>
                    <div class="service-description">Strong structural enhancement that builds and protects natural nails from breaking.</div>
                    <div class="service-price">P750 - 2000</div>
                    <div class="service-time">30 mins - 1 hour</div>
                </div>
            </div>

            <div class="service-card">
                <img src="Assets/menicure.jpg" alt="Menicure" class="service-image">
                <div class="service-content">
                    <div class="service-title">Menicure</div>
                    <div class="service-description">Professional nail care including shaping, buffing, cuticle treatment and polish.</div>
                    <div class="service-price">starts at P400</div>
                    <div class="service-time">30 mins - 1 hour</div>
                </div>
            </div>

            <div class="service-card">
                <img src="Assets/removal.jpg" alt="Removal" class="service-image">
                <div class="service-content">
                    <div class="service-title">Removal / Fill</div>
                    <div class="service-description">Safe removal of previous enhancements or maintenance fills for grown-out nails.</div>
                    <div class="service-price">P150 - 1200</div>
                    <div class="service-time">30 mins - 1 hour</div>
                </div>
            </div>

            <div class="service-card">
                <img src="Assets/other.jpg" alt="Other Services" class="service-image">
                <div class="service-content">
                    <div class="service-title">Other Services</div>
                    <div class="service-description">Home Service and Event Packages</div>
                    <div class="service-price">price varies</div>
                    <div class="service-time"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle navigation
            const backButton = document.querySelector('.back-button');
            backButton.addEventListener('click', function() {
                window.location.href = 'index.php';
            });

            const logo = document.querySelector('.logo');
            logo.addEventListener('click', function() {
                window.location.href = 'index.php';
            });

            const bookNow = document.querySelector('.book-now');
            bookNow.addEventListener('click', function() {
                window.location.href = 'booking.php';
            });

            <?php if ($logged_in): ?>
                const userInitial = document.querySelector('.user-initial');
                userInitial.addEventListener('click', function() {
                    window.location.href = 'members-lounge.php';
                });
            <?php else: ?>
                const loginIcon = document.querySelector('.login-icon');
                loginIcon.addEventListener('click', function() {
                    window.location.href = 'login.php';
                });
            <?php endif; ?>

            const servicesLink = document.querySelector('.nav-link');
            servicesLink.addEventListener('click', function() {
                window.location.href = 'services.php';
            });
        });
    </script>
    <?php include 'chat-widget.php'; ?>
</body>

</html>