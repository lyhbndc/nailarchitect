<?php
// Start session
session_start();

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);

// If logged in, get the first letter of the user's first name for the avatar
if ($logged_in) {
    $first_letter = substr($_SESSION['user_name'], 0, 1);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
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
    <title>Nail Architect</title>
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
            overflow-y: auto;
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
            position: relative;
            z-index: 1;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 25px;
            animation: fadeIn 0.5s ease-out forwards;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            grid-template-rows: repeat(2, auto);
            gap: 15px;
            flex: 1;
            animation: fadeIn 0.6s ease-out forwards;
        }

        .grid-item {
            background-color: #f5dbdb;
            border-radius: 25px;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .grid-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .main-card {
            grid-column: span 5;
            grid-row: span 7;
            min-height: 300px;
        }

        .book-appointment {
            grid-column: 6 / span 7;
            grid-row: span 3;
            min-height: 150px;
            background-color: #f5dbdb;
        }

        .gallery {
            grid-column: 6 / span 3;
            grid-row: 4 / span 4;
            min-height: 150px;
        }

        .contact {
            grid-column: 9 / span 4;
            grid-row: 4 / span 4;
            min-height: 150px;
            background-color: #f5dbdb;
        }

        .faq {
            grid-column: span 8;
            grid-row: 8 / span 5;
            min-height: 200px;
            background-color: #f5dbdb;
        }

        .visit-us {
            grid-column: 9 / span 4;
            grid-row: 8 / span 5;
            min-height: 200px;
        }

        .grid-img {
            width: 65%;
            /* Adjusted to not cover the entire container */
            height: auto;
            object-fit: contain;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
        }

        /* Special handling for specific layouts */
        .main-card .grid-img {
            width: 75%;
            height: auto;
            left: 40%;
            top: 60%;
            transform: translateY(-50%);
        }

        .visit-us .grid-img {
            width: 75%;
            height: auto;
            left: 40%;
            top: 50%;
            transform: translateY(-50%);
        }

        .book-appointment .grid-img {
            width: 60%;
            height: auto;
            left: 30%;
            top: 90%;
            transform: translateY(-40%);
        }

        .gallery .grid-img {
            width: 80%;
            height: auto;
            left: 35%;
            top: 65%;
            transform: translateY(-50%);

        }

        .contact .grid-img {
            width: 60%;
            height: auto;
            left: 1%;
            top: 60%;
            transform: translateY(-50%);
        }

        .faq .grid-img {
            width: 60%;
            height: auto;
            left: 20%;
            transform: translate(-50%, -50%);
        }

        .grid-content {
            position: relative;
            z-index: 2;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            color: #333;
        }

        .grid-title {
            font-weight: 600;
            font-size: 20px;
            margin-bottom: 5px;
            color: #333;
            position: relative;
            z-index: 5;
        }

        /* Positioning text for different layouts to match the design */
        .book-appointment .grid-title {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .gallery .grid-title {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .contact .grid-title {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        .faq .grid-title {
            position: absolute;
            bottom: 80px;
            right: 100px;
        }

        .visit-us .grid-title {
            position: absolute;
            bottom: 20px;
            left: 20px;
        }

        /* Main card special styling */
        .main-title {
            font-size: 22px;
            font-weight: 600;
            line-height: 1.3;
            position: absolute;
            top: 20px;
            left: 20px;
            max-width: 50%;
        }

        .card-action {
            position: absolute;
            left: 20px;
            bottom: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .main-card {
            background-color: #f5c7c7;
        }

        .book-appointment {
            background-color: rgb(255, 194, 196);
        }

        .gallery {
            background-color: #eb9c9d;
        }

        .contact {
            background-color: #ffc5cb;
        }

        .faq {
            background-color: #ffbcc2;
        }

        .visit-us {
            background-color: #ffdbdc;
        }

        /* Responsive styles */
        @media (max-width: 1024px) {
            .grid {
                grid-template-rows: repeat(3, auto);
            }

            .main-card {
                grid-column: span 12;
            }

            .book-appointment {
                grid-column: span 12;
                grid-row: 2 / span 1;
            }

            .gallery {
                grid-column: span 6;
                grid-row: 3 / span 1;
            }

            .contact {
                grid-column: 7 / span 6;
                grid-row: 3 / span 1;
            }

            .faq {
                grid-column: span 12;
                grid-row: 4 / span 1;
            }

            .visit-us {
                grid-column: span 12;
                grid-row: 5 / span 1;
            }

            /* Responsive title positions for tablet */
            .faq .grid-title {
                bottom: 120px;
                right: 50px;
            }

            .visit-us .grid-title {
                bottom: 20px;
                right: 20px;
            }
        }

        @media (max-width: 768px) {
            body {
                overflow-y: auto;
                height: auto;
                min-height: 100vh;
            }

            .container {
                height: auto;
                min-height: auto;
            }

            .grid {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .grid-item {
                min-height: 200px;
            }

            .main-card {
                min-height: 250px;
            }

            /* Responsive title positions for mobile */
            .main-title {
                font-size: 20px;
                max-width: 60%;
            }

            .grid-title {
                font-size: 18px;
            }

            .book-appointment .grid-title,
            .gallery .grid-title {
                top: 15px;
                left: 15px;
            }

            .contact .grid-title {
                top: 20px;
                right: 35px;
            }

            .faq .grid-title {
                position: absolute;
                top: 15px;
                left: 200px;
                bottom: auto;
                right: auto;
            }

            .visit-us .grid-title {
                position: absolute;
                top: 15px;
                left: 15px;
                bottom: auto;
                right: auto;
            }

            .card-action {
                position: absolute;
                top: 140px;
                left: 20px;
                bottom: auto;
                right: auto;
            }
        }

        /* Small mobile adjustments */
        @media (max-width: 480px) {
            .grid-item {
                min-height: 180px;
            }

            .main-title {
                font-size: 18px;
                max-width: 70%;
            }

            .grid-title {
                font-size: 16px;
            }

            /* Ensure we keep image positions but adjust titles */
            .main-card .grid-img,
            .book-appointment .grid-img,
            .gallery .grid-img,
            .contact .grid-img,
            .faq .grid-img,
            .visit-us .grid-img {
                /* Keep original image positions */
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

        <div class="grid">
            <div class="grid-item main-card">
                <img src="Assets/nailpolish.png" alt="Nail polish bottle" class="grid-img">
                <div class="grid-content">
                    <div class="main-title">
                        Your nails, your<br>
                        statement
                    </div>
                    <div class="card-action">View Services</div>
                </div>
            </div>

            <div class="grid-item book-appointment">
                <img src="Assets/calendar.png" alt="Calendar or appointment book" class="grid-img">
                <div class="grid-content">
                    <div class="grid-title">Book Appointment</div>
                </div>
            </div>

            <div class="grid-item gallery">
                <img src="Assets/polaroid.png" alt="Vintage camera" class="grid-img">
                <div class="grid-content">
                    <div class="grid-title">Gallery</div>
                </div>
            </div>

            <div class="grid-item contact">
                <img src="Assets/send.png" alt="Envelope or contact icon" class="grid-img">
                <div class="grid-content">
                    <div class="grid-title">Contact</div>
                </div>
            </div>

            <div class="grid-item faq">
                <img src="Assets/hana.png" alt="Flower decorations" class="grid-img">
                <div class="grid-content">
                    <div class="grid-title">Take a<br>Matchmaking Quiz</div>
                </div>
            </div>

            <div class="grid-item visit-us">
                <img src="Assets/faqs.png" alt="Question mark icon" class="grid-img">
                <div class="grid-content">
                    <div class="grid-title">FAQ</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle navigation
            const viewServices = document.querySelector('.card-action');
            viewServices.addEventListener('click', function() {
                window.location.href = 'services.php';
            });

            const servicesLink = document.querySelector('.nav-link');
            servicesLink.addEventListener('click', function() {
                window.location.href = 'services.php';
            });

            const services = document.querySelector('.main-card');
            services.addEventListener('click', function() {
                window.location.href = 'services.php';
            });

            const bookNow = document.querySelector('.book-now');
            bookNow.addEventListener('click', function() {
                window.location.href = 'booking.php';
            });

            const bookAppointment = document.querySelector('.book-appointment');
            bookAppointment.addEventListener('click', function() {
                window.location.href = 'booking.php';
            });

            const gallery = document.querySelector('.gallery');
            gallery.addEventListener('click', function() {
                window.location.href = 'gallery.php';
            });

            const contact = document.querySelector('.contact');
            contact.addEventListener('click', function() {
                window.location.href = 'get-in-touch-page.php';
            });

            const faq = document.querySelector('.faq');
            faq.addEventListener('click', function() {
                window.location.href = 'matchmaking.php';
            });

            const visitUs = document.querySelector('.visit-us');
            visitUs.addEventListener('click', function() {
                window.location.href = 'faq.php';
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
        });
    </script>
    <?php include 'chat-widget.php'; ?>
</body>

</html>