<?php
// contact.php - Client-side contact form
session_start();

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
if ($logged_in) {
    $first_letter = substr($_SESSION['user_name'], 0, 1);
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    // Database connection
    $conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Sanitize input data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Split name into first and last name
    $name_parts = explode(' ', $name, 2);
    $first_name = $name_parts[0];
    $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
    
    // Insert inquiry into database
    $query = "INSERT INTO inquiries (first_name, last_name, email, phone, subject, message, status, created_at) 
              VALUES ('$first_name', '$last_name', '$email', '$phone', '$subject', '$message', 'unread', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $success_message = 'Thank you for your inquiry! We will get back to you soon.';
        // Clear form data
        $_POST = array();
    } else {
        $error_message = 'There was an error submitting your inquiry. Please try again.';
    }
    
    mysqli_close($conn);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Nail Architect - Get in Touch</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f2e9e9;
            padding: 20px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(235, 184, 184, 0.3);
            box-shadow: 
                0 4px 16px rgba(0, 0, 0, 0.1),
                0 2px 8px rgba(0, 0, 0, 0.05),
                inset 0 1px 2px rgba(255, 255, 255, 0.3);
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
            font-family: 'Poppins', sans-serif;
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
            border: none;
            color: white;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(230, 164, 164, 0.3);
        }

        .contact-info {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(235, 184, 184, 0.3);
            box-shadow: 
                0 4px 16px rgba(0, 0, 0, 0.1),
                0 2px 8px rgba(0, 0, 0, 0.05),
                inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }

        .info-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .info-section {
            margin-bottom: 25px;
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

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(to right, #e6a4a4, rgb(194, 138, 138));
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
            color: white;
            
        }

        .social-link:hover {
            transform: scale(1.1);
        }
        .social-links a {
    text-decoration: none; 
        }

        .map-section {
            margin-top: 30px;
            background-color: #dcdcdc;
            border-radius: 15px;
            overflow: hidden;
            height: 400px;
            animation: fadeIn 0.8s ease-out forwards;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: block;
            animation: fadeIn 0.5s ease-out;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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

        <div class="page-title">Get in Touch</div>
        <div class="page-subtitle">We'd love to hear from you. Send us a message or visit our salon.</div>

        <div class="contact-grid">
            <div class="contact-form-container">
                <div class="form-title">Send Us a Message</div>
                
                <?php if ($success_message): ?>
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                         <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject">
                            <option value="general">General Inquiry</option>
                            <option value="appointment">Appointment Question</option>
                            <option value="feedback">Feedback</option>
                            <option value="services">Services Information</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="submit_inquiry" class="submit-button">Send Message</button>
                </form>
            </div>

            <div class="contact-info">
                <div class="info-title">Contact Information</div>

                <div class="info-section">
                    <div class="info-heading">Address</div>
                    <div class="info-content">
                        <p>46 Osmena St., TS Cruz Subdivision</p>
                        <p>Novaliches, Quezon City</p>
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
                        <p>Email: nailarchitect.glamhub@gmail.com</p>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-heading">Follow Us</div>
                    <div class="social-links">
                        <a href="https://instagram.com/nailarchitect.ph" target="_blank">
                            <div class="social-link">
                                <i class="fab fa-instagram"></i>
                            </div>
                        </a>
                        <a href="https://facebook.com/nailarchitect.ph" target="_blank">
                            <div class="social-link">
                                <i class="fab fa-facebook"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="map-section">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3858.7160704716625!2d121.02828124838976!3d14.728637937375485!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b1aac9f4e097%3A0x15a42bfc5ec14994!2s46%20Osmena%20Right%2C%20Novaliches%2C%20Quezon%20City%2C%20Metro%20Manila!5e0!3m2!1sen!2sph!4v1743961729361!5m2!1sen!2sph"
                width="100%"
                height="100%"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
            </iframe>
        </div>
    </div>

    <script>
        // Simple client-side navigation
        document.querySelector('.nav-link').addEventListener('click', function() {
            window.location.href = 'services.php';
        });

        document.querySelector('.book-now').addEventListener('click', function() {
            window.location.href = 'booking.php';
        });

        <?php if ($logged_in): ?>
            document.querySelector('.user-initial').addEventListener('click', function() {
                window.location.href = 'members-lounge.php';
            });
        <?php else: ?>
            document.querySelector('.login-icon').addEventListener('click', function() {
                window.location.href = 'login.php';
            });
        <?php endif; ?>
    </script>
    <?php include 'chat-widget.php'; ?>
</body>
</html>