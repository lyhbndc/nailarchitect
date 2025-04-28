<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="navbar.css">
    <link rel="icon" type="image/png" href="Assets/favicon.png">
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
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
            background-color: #d9bbb0;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .filter-button:hover {
            background-color: #ae9389;
        }
        
        .filter-button.active {
            background-color: #ae9389;
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
        <div class="page-title">Our Gallery</div>
        
        <div class="gallery-filters">
            <div class="filter-button active">All</div>
            <div class="filter-button">Manicure</div>
            <div class="filter-button">Pedicure</div>
            <div class="filter-button">Nail Art</div>
            <div class="filter-button">Extensions</div>
        </div>
        
        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="Assets/visit.png" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Pink Ombré</div>
                    <div class="overlay-desc">Gel manicure with gradient effect</div>
                </div>
            </div>
            
            <div class="gallery-item">
                <img src="Assets/visit.png" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">French Tips</div>
                    <div class="overlay-desc">Classic design with modern twist</div>
                </div>
            </div>
            
            <div class="gallery-item">
                <img src="Assets/visit.png" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Abstract Art</div>
                    <div class="overlay-desc">Freehand design with matte finish</div>
                </div>
            </div>
            
            <div class="gallery-item">
                <img src="Assets/visit.png" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Glitter Accents</div>
                    <div class="overlay-desc">Sparkle details on neutral base</div>
                </div>
            </div>
            
            <div class="gallery-item">
                <img src="Assets/visit.png" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Almond Shape</div>
                    <div class="overlay-desc">Elegant extensions with jewel tones</div>
                </div>
            </div>
            
            <div class="gallery-item">
                <img src="Assets/visit.png" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Minimalist Lines</div>
                    <div class="overlay-desc">Simple geometric patterns</div>
                </div>
            </div>
            
            <div class="gallery-item">
                <img src="Assets/visit.png" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Summer Colors</div>
                    <div class="overlay-desc">Bright tropical palette</div>
                </div>
            </div>
            
            <div class="gallery-item">
                <img src="Assets/visit.png" alt="Nail design" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="overlay-title">Marble Effect</div>
                    <div class="overlay-desc">Stone-inspired design in neutrals</div>
                </div>
            </div>
        </div>
        
        
    </div>
</body>
</html>
