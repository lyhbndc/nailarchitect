<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="bg-gradient.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <title>Nail Architect - Get in Touch</title>
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

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            animation: fadeIn 0.7s ease-out forwards;
        }

        .contact-form-container {
            background-color: rgb(224, 184, 184);
            border-radius: 15px;
            padding: 30px;
        }

        .form-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #f2e9e9;
            font-family: Poppins;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            background-color: #ffffff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .submit-button {
            padding: 12px 24px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            margin-top: 20px;
            font-weight: bold;
        }

        .submit-button:hover {
            background-color: #ae9389;
            transform: translateY(-2px);
        }

        .contact-info {
            background-color: rgb(224, 184, 184);
            border: none;
            border-radius: 15px;
            padding: 30px;
        }

        .info-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-section:last-child {
            margin-bottom: 0;
        }

        .info-heading {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .info-content {
            font-size: 14px;
            line-height: 1.6;
        }

        .info-content p {
            margin-bottom: 8px;
        }

        .info-content p:last-child {
            margin-bottom: 0;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgb(158, 91, 91);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .social-link:hover {
            background-color: #ae9389;
            transform: scale(1.1);
        }

        .map-section {
            margin-top: 30px;
            background-color: #dcdcdc;
            border-radius: 15px;
            overflow: hidden;
            height: 400px;
            animation: fadeIn 0.8s ease-out forwards;
        }

        .map-container {
            width: 100%;
            height: 100%;
        }

        .map-placeholder {
            width: 100%;
            height: 100%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
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
        @media (max-width: 992px) {
            .contact-grid {
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
                <div class="login-icon"></div>
            </div>
        </header>
        <a href="index.php">
            <div class="back-button">← Back</div>
        </a>
        <div class="page-title">Get in Touch</div>
        <div class="page-subtitle">We'd love to hear from you. Send us a message or visit our salon.</div>

        <div class="contact-grid">
            <div class="contact-form-container">
                <div class="form-title">Send Us a Message</div>
                <form id="contact-form">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject">
                            <option value="general">General Inquiry</option>
                            <option value="appointment">Appointment Question</option>
                            <option value="feedback">Feedback</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit" class="submit-button">Send Message</button>
                </form>
            </div>

            <div class="contact-info">
                <div class="info-title">Contact Information</div>

                <div class="info-section">
                    <div class="info-heading">Address</div>
                    <div class="info-content">
                        <p>46 Osmena, Novaliches</p>
                        <p>Quezon City, Metro Manila</p>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-heading">Hours</div>
                    <div class="info-content">
                        <p>Monday - Friday: 9am - 7pm</p>
                        <p>Saturday: 10am - 6pm</p>
                        <p>Sunday: 12pm - 5pm</p>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-heading">Contact</div>
                    <div class="info-content">
                        <p>Phone: (0979) 555-7890</p>
                        <p>Email: hello@nailarchitect.com</p>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-heading">Follow Us</div>
                    <div class="social-links">
                        <div class="social-link">IG</div>
                        <div class="social-link">FB</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="map-section" id="map">
            <div class="map-container">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3858.7160704716625!2d121.02828124838976!3d14.728637937375485!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b1aac9f4e097%3A0x15a42bfc5ec14994!2s46%20Osmena%20Right%2C%20Novaliches%2C%20Quezon%20City%2C%20Metro%20Manila!5e0!3m2!1sen!2sph!4v1743961729361!5m2!1sen!2sph"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>


    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.getElementById('contact-form').addEventListener('submit', function(e) {
                e.preventDefault();

                alert('Thank you for your message! We will get back to you soon.');
                this.reset();
            });

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