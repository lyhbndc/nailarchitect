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
    <title>Nail Architect - Gallery</title>
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
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease-out forwards;
        }

        .gallery-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            animation: fadeIn 0.6s ease-out forwards;
        }

        .filter-button {
            padding: 8px 16px;
            background: linear-gradient(to right, #e6a4a4, #d98d8d);
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-button:hover {
            background: linear-gradient(to right, #d98d8d, #c47878);
        }

        .filter-button.active {
            background: linear-gradient(to right, #ae9389, #917268);
            color: #f2e9e9;
            font-weight: bold;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            animation: fadeIn 0.7s ease-out forwards;
        }

        .gallery-item {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            aspect-ratio: 1 / 1;
            background-color: #dcdcdc;
            transition: all 0.3s ease;
        }

        .gallery-item:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 10px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        .overlay-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .overlay-desc {
            font-size: 12px;
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

        /* Animation for filtering */
        .gallery-item {
            opacity: 1;
            transform: scale(1);
            transition: opacity 0.3s ease, transform 0.3s ease;
            display: block;
        }

        .gallery-item.hidden {
            opacity: 0;
            transform: scale(0.8);
            height: 0;
            width: 0;
            margin: 0;
            padding: 0;
            position: absolute;
            overflow: hidden;
            display: none;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .gallery-filters {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .gallery-grid {
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
        <div class="page-title">Our Gallery</div>

        <div class="gallery-filters">
            <div class="filter-button active" data-filter="all">All</div>
            <div class="filter-button" data-filter="soft-gel">Soft Gel</div>
            <div class="filter-button" data-filter="builder-gel">Builder Gel</div>
            <div class="filter-button" data-filter="press-ons">Press Ons</div>
            <div class="filter-button" data-filter="menicure">Menicure</div>
        </div>

        <div class="gallery-grid">
            <div class="gallery-item" data-category="soft-gel">
                <img src="Assets/gallery/sg1.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Abstract Minimalist</div>
                    <div class="overlay-desc">Soft gel with minimalist chrome line art and subtle rhinestone accents on a neutral base.</div>
                </div>
            </div>

            <div class="gallery-item" data-category="soft-gel">
                <img src="Assets/gallery/sg2.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Burgundy Petal Tips</div>
                    <div class="overlay-desc">Soft gel featuring pink base with burgundy French tips and delicate red flower details.</div>
                </div>
            </div>

            <div class="gallery-item" data-category="soft-gel">
                <img src="Assets/gallery/sg3.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Fruity Fun</div>
                    <div class="overlay-desc">Soft gel with bright summer motifs including citrus slices, fish, and colorful floral designs.</div>
                </div>
            </div>

            <div class="gallery-item" data-category="builder-gel">
                <img src="Assets/gallery/bg1.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Blush Almond</div>
                    <div class="overlay-desc">Builder gel in soft pink with almond shape for an elegant, natural look.</div>
                </div>
            </div>

            <div class="gallery-item" data-category="builder-gel">
                <img src="Assets/gallery/bg2.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Milky French</div>
                    <div class="overlay-desc">Builder gel featuring translucent pink base with crisp white tips in oval shape.</div>
                </div>
            </div>

            <div class="gallery-item" data-category="press-ons">
                <img src="Assets/gallery/po1.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Celestial Bloom</div>
                    <div class="overlay-desc">Press-ons with nude base, blue floral French tips and gold crescent moon accents.</div>
                </div>
            </div>

            <div class="gallery-item" data-category="press-ons">
                <img src="Assets/gallery/po2.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Cartoon Cosmos</div>
                    <div class="overlay-desc">Press-ons featuring Rick and Morty characters on olive green nails with cosmic black swirls.</div>
                </div>
            </div>

            <div class="gallery-item" data-category="press-ons">
                <img src="Assets/gallery/po3.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Amber Geometry</div>
                    <div class="overlay-desc">Press-ons with transparent nude base, brown geometric patterns and gold wire accents.</div>
                </div>
            </div>
            <div class="gallery-item" data-category="press-ons">
                <img src="Assets/gallery/po4.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Geek Chic</div>
                    <div class="overlay-desc">Press-ons showcasing colorful superhero and science-themed designs with pop culture references.</div>
                </div>
            </div>
            <div class="gallery-item" data-category="menicure">
                <img src="Assets/gallery/m1.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Street Art Mix</div>
                    <div class="overlay-desc">Menicure featuring eclectic designs on clear base including blue flames, black symbols, racing checks, and micro florals for an edgy urban vibe.</div>
                </div>
            </div>
            <div class="gallery-item" data-category="menicure">
                <img src="Assets/gallery/m2.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Cosmic Pastels</div>
                    <div class="overlay-desc">Menicure with soft blue tones, burgundy spirals, and yellow star accent on short rounded nails for a subtle yet artistic statement.</div>
                </div>
            </div>
            <div class="gallery-item" data-category="menicure">
                <img src="Assets/gallery/m3.jpeg" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Monochrome Graphic</div>
                    <div class="overlay-desc">Menicure in sleek black and white with bold spiral patterns, sun motifs, and minimalist striping for a modern, architectural look.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all filter buttons and gallery items
            const filterButtons = document.querySelectorAll('.filter-button');
            const galleryItems = document.querySelectorAll('.gallery-item');
            const galleryGrid = document.querySelector('.gallery-grid');

            // Add click event to each filter button
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to clicked button
                    this.classList.add('active');

                    // Get the filter value
                    const filterValue = this.getAttribute('data-filter');

                    // Filter the gallery items
                    galleryItems.forEach(item => {
                        if (filterValue === 'all' || item.getAttribute('data-category').includes(filterValue)) {
                            item.classList.remove('hidden');
                        } else {
                            item.classList.add('hidden');
                        }
                    });

                    // Force grid layout to update and recalculate without spaces
                    setTimeout(() => {
                        galleryGrid.style.display = 'none';
                        // Force a reflow
                        void galleryGrid.offsetWidth;
                        galleryGrid.style.display = 'grid';
                    }, 10);
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const servicesLink = document.querySelector('.nav-link');
            servicesLink.addEventListener('click', function() {
                window.location.href = 'services.php';
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
            });
    </script>
    <?php include 'chat-widget.php'; ?>
</body>

</html>