<?php
// Start session
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);

// If logged in, get the first letter of the user's first name for the avatar
$user_name = '';
$user_email = '';
$user_phone = '';

if ($logged_in) {
    // Get first letter for avatar
    $first_letter = substr($_SESSION['user_name'], 0, 1);

    // Connect to database to get user info
    $conn = mysqli_connect("localhost", "root", "", "nail_architect_db");
    if ($conn) {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT first_name, last_name, email, phone FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $user_name = $user['first_name'] . ' ' . $user['last_name'];
            $user_email = $user['email'];
            $user_phone = $user['phone'];
        }

        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="bg-gradient.css">
    <link rel="icon" type="image/png" href="Assets/favicon1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Nail Architect - Booking Form</title>
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

        .booking-form-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            animation: fadeIn 0.7s ease-out forwards;
        }

        .booking-form {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(235, 184, 184, 0.3);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.1),
    0 2px 8px rgba(0, 0, 0, 0.05),
    inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
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

        .service-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .service-option {
            position: relative;
            padding: 12px;
            background-color: #f2e9e9;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .service-option:hover {
            background-color: #f8f0ed;
        }

        .service-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .service-option label {
            display: block;
            padding-left: 30px;
            cursor: pointer;
            font-weight: normal;
            margin-bottom: 0;
        }

        .checkmark {
            position: absolute;
            top: 12px;
            left: 12px;
            height: 20px;
            width: 20px;
            background-color: #fff;
            border-radius: 50%;
        }

        .service-option input:checked~.checkmark {
            background-color: #d9bbb0;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .service-option input:checked~.checkmark:after {
            display: block;
        }

        .service-option .checkmark:after {
            left: 7px;
            top: 3px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .service-price {
            float: right;
            font-size: 12px;
            color: #666;
        }

        .upload-section {
            border: 2px dashed #c0c0c0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f0f0f0;
            position: relative;
            overflow: hidden;
        }

        .upload-section:hover {
            border-color: #a0a0a0;
        }

        .upload-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .upload-text {
            margin-bottom: 15px;
            font-size: 14px;
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .browse-button {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .browse-button:hover {
            background-color: #ae9389;
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .preview-image {
            width: 80px;
            height: 80px;
            border-radius: 5px;
            object-fit: cover;
            position: relative;
        }

        .remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 20px;
            height: 20px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
        }

        .date-time-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .payment-info {
            background-color:rgb(245, 207, 207);
            border-radius: 15px;
            padding: 25px;
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

        .qr-container {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code {
            width: 180px;
            height: 180px;
            margin: 0 auto 15px;
            background-color: #fff;
            padding: 10px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }

        .qr-instructions {
            font-size: 14px;
            line-height: 1.5;
        }

        .policy-box {
            background-color: rgb(245, 207, 207);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(235, 184, 184, 0.3);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.1),
    0 2px 8px rgba(0, 0, 0, 0.05),
    inset 0 1px 2px rgba(255, 255, 255, 0.3);
        
        }

        .policy-content {
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .policy-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 15px;
        }

        .policy-checkbox input {
            width: auto;
            margin-top: 3px;
        }

        .policy-checkbox label {
            margin-bottom: 0;
            font-weight: normal;
        }

        .submit-button {
            padding: 12px 24px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            border: none;
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
        @media (max-width: 1024px) {
            .booking-form-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .payment-info,
            .policy-box {
                flex: 1;
                min-width: 300px;
            }
        }

        @media (max-width: 768px) {
            .service-options {
                grid-template-columns: 1fr;
            }

            .date-time-grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                flex-direction: column;
            }
        }
        .upload-section.has-images .upload-icon,
.upload-section.has-images .upload-text,
.upload-section.has-images .browse-button {
    display: none;
}

.upload-section.has-images {
    border-style: solid;
    min-height: 120px;
}

/* Image wrapper for proper positioning */
.preview-image-wrapper {
    position: relative;
    display: inline-block;
}

.remove-image {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 20px;
    height: 20px;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
}

.remove-image:hover {
    background-color: rgba(0, 0, 0, 0.9);
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
        <div class="page-title">Book Your Appointment</div>
        <div class="page-subtitle">Complete the form below to schedule your visit</div>

        <div class="booking-form-container">
            <div class="booking-form">
                <form id="appointment-form" method="POST" action="process_booking.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Select Service</label>
                        <div class="service-options">
                            <div class="service-option">
                                <input type="radio" id="service1" name="service" value="soft-gel" required>
                                <label for="service1">Soft Gel<span class="service-price">starts at P800</span></label>
                                <span class="checkmark"></span>
                            </div>

                            <div class="service-option">
                                <input type="radio" id="service2" name="service" value="press-ons">
                                <label for="service2">Press Ons<span class="service-price">starts at P300</span></label>
                                <span class="checkmark"></span>
                            </div>

                            <div class="service-option">
                                <input type="radio" id="service3" name="service" value="builder-gel">
                                <label for="service3">Builder Gel<span class="service-price">starts at P750</span></label>
                                <span class="checkmark"></span>
                            </div>

                            <div class="service-option">
                                <input type="radio" id="service4" name="service" value="menicure">
                                <label for="service4">Menicure<span class="service-price">starts at P400</span></label>
                                <span class="checkmark"></span>
                            </div>

                            <div class="service-option">
                                <input type="radio" id="service5" name="service" value="removal-fill">
                                <label for="service5">Removal/Fill<span class="service-price">starts at P150</span></label>
                                <span class="checkmark"></span>
                            </div>

                            <div class="service-option">
                                <input type="radio" id="service6" name="service" value="other">
                                <label for="service6">Other Services<span class="service-price">price varies</span></label>
                                <span class="checkmark"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nail Inspiration Images (Optional)</label>
                        <div class="upload-section" id="inspiration-upload">
                            <div class="upload-icon">📁</div>
                            <div class="upload-text">Drag and drop images or click to browse</div>
                            <div class="browse-button">Browse Files</div>
                            <input type="file" id="nail-inspo" name="nail_inspo[]" class="file-input" accept="image/*" multiple>
                            <div class="preview-container" id="inspo-preview"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="date-time-grid">
                            <div>
                                <label for="date">Preferred Date</label>
                                <input type="date" id="date" name="date" required>
                            </div>

                            <div>
                                <label for="time">Preferred Time</label>
                                <select id="time" name="time" required disabled>
                                    <option value="">Select a date first</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Special Requests or Notes</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload Screenshot of the Payment</label>
                        <div class="upload-section" id="payment-upload">
                            <div class="upload-icon">📁</div>
                            <div class="upload-text">Drag and drop payment screenshot or click to browse</div>
                            <div class="browse-button">Browse Files</div>
                            <input type="file" id="payment-proof" name="payment_proof" class="file-input" accept="image/*" required>
                            <div class="preview-container" id="payment-preview"></div>
                        </div>
                    </div>

                    <div class="form-group policy-checkbox">
                        <input type="checkbox" id="deposit-confirm" name="deposit-confirm" required>
                        <label for="deposit-confirm">I confirm that I have made the deposit payment via the QR code</label>
                    </div>

                    <div class="form-group policy-checkbox">
                        <input type="checkbox" id="policy-agree" name="policy-agree" required>
                        <label for="policy-agree">I have read and agree to the salon policies</label>
                    </div>

                    <?php if ($logged_in): ?>
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <?php endif; ?>

                    <button type="submit" class="submit-button">Book Appointment</button>
                </form>
            </div>

            <div class="sidebar">
                <div class="payment-info">
                    <div class="info-title">Down Payment</div>
                    <p>A P500 deposit is required to secure your appointment. This amount will be deducted from your final bill.</p>

                    <div class="qr-container">
                        <div class="qr-code">
                            <img src="Assets/qr.png" alt="Payment QR Code">
                        </div>
                        <div class="qr-instructions">
                            Scan this QR code with your phone's camera to make the P500 deposit payment using your preferred payment app.
                        </div>
                    </div>
                </div>

                <div class="policy-box">
                    <div class="info-title">Salon Policies</div>
                    <div class="policy-content">
                        <p><strong>Cancellation Policy:</strong> We require 24 hours notice for cancellations or rescheduling. Late cancellations (less than 24 hours) or no-shows will forfeit the deposit.</p>
                        <p><strong>Late Arrivals:</strong> If you're more than 15 minutes late, we may need to reschedule or modify your service.</p>
                        <p><strong>Health & Safety:</strong> Please inform us of any health concerns or allergies before your appointment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
     document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker with current date + 1 day as minimum
    const dateInput = document.getElementById('date');
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowFormatted = tomorrow.toISOString().split('T')[0];
    dateInput.setAttribute('min', tomorrowFormatted);

    // Handle navigation
    const backButton = document.querySelector('.back-button');
    backButton.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'index.php';
    });

    const servicesLink = document.querySelector('.nav-link');
    servicesLink.addEventListener('click', function() {
        window.location.href = 'services.php';
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

    // Handle file uploads for inspiration images
    setupFileUpload('nail-inspo', 'inspo-preview', 'inspiration-upload', 3);

    // Handle file upload for payment proof
    setupFileUpload('payment-proof', 'payment-preview', 'payment-upload', 1);

    // Setup file upload functionality
    function setupFileUpload(inputId, previewId, areaId, maxFiles) {
        const fileInput = document.getElementById(inputId);
        const previewContainer = document.getElementById(previewId);
        const uploadArea = document.getElementById(areaId);
        
        // Store files in a DataTransfer object to maintain file list
        let storedFiles = new DataTransfer();

        // Handle drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, function() {
                uploadArea.style.borderColor = '#a0a0a0';
                uploadArea.style.backgroundColor = '#e5e5e5';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, function() {
                uploadArea.style.borderColor = '#c0c0c0';
                uploadArea.style.backgroundColor = '#f0f0f0';
            }, false);
        });

        uploadArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }, false);

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            // Convert FileList to Array
            const filesArray = Array.from(files);
            
            // Clear stored files if single file upload
            if (maxFiles === 1) {
                storedFiles = new DataTransfer();
                previewContainer.innerHTML = '';
            }
            
            let currentFileCount = storedFiles.files.length;
            
            filesArray.forEach(file => {
                if (!file.type.match('image.*')) {
                    alert('Please upload only image files.');
                    return;
                }
                
                if (currentFileCount >= maxFiles) {
                    alert(`Maximum ${maxFiles} files allowed.`);
                    return;
                }
                
                // Add file to stored files
                storedFiles.items.add(file);
                currentFileCount++;
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imagePreviewWrapper = document.createElement('div');
                    imagePreviewWrapper.className = 'preview-image-wrapper';
                    imagePreviewWrapper.dataset.fileName = file.name;
                    
                    const img = document.createElement('img');
                    img.classList.add('preview-image');
                    img.src = e.target.result;
                    
                    const removeBtn = document.createElement('div');
                    removeBtn.classList.add('remove-image');
                    removeBtn.innerHTML = '×';
                    removeBtn.addEventListener('click', function() {
                        // Remove file from stored files
                        const newStoredFiles = new DataTransfer();
                        for (let i = 0; i < storedFiles.files.length; i++) {
                            if (storedFiles.files[i].name !== file.name) {
                                newStoredFiles.items.add(storedFiles.files[i]);
                            }
                        }
                        storedFiles = newStoredFiles;
                        
                        // Update the input files
                        fileInput.files = storedFiles.files;
                        
                        // Remove the preview
                        imagePreviewWrapper.remove();
                        
                        // Update upload area class
                        checkForImages();
                    });
                    
                    imagePreviewWrapper.appendChild(img);
                    imagePreviewWrapper.appendChild(removeBtn);
                    previewContainer.appendChild(imagePreviewWrapper);
                    
                    // Update upload area to show images are present
                    uploadArea.classList.add('has-images');
                }
                
                reader.readAsDataURL(file);
            });
            
            // Update the file input with all stored files
            fileInput.files = storedFiles.files;
        }
        
        function checkForImages() {
            if (previewContainer.children.length === 0) {
                uploadArea.classList.remove('has-images');
            } else {
                uploadArea.classList.add('has-images');
            }
        }
    }

    // Get time slot select element
    const timeSelect = document.getElementById('time');

    // Update available times when date changes
    dateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        if (selectedDate) {
            updateAvailableTimes(selectedDate);
        }
    });

    // Function to update available time slots
    function updateAvailableTimes(date) {
        // Clear current options
        timeSelect.innerHTML = '<option value="">Select a time</option>';
        timeSelect.disabled = true;

        // Add loading option
        const loadingOption = document.createElement('option');
        loadingOption.text = 'Loading available times...';
        timeSelect.add(loadingOption);

        // Fetch available times from server
        fetch('get-available-times.php?date=' + date)
            .then(response => response.json())
            .then(data => {
                // Remove loading option
                timeSelect.removeChild(loadingOption);

                // Enable select
                timeSelect.disabled = false;

                if (data.success) {
                    if (Object.keys(data.available_times).length === 0) {
                        // No available times
                        const noTimesOption = document.createElement('option');
                        noTimesOption.text = 'No available times for this date';
                        noTimesOption.disabled = true;
                        timeSelect.add(noTimesOption);
                    } else {
                        // Add available times
                        for (const [value, label] of Object.entries(data.available_times)) {
                            const option = document.createElement('option');
                            option.value = value;
                            option.text = label;
                            timeSelect.add(option);
                        }
                    }
                } else {
                    console.error('Error fetching available times:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                timeSelect.disabled = false;
            });
    }

    // Form validation
    document.getElementById('appointment-form').addEventListener('submit', function(e) {
        // Basic validation
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const date = document.getElementById('date').value;
        const time = document.getElementById('time').value;
        const serviceSelected = document.querySelector('input[name="service"]:checked');
        const depositConfirm = document.getElementById('deposit-confirm').checked;
        const policyAgree = document.getElementById('policy-agree').checked;
        const paymentProof = document.getElementById('payment-proof').files.length > 0;

        if (!name || !email || !phone || !date || !time || !serviceSelected || !depositConfirm || !policyAgree || !paymentProof) {
            e.preventDefault();
            alert('Please fill in all required fields, upload payment proof, and confirm the policies.');
        }
    });
});
    </script>
    <?php include 'chat-widget.php'; ?>
</body>

</html>