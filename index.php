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
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #F2E9E9;
            padding: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .container {
            max-width: 1200px;
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
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .logo-container img {
            height: 60px;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-link {
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .nav-link:hover {
            opacity: 0.7;
        }
        
        .book-now {
            padding: 8px 20px;
            background-color: #e8d7d0;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .book-now:hover {
            background-color: #d9bbb0;
        }
        
        .login-icon {
            cursor: pointer;
        }
        
        .user-initial {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #e0c5b7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
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
            background-color: #e9e1de;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .grid-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
            background-color: #e6a4a4;
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
            background-color: #e6a4a4;
        }
        
        .faq {
            grid-column: span 8;
            grid-row: 8 / span 5;
            min-height: 200px;
            background-color: #c78c8c;
        }
        
        .visit-us {
            grid-column: 9 / span 4;
            grid-row: 8 / span 5;
            min-height: 200px;
        }
        
        .grid-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
        
        .grid-content {
            position: relative;
            z-index: 2;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            color: #333;
        }
        
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                to bottom,
                rgba(217, 187, 176, 0) 0%,
                rgba(217, 187, 176, 0.3) 50%,
                rgba(217, 187, 176, 0.5) 75%,
                rgba(217, 187, 176, 0.7) 100%
            );
            z-index: 1;
        }
        
        .grid-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .light {
            color: #F2E9E9;
        }
        .main-title {
            font-size: 24px;
            font-weight: bold;
            line-height: 1.3;
        }
        
        .card-action {
            align-self: flex-end;
            margin-top: auto;
            font-size: 14px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
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
        }
        
        @media (max-width: 768px) {
            body {
                overflow-y: auto;
                height: auto;
            }
            
            .container {
                height: auto;
                min-height: 100%;
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
        }
    </style>
</head>
<body>
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
                <img src="Assets/services.webp" alt="Colorful nails with rings" class="grid-img">
                <div class="overlay"></div>
                <div class="grid-content">
                    <div>
                        <div class="main-title">
                            Your Nails,<br>
                            Your Statement.
                        </div>
                    </div>
                    <div class="card-action">> View Services</div>
                </div>
            </div>
            
            <div class="grid-item book-appointment">
                <img src="Assets/book.jpg" alt="Elegant nail art" class="grid-img">
                <div class="overlay"></div>
                <div class="grid-content">
                    <div class="grid-title light">Book Appointment</div>
                </div>
            </div>
            
            <div class="grid-item gallery">
                <img src="Assets/gallery.jpg" alt="Artistic nail designs" class="grid-img">
                <div class="overlay"></div>
                <div class="grid-content">
                    <div class="grid-title light">Gallery</div>
                </div>
            </div>
            
            <div class="grid-item contact">
                <img src="Assets/contact.png" alt="Colorful nail art" class="grid-img">
                <div class="overlay"></div>
                <div class="grid-content">
                    <div class="grid-title light">Contact</div>
                </div>
            </div>
            
            <div class="grid-item faq">
                <img src="Assets/faq.jpg" alt="Close-up of lips and nails" class="grid-img">
                <div class="overlay"></div>
                <div class="grid-content">
                    <div class="grid-title light">Take A Matchmaking Quiz</div>
                </div>
            </div>
            
            <div class="grid-item visit-us">
                <img src="Assets/visit.png" alt="Nail technician working" class="grid-img">
                <div class="overlay"></div>
                <div class="grid-content">
                    <div class="grid-title light">FAQ</div>
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
                window.location.href = 'contact.php';
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
</body>
</html>