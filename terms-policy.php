<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Terms of Service & Privacy Policy - Nail Architect</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }
        
        body {
            background-color: #f5ece9;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1500px;
            margin: 0 auto;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 25px;
            margin-bottom: 30px;
        }
        
        .legal-container {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 40px;
        }
        
        .page-title {
            text-align: center;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
        }
        
        .tab-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 40px;
        }
        
        .tab-button {
            padding: 10px 30px;
            background: linear-gradient(to right, rgb(233, 171, 171), rgb(226, 178, 178));
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background: linear-gradient(to right, #d98d8d, #ce7878);
        }
        
        .tab-button:hover {
            background: linear-gradient(to right, #d98d8d, #ce7878);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgb(173, 150, 150);
        }
        
        .sub-section {
            margin-bottom: 25px;
        }
        
        .sub-section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .section-content {
            color: #333;
            margin-bottom: 15px;
        }
        
        ul {
            list-style-position: inside;
            margin-left: 20px;
            margin-bottom: 15px;
        }
        
        li {
            margin-bottom: 8px;
        }
        
        .contact-info {
            background-color: #f0f0f0;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .last-updated {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 40px;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 30px;
            font-size: 14px;
            cursor: pointer;
            position: relative;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .back-button:after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: #000;
            transition: width 0.3s ease;
        }
        
        .back-button:hover:after {
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .legal-container {
                padding: 20px;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .section-title {
                font-size: 18px;
            }
            
            .tab-button {
                padding: 8px 20px;
                font-size: 14px;
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
        
        <div class="legal-container">
            <h1 class="page-title">Terms of Service & Privacy Policy</h1>
            <p class="page-subtitle">Please review our legal policies below</p>
            
            <div class="tab-container">
                <button class="tab-button active" data-tab="terms">Terms of Service</button>
                <button class="tab-button" data-tab="privacy">Privacy Policy</button>
            </div>
            
            <!-- Terms of Service Tab -->
            <div class="tab-content active" id="terms">
                <div class="section">
                    <h2 class="section-title">1. Acceptance of Terms</h2>
                    <p class="section-content">
                        By accessing and using Nail Architect's website and services, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                    </p>
                </div>
                
                <div class="section">
                    <h2 class="section-title">2. Service Description</h2>
                    <p class="section-content">
                        Nail Architect provides professional nail care services including but not limited to:
                    </p>
                    <ul>
                        <li>Manicures and pedicures</li>
                        <li>Gel and acrylic nail services</li>
                        <li>Nail art and design</li>
                        <li>Nail repairs and maintenance</li>
                        <li>Consultation and aftercare advice</li>
                    </ul>
                    <p class="section-content">
                        All services are subject to availability and must be scheduled in advance.
                    </p>
                </div>
                
                <div class="section">
                    <h2 class="section-title">3. Booking and Cancellation Policy</h2>
                    <div class="sub-section">
                        <h3 class="sub-section-title">3.1 Booking Requirements</h3>
                        <p class="section-content">
                            Appointments must be booked at least 48 hours in advance for optimal availability. A 20% deposit is required to secure your appointment, which will be applied to your final service total.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">3.2 Cancellation Policy</h3>
                        <p class="section-content">
                            We require at least 24 hours notice for cancellations or rescheduling. Late cancellations (less than 24 hours) or no-shows will forfeit their deposit. Repeated no-shows may result in service restrictions.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">3.3 Late Arrivals</h3>
                        <p class="section-content">
                            If you arrive more than 15 minutes late for your appointment, we may need to reschedule or modify your service to accommodate other clients. In some cases, services may be shortened to maintain our schedule.
                        </p>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">4. Payment Terms</h2>
                    <div class="sub-section">
                        <h3 class="sub-section-title">4.1 Accepted Payment Methods</h3>
                        <p class="section-content">
                            We accept all major credit cards, mobile payments (Apple Pay, Google Pay), and cash. Payment is due at the time of service unless other arrangements have been made in advance.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">4.2 Pricing</h3>
                        <p class="section-content">
                            Service prices are subject to change based on the complexity of the design, length of nails, or additional treatments required. Your technician will discuss any additional costs with you before proceeding.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">4.3 Gratuity</h3>
                        <p class="section-content">
                            Gratuity is not included in our service prices. While tipping is at your discretion, it is appreciated for excellent service.
                        </p>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">5. Health and Safety</h2>
                    <div class="sub-section">
                        <h3 class="sub-section-title">5.1 Sanitation Standards</h3>
                        <p class="section-content">
                            We follow strict sanitation procedures including but not limited to:
                        </p>
                        <ul>
                            <li>Hospital-grade sterilization of all reusable tools</li>
                            <li>Single-use files and buffers for each client</li>
                            <li>Disposable pedicure tub liners</li>
                            <li>Regular sanitization of all surfaces and equipment</li>
                        </ul>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">5.2 Health Disclosure</h3>
                        <p class="section-content">
                            Clients must disclose any health conditions, allergies, or medications that might affect their service. We reserve the right to refuse service if there are health or safety concerns.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">5.3 Service Refusal</h3>
                        <p class="section-content">
                            We reserve the right to refuse service to any client who appears to have a contagious condition or infection, or whose behavior threatens the safety or comfort of our staff or other clients.
                        </p>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">6. Liability and Disclaimers</h2>
                    <div class="sub-section">
                        <h3 class="sub-section-title">6.1 Service Results</h3>
                        <p class="section-content">
                            While we strive for excellence in all our services, results may vary based on individual nail conditions and aftercare. We cannot guarantee specific results or duration of services.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">6.2 Allergic Reactions</h3>
                        <p class="section-content">
                            While we use high-quality products, allergic reactions can occur. Clients with known sensitivities should inform us before their service. We are not liable for allergic reactions unless due to our negligence.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">6.3 Personal Property</h3>
                        <p class="section-content">
                            We are not responsible for lost or damaged personal items left in our salon. Please secure your belongings during your visit.
                        </p>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">7. Intellectual Property</h2>
                    <p class="section-content">
                        All content on our website, including but not limited to text, graphics, logos, images, and software, is the property of Nail Architect and protected by copyright laws. Users may not reproduce, distribute, or create derivative works without our express written permission.
                    </p>
                </div>
                
                <div class="section">
                    <h2 class="section-title">8. Modifications to Terms</h2>
                    <p class="section-content">
                        We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting to our website. Your continued use of our services constitutes acceptance of any modifications.
                    </p>
                </div>
            </div>
            
            <!-- Privacy Policy Tab -->
            <div class="tab-content" id="privacy">
                <div class="section">
                    <h2 class="section-title">1. Information We Collect</h2>
                    <div class="sub-section">
                        <h3 class="sub-section-title">1.1 Personal Information</h3>
                        <p class="section-content">
                            We collect information you provide directly to us, such as:
                        </p>
                        <ul>
                            <li>Name and contact information (email, phone number, address)</li>
                            <li>Appointment history and preferences</li>
                            <li>Payment information (processed securely through third-party providers)</li>
                            <li>Health information relevant to services (allergies, sensitivities)</li>
                            <li>Photos of nail designs (with your consent)</li>
                        </ul>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">1.2 Automatically Collected Information</h3>
                        <p class="section-content">
                            When you visit our website, we may automatically collect:
                        </p>
                        <ul>
                            <li>Browser type and operating system</li>
                            <li>IP address</li>
                            <li>Pages visited and time spent on our site</li>
                            <li>Referring website addresses</li>
                        </ul>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">2. How We Use Your Information</h2>
                    <p class="section-content">
                        We use the information we collect to:
                    </p>
                    <ul>
                        <li>Process and manage appointments</li>
                        <li>Send appointment reminders and confirmations</li>
                        <li>Process payments and send receipts</li>
                        <li>Communicate with you about our services and promotions</li>
                        <li>Improve our services and customer experience</li>
                        <li>Comply with legal obligations</li>
                        <li>Personalize your experience and recommendations</li>
                    </ul>
                </div>
                
                <div class="section">
                    <h2 class="section-title">3. Information Sharing and Disclosure</h2>
                    <div class="sub-section">
                        <h3 class="sub-section-title">3.1 We Do Not Sell Your Information</h3>
                        <p class="section-content">
                            We do not sell, trade, or rent your personal information to third parties for their marketing purposes.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">3.2 Service Providers</h3>
                        <p class="section-content">
                            We may share your information with third-party service providers who perform services on our behalf, such as:
                        </p>
                        <ul>
                            <li>Payment processors</li>
                            <li>Appointment scheduling software</li>
                            <li>Email service providers</li>
                            <li>Analytics services</li>
                        </ul>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">3.3 Legal Requirements</h3>
                        <p class="section-content">
                            We may disclose your information if required by law or if we believe such action is necessary to:
                        </p>
                        <ul>
                            <li>Comply with legal obligations</li>
                            <li>Protect our rights or property</li>
                            <li>Prevent fraud or illegal activity</li>
                            <li>Ensure the safety of our staff and clients</li>
                        </ul>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">4. Data Security</h2>
                    <p class="section-content">
                        We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:
                    </p>
                    <ul>
                        <li>Secure socket layer (SSL) encryption for data transmission</li>
                        <li>Regular security audits and updates</li>
                        <li>Limited access to personal information on a need-to-know basis</li>
                        <li>Employee training on data protection and privacy</li>
                    </ul>
                    <p class="section-content">
                        However, no method of transmission over the internet or electronic storage is 100% secure. While we strive to protect your personal information, we cannot guarantee its absolute security.
                    </p>
                </div>
                
                <div class="section">
                    <h2 class="section-title">5. Your Rights and Choices</h2>
                    <div class="sub-section">
                        <h3 class="sub-section-title">5.1 Access and Update</h3>
                        <p class="section-content">
                            You have the right to access and update your personal information at any time by contacting us or through your account dashboard.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">5.2 Opt-Out</h3>
                        <p class="section-content">
                            You may opt out of receiving promotional communications from us by following the unsubscribe instructions in our emails or by contacting us directly.
                        </p>
                    </div>
                    <div class="sub-section">
                        <h3 class="sub-section-title">5.3 Data Deletion</h3>
                        <p class="section-content">
                            You may request deletion of your personal information, subject to certain exceptions required by law or for legitimate business purposes.
                        </p>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">6. Cookies and Tracking Technologies</h2>
                    <p class="section-content">
                        We use cookies and similar tracking technologies to:
                    </p>
                    <ul>
                        <li>Remember your preferences and settings</li>
                        <li>Understand how you use our website</li>
                        <li>Improve our services and user experience</li>
                        <li>Deliver targeted advertisements</li>
                    </ul>
                    <p class="section-content">
                        You can control cookies through your browser settings, but disabling cookies may affect the functionality of our website.
                    </p>
                </div>
                
                <div class="section">
                    <h2 class="section-title">7. Children's Privacy</h2>
                    <p class="section-content">
                        Our services are not directed to individuals under the age of 13. We do not knowingly collect personal information from children under 13. If we become aware that we have collected personal information from a child under 13, we will take steps to delete such information.
                    </p>
                </div>
                
                <div class="section">
                    <h2 class="section-title">8. International Data Transfers</h2>
                    <p class="section-content">
                        Your information may be transferred to and maintained on servers located outside of your state, province, country, or other governmental jurisdiction where data protection laws may differ. By using our services, you consent to such transfers.
                    </p>
                </div>
                
                <div class="section">
                    <h2 class="section-title">9. Third-Party Links</h2>
                    <p class="section-content">
                        Our website may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. We encourage you to review their privacy policies before providing any personal information.
                    </p>
                </div>
                
                <div class="section">
                    <h2 class="section-title">10. Changes to Privacy Policy</h2>
                    <p class="section-content">
                        We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. Your continued use of our services after any changes indicates your acceptance of the updated policy.
                    </p>
                </div>
            </div>
            
            <div class="contact-info">
                <h3 class="sub-section-title">Contact Information</h3>
                <p class="section-content">
                    If you have any questions about these Terms of Service or Privacy Policy, please contact us at:
                </p>
                <p class="section-content">
                    <strong>Nail Architect</strong><br>
                    46 Osmena St., TS Cruz Subdivision<br>
                    Novaliches, Quezon City<br>
                    Phone: (212) 555-7890<br>
                    Email: legal@nailarchitect.com
                </p>
            </div>
            
            <p class="last-updated">Last Updated: May 2025</p>
        </div>
        
        <a href="index.php" class="back-button">← Back to Home</a>
    </div>
    
    <script>
        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-tab');
                
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                document.getElementById(targetTab).classList.add('active');
            });
        });
    </script>
</body>
</html>