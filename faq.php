<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <title>Nail Salon - Policies & FAQ</title>
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

        .content-wrapper {
            animation: fadeIn 0.7s ease-out forwards;
            display: flex;
            gap: 30px;
        }

        .sidebar {
            width: 250px;
            flex-shrink: 0;
        }

        .sidebar-nav {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e8d7d0;
        }

        .nav-list {
            list-style: none;
        }

        .nav-list li {
            margin-bottom: 10px;
        }

        .nav-list a {
            text-decoration: none;
            color: inherit;
            display: block;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-list a:hover,
        .nav-list a.active {
            background-color: #ae9389;
        }

        .main-content {
            flex: 1;
        }

        .section {
            background-color: #e8d7d0;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #c0c0c0;
        }

        .policy-item,
        .faq-item {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e0e0e0;
        }

        .policy-item:last-child,
        .faq-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .policy-title,
        .faq-question {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .policy-content,
        .faq-answer {
            font-size: 14px;
            line-height: 1.6;
        }

        .policy-content p,
        .faq-answer p {
            margin-bottom: 10px;
        }

        .policy-content p:last-child,
        .faq-answer p:last-child {
            margin-bottom: 0;
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
        @media (max-width: 992px) {
            .content-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .sidebar-nav {
                position: static;
                margin-bottom: 20px;
            }

            .nav-list {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .nav-list li {
                margin-bottom: 0;
            }
        }

        @media (max-width: 576px) {
            .nav-list {
                flex-direction: column;
            }

            .section {
                padding: 20px;
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
                <div class="login-icon"></div>
            </div>
        </header>
        <a href="index.php">
            <div class="back-button">← Back</div>
        </a>
        <div class="page-title">Policies & FAQ</div>
        <div class="page-subtitle">Learn about our salon policies and get answers to common questions</div>

        <div class="content-wrapper">
            <div class="sidebar">
                <div class="sidebar-nav">
                    <div class="sidebar-title">Quick Navigation</div>
                    <ul class="nav-list">
                        <li><a href="#appointments" class="active">Appointments</a></li>
                        <li><a href="#cancellations">Cancellations & Rescheduling</a></li>
                        <li><a href="#payments">Payments</a></li>
                        <li><a href="#health-safety">Health & Safety</a></li>
                        <li><a href="#services-faq">Services FAQ</a></li>
                        <li><a href="#service-duration">Service Duration</a></li>
                        <li><a href="#manicure-care">Manicure Care</a></li>
                        <li><a href="#aftercare">Nail Care & Aftercare</a></li>
                    </ul>
                </div>
            </div>

            <div class="main-content">
                <!-- Policies Section -->
                <div class="section" id="policies">
                    <div class="section-title">Salon Policies</div>

                    <div class="policy-item" id="appointments">
                        <div class="policy-title">Appointment Booking</div>
                        <div class="policy-content">
                            <p>We recommend booking your appointment at least 48 hours in advance to secure your preferred date and time. Walk-ins are welcome but subject to availability.</p>
                            <p>Please arrive 5-10 minutes before your scheduled appointment. If you are more than 15 minutes late, we may need to reschedule or modify your service to ensure all clients receive their full appointment time.</p>
                            <p>A 20% deposit is required to secure your appointment. This deposit will be applied to your service total upon completion.</p>
                        </div>
                    </div>

                    <div class="policy-item" id="cancellations">
                        <div class="policy-title">Cancellations & Rescheduling</div>
                        <div class="policy-content">
                            <p>We require at least 24 hours notice for cancellations or rescheduling. Late cancellations (less than 24 hours notice) or no-shows may forfeit their deposit.</p>
                            <p>If you need to cancel or reschedule, please call us at (212) 555-7890 or use our website/app to make changes to your appointment.</p>
                            <p>In case of emergencies, please contact us as soon as possible, and we'll do our best to accommodate your situation.</p>
                        </div>
                    </div>

                    <div class="policy-item" id="payments">
                        <div class="policy-title">Payment & Pricing</div>
                        <div class="policy-content">
                            <p>We accept all major credit cards, mobile payments (Apple Pay, Google Pay), and cash.</p>
                            <p>Service prices are subject to change based on the complexity of the design, length of nails, or additional treatments required. Your technician will discuss any additional costs with you before proceeding.</p>
                            <p>Gratuity is not included in our service prices. While tipping is at your discretion, it is appreciated for excellent service.</p>
                        </div>
                    </div>

                    <div class="policy-item" id="health-safety">
                        <div class="policy-title">Health & Safety</div>
                        <div class="policy-content">
                            <p>We prioritize your health and safety. All tools are thoroughly sanitized between clients using hospital-grade disinfectants and autoclaves.</p>
                            <p>If you have any health concerns, allergies, or medical conditions that might affect your service, please inform us when booking your appointment.</p>
                            <p>We kindly ask clients to reschedule if they are experiencing any contagious illness or infection. We reserve the right to refuse service to protect the health of our staff and other clients.</p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="section" id="faq">
                    <div class="section-title">Frequently Asked Questions</div>

                    <div class="faq-item" id="services-faq">
                        <div class="faq-question">What's the difference between gel, acrylic, and dip powder nails?</div>
                        <div class="faq-answer">
                            <p><strong>Gel Nails:</strong> Applied like polish and cured under a UV or LED lamp. They're known for their glossy finish and flexibility. Gel nails typically last 2-3 weeks.</p>
                            <p><strong>Acrylic Nails:</strong> Created by mixing a liquid monomer with a powder polymer to form a dough-like consistency that hardens when exposed to air. Acrylics are very durable and can last 6-8 weeks with proper fills.</p>
                            <p><strong>Dip Powder:</strong> A technique where nails are dipped into colored powder, sealed with a protective clear coat. Dip powder nails are lightweight, durable, and typically last 3-4 weeks.</p>
                        </div>
                    </div>

                    <div class="faq-item" id="service-duration">
                        <div class="faq-question">How long do nail services typically take?</div>
                        <div class="faq-answer">
                            <p>Service times vary depending on the treatment:</p>
                            <p>- Basic manicure: 30-45 minutes<br>
                                - Gel manicure: 45-60 minutes<br>
                                - Basic pedicure: 45-60 minutes<br>
                                - Luxury pedicure: 60-75 minutes<br>
                                - Full set acrylics: 60-90 minutes<br>
                                - Nail art: varies depending on complexity</p>
                            <p>When booking, our system will automatically allocate the appropriate time for your chosen service.</p>
                        </div>
                    </div>

                    <div class="faq-item" id="manicure-care">
                        <div class="faq-question">How can I make my manicure last longer?</div>
                        <div class="faq-answer">
                            <p>To extend the life of your manicure:</p>
                            <p>- Wear gloves when cleaning, gardening, or doing dishes<br>
                                - Apply cuticle oil daily to maintain flexibility<br>
                                - Use a quality top coat every 2-3 days for regular polish<br>
                                - Avoid using your nails as tools (opening cans, scratching labels)<br>
                                - Keep hands moisturized<br>
                                - Avoid exposure to harsh chemicals</p>
                            <p>Following these tips can help your manicure last significantly longer!</p>
                        </div>
                    </div>

                    <div class="faq-item" id="aftercare">
                        <div class="faq-question">How should I care for my nails after a service?</div>
                        <div class="faq-answer">
                            <p><strong>For regular polish:</strong> Allow 1-2 hours for full hardening, even if they feel dry to the touch. Apply a top coat every few days to extend wear.</p>
                            <p><strong>For gel/dip/acrylic:</strong> Avoid contact with harsh chemicals. Apply cuticle oil daily. Do not pick, peel, or try to remove enhancement products yourself as this can damage your natural nails.</p>
                            <p><strong>For all services:</strong> Moisturize hands regularly, wear gloves for cleaning or gardening, and avoid using your nails as tools.</p>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll to sections when clicking on sidebar links
            const sidebarLinks = document.querySelectorAll('.nav-list a');

            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all links
                    sidebarLinks.forEach(l => l.classList.remove('active'));

                    // Add active class to clicked link
                    this.classList.add('active');

                    // Get the target section
                    const targetId = this.getAttribute('href');
                    const targetSection = document.querySelector(targetId);

                    // Scroll to the section
                    if (targetSection) {
                        window.scrollTo({
                            top: targetSection.offsetTop - 20,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Navigation handlers
            document.querySelector('.logo').addEventListener('click', function() {
                window.location.href = 'index.php';
            });

            const navLinks = document.querySelectorAll('.nav-links .nav-link');

            navLinks[0].addEventListener('click', function() {
                window.location.href = 'index.php';
            });

            navLinks[1].addEventListener('click', function() {
                window.location.href = 'services.php';
            });

            navLinks[2].addEventListener('click', function() {
                window.location.href = 'gallery.php';
            });

            document.querySelector('.book-now').addEventListener('click', function() {
                window.location.href = 'booking-form-with-upload.php';
            });

            document.querySelector('.login-icon').addEventListener('click', function() {
                window.location.href = 'login-form.php';
            });

            document.querySelector('.back-button').addEventListener('click', function() {
                window.location.href = 'index.php';
            });
        });
    </script>
    <?php include 'chat-widget.php'; ?>
</body>

</html>