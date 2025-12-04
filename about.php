<?php
require_once 'config.php';
requireLogin();
redirectOfficials();

$user_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];

// Get statistics from database
$stats = [
    'total_residents' => 0,
    'total_households' => 0,
    'male_residents' => 0,
    'female_residents' => 0,
    'senior_citizens' => 0,
    'registered_voters' => 0
];

// In real implementation, these would come from database
// For demo, we'll use sample data
$stats = [
    'total_residents' => 5236,
    'total_households' => 1248,
    'male_residents' => 2540,
    'female_residents' => 2696,
    'senior_citizens' => 489,
    'registered_voters' => 3845,
    'youth_population' => 1850,
    'children_population' => 987
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Dahat - About Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Include all CSS from home.php here */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        body.modal-open {
            overflow: hidden;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header Styles - Same as home.php */
        header {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .logo-container:hover {
            transform: translateY(-2px);
        }

        .logo {
            width: 60px;
            height: 60px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: #0d4a9e;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: rotate(10deg) scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }

        .logo-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .logo-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            padding: 8px 15px;
            border-radius: 30px;
            position: relative;
            overflow: hidden;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: left 0.5s ease;
            z-index: -1;
        }

        .nav-links a:hover::before {
            left: 0;
        }

        .nav-links a:hover, .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
        }

        .user-info i {
            font-size: 1.2rem;
        }

        .user-name {
            font-weight: 500;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .logout-btn {
            background-color: #ff4757;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .logout-btn:hover {
            background-color: #ff3838;
            transform: translateY(-3px);
            box-shadow: 0 7px 15px rgba(255, 71, 87, 0.3);
        }

        .logout-btn:active {
            transform: translateY(1px);
        }

        .mobile-menu {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .mobile-menu:hover {
            transform: scale(1.1);
        }

        /* Hero Section for About Us */
        .about-hero {
            padding: 5rem 0;
            background: linear-gradient(rgba(13, 74, 158, 0.9), rgba(13, 74, 158, 0.7)), 
                        url('https://images.unsplash.com/photo-1589829545856-d10d557cf95f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(13, 74, 158, 0.8) 0%, rgba(255, 126, 48, 0.6) 100%);
            z-index: 1;
        }

        .about-hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .about-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 1s ease;
        }

        .about-hero p {
            font-size: 1.3rem;
            line-height: 1.8;
            opacity: 0.95;
            animation: fadeInUp 1s ease 0.3s both;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* About Content Section */
        .about-content-section {
            padding: 5rem 0;
            background-color: #fff;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: #0d4a9e;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 4px;
            background: linear-gradient(to right, #0d4a9e, #ff7e30);
            border-radius: 2px;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        /* History Timeline */
        .timeline-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%);
        }

        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #0d4a9e, #ff7e30);
            border-radius: 2px;
        }

        .timeline-item {
            margin-bottom: 4rem;
            position: relative;
            width: 45%;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
        }

        .timeline-item:nth-child(even) {
            left: 55%;
        }

        .timeline-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            transition: all 0.4s ease;
        }

        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .timeline-year {
            position: absolute;
            top: -20px;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(13, 74, 158, 0.3);
        }

        .timeline-item:nth-child(odd) .timeline-year {
            right: -60px;
        }

        .timeline-item:nth-child(even) .timeline-year {
            left: -60px;
        }

        .timeline-content h3 {
            color: #0d4a9e;
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .timeline-content p {
            color: #555;
            line-height: 1.7;
        }

        /* Statistics Section */
        .stats-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect width="20" height="20" fill="none"/><circle cx="10" cy="10" r="1" fill="white" fill-opacity="0.1"/></svg>');
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            position: relative;
            z-index: 2;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .stat-item:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
            color: white;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Mission Vision Section */
        .mission-vision-section {
            padding: 5rem 0;
            background-color: #fff;
        }

        .mv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            margin-top: 3rem;
        }

        .mv-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #eaeaea;
        }

        .mv-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #0d4a9e, #ff7e30);
        }

        .mv-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .mv-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            transition: all 0.4s ease;
        }

        .mv-card:hover .mv-icon {
            transform: rotate(15deg) scale(1.1);
            background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%);
        }

        .mv-card h3 {
            color: #0d4a9e;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
        }

        .mv-card p {
            color: #555;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        /* Facilities Section */
        .facilities-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%);
        }

        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .facility-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
        }

        .facility-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .facility-image {
            height: 200px;
            overflow: hidden;
        }

        .facility-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .facility-card:hover .facility-image img {
            transform: scale(1.1);
        }

        .facility-content {
            padding: 1.5rem;
        }

        .facility-content h4 {
            color: #0d4a9e;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .facility-content p {
            color: #666;
            font-size: 0.95rem;
        }

        /* Map Section */
        .map-section {
            padding: 5rem 0;
            background-color: #fff;
        }

        .map-container {
            margin-top: 3rem;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            height: 500px;
        }

        /* Footer Styles - Same as home.php */
        footer {
            background-color: #0d1b2a;
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .footer-column h4 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #fff;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h4::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(to right, #0d4a9e, #ff7e30);
        }

        .footer-column p {
            color: #b0b7c3;
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: #b0b7c3;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            padding: 2px 0;
        }

        .footer-links a:hover {
            color: #ff7e30;
            transform: translateX(5px);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #1c2d3f;
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .social-links a:hover {
            background: linear-gradient(135deg, #0d4a9e 0%, #ff7e30 100%);
            transform: translateY(-5px) scale(1.1);
        }

        .newsletter-form {
            display: flex;
            margin-top: 1.5rem;
        }

        .newsletter-form input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 30px 0 0 30px;
            font-size: 0.95rem;
            background-color: #1c2d3f;
            color: white;
        }

        .newsletter-form input::placeholder {
            color: #b0b7c3;
        }

        .newsletter-form button {
            background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 0 30px 30px 0;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .newsletter-form button:hover {
            background: linear-gradient(135deg, #ff6a0d 0%, #ff8a3d 100%);
            transform: scale(1.05);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #1c2d3f;
            color: #b0b7c3;
            font-size: 0.9rem;
        }

        /* Mobile Navigation */
        .mobile-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            padding: 1rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .mobile-nav.active {
            display: block;
        }

        .mobile-nav-links {
            list-style: none;
            margin-bottom: 1rem;
        }

        .mobile-nav-links li {
            margin-bottom: 0.8rem;
        }

        .mobile-nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .mobile-nav-links a:hover, .mobile-nav-links a.active {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .mobile-user-section {
            padding: 0 15px;
            margin-top: 1rem;
        }

        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            margin-bottom: 1rem;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .footer-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .nav-links, .user-section {
                display: none;
            }
            
            .mobile-menu {
                display: block;
            }
            
            .timeline::before {
                left: 30px;
            }
            
            .timeline-item {
                width: 100%;
                left: 0 !important;
                padding-left: 70px;
            }
            
            .timeline-year {
                left: 0 !important;
                right: auto !important;
            }
        }

        @media (max-width: 768px) {
            .about-hero h1 {
                font-size: 2.5rem;
            }
            
            .about-hero p {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .mv-grid {
                grid-template-columns: 1fr;
            }
            
            .map-container {
                height: 400px;
            }
        }

        @media (max-width: 576px) {
            .footer-container {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .facilities-grid {
                grid-template-columns: 1fr;
            }
            
            .logo-text h1 {
                font-size: 1.5rem;
            }
            
            .logo {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .logout-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
            
            .stat-item h3 {
                font-size: 2.5rem;
            }
            
            .map-container {
                height: 300px;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #1e6bc4 0%, #2a7cd6 100%);
        }

        /* Counter Animation */
        .counter {
            display: inline-block;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo-container" id="logo">
                <div class="logo">BD</div>
                <div class="logo-text">
                    <h1>Barangay Dahat</h1>
                    <p>Welcome, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>!</p>
                </div>
            </div>
            
            <nav class="desktop-nav">
                <ul class="nav-links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="portfolio.php">Portfolio</a></li>
                    <li><a href="about.php" class="active">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </nav>
            
            <div class="user-section">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span class="user-name"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></span>
                </div>
                <form action="logout.php" method="POST" style="margin: 0;">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
            
            <div class="mobile-menu" id="mobileMenu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container about-hero-content">
            <h1>About Barangay Dahat</h1>
            <p>Welcome to Barangay Dahat, a progressive and vibrant community dedicated to serving its residents with excellence, integrity, and compassion. Established in 1985, we have grown into a model barangay known for our strong community spirit and commitment to sustainable development.</p>
        </div>
    </section>

    <!-- About Content Section -->
    <section class="about-content-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Story</h2>
                <p>The journey of Barangay Dahat from a small farming village to a thriving residential community</p>
            </div>
            
            <div class="about-content">
                <div style="max-width: 900px; margin: 0 auto;">
                    <div style="margin-bottom: 2rem;">
                        <h3 style="color: #0d4a9e; margin-bottom: 1rem; font-size: 1.5rem;">Historical Background</h3>
                        <p style="color: #555; line-height: 1.8; font-size: 1.05rem; margin-bottom: 1.5rem;">
                            Barangay Dahat traces its roots back to 1985 when it was officially recognized as an independent barangay. The name "Dahat" comes from the local word meaning "unity" or "coming together," which reflects the community's founding principle of collective progress through cooperation.
                        </p>
                        <p style="color: #555; line-height: 1.8; font-size: 1.05rem;">
                            Originally a small agricultural community with rice fields and vegetable farms, Dahat has transformed into a modern residential area while maintaining its green spaces and community-oriented values. Today, we are proud to be one of the most progressive barangays in the municipality, recognized for our innovative community programs and effective governance.
                        </p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 3rem;">
                        <div style="background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%); padding: 2rem; border-radius: 10px;">
                            <h4 style="color: #0d4a9e; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-bullseye"></i> Our Purpose
                            </h4>
                            <p style="color: #555; line-height: 1.7;">
                                To provide quality public service that empowers residents, fosters community development, and ensures a safe, clean, and prosperous environment for all.
                            </p>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%); padding: 2rem; border-radius: 10px;">
                            <h4 style="color: #0d4a9e; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-hands-helping"></i> Community Values
                            </h4>
                            <p style="color: #555; line-height: 1.7;">
                                We believe in transparency, accountability, community participation, environmental sustainability, and respect for diversity.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="section-title" style="color: white;">
                <h2>Community Statistics</h2>
                <p style="color: rgba(255, 255, 255, 0.9);">Updated population and demographic data as of <?php echo date('F Y'); ?></p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $stats['total_residents']; ?>">0</h3>
                    <p>Total Residents</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $stats['total_households']; ?>">0</h3>
                    <p>Households</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-male"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $stats['male_residents']; ?>">0</h3>
                    <p>Male Residents</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-female"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $stats['female_residents']; ?>">0</h3>
                    <p>Female Residents</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $stats['senior_citizens']; ?>">0</h3>
                    <p>Senior Citizens</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $stats['registered_voters']; ?>">0</h3>
                    <p>Registered Voters</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $stats['youth_population']; ?>">0</h3>
                    <p>Youth (13-21 years)</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-baby"></i>
                    </div>
                    <h3 class="counter" data-target="<?php echo $stats['children_population']; ?>">0</h3>
                    <p>Children (0-12 years)</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="mission-vision-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Mission & Vision</h2>
                <p>Guiding principles that shape our community service and development</p>
            </div>
            
            <div class="mv-grid">
                <div class="mv-card">
                    <div class="mv-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>To provide efficient, transparent, and accessible public services that empower residents, promote sustainable development, and enhance the quality of life in Barangay Dahat through responsive governance and active community participation.</p>
                </div>
                
                <div class="mv-card">
                    <div class="mv-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Our Vision</h3>
                    <p>To be a model barangay known for excellence in public service, environmental sustainability, social harmony, and economic progress, where every resident enjoys a safe, healthy, and prosperous life in a closely-knit community that values unity and mutual respect.</p>
                </div>
                
                <div class="mv-card">
                    <div class="mv-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Our Core Values</h3>
                    <p><strong>INTEGRITY:</strong> Honest and transparent governance<br>
                       <strong>SERVICE:</strong> Dedication to public service excellence<br>
                       <strong>UNITY:</strong> Strong community spirit and cooperation<br>
                       <strong>PROGRESS:</strong> Commitment to sustainable development<br>
                       <strong>RESPECT:</strong> Valuing diversity and human dignity</p>
                </div>
            </div>
        </div>
    </section>

    <!-- History Timeline -->
    <section class="timeline-section">
        <div class="container">
            <div class="section-title">
                <h2>Our History Timeline</h2>
                <p>Key milestones in the development of Barangay Dahat</p>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-year">1985</div>
                    <div class="timeline-content">
                        <h3>Founding of Barangay Dahat</h3>
                        <p>Barangay Dahat was officially established as an independent barangay through Municipal Ordinance No. 85-123. The first barangay hall was constructed on a 500-square meter lot donated by the founding families.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">1990</div>
                    <div class="timeline-content">
                        <h3>First Elementary School</h3>
                        <p>Dahat Elementary School was established, providing quality education to the growing number of children in the barangay. The school started with 5 teachers and 120 students.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">1998</div>
                    <div class="timeline-content">
                        <h3>Water System Installation</h3>
                        <p>A community water system was installed, providing clean and potable water to all households. This project significantly improved public health and sanitation in the barangay.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2005</div>
                    <div class="timeline-content">
                        <h3>Health Center Construction</h3>
                        <p>The Barangay Health Center was constructed, offering basic healthcare services, maternal care, immunization programs, and health education to residents.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2012</div>
                    <div class="timeline-content">
                        <h3>Barangay Hall Renovation</h3>
                        <p>The barangay hall underwent major renovation and expansion to accommodate growing administrative needs and improve service delivery to residents.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2018</div>
                    <div class="timeline-content">
                        <h3>Digital Transformation</h3>
                        <p>Implementation of the Barangay Management Information System (BMIS), digitizing records and introducing online services for residents.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-year">2023</div>
                    <div class="timeline-content">
                        <h3>Community Center Opening</h3>
                        <p>The new Multi-Purpose Community Center was inaugurated, providing facilities for events, trainings, sports, and social activities for all residents.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities Section -->
    <section class="facilities-section">
        <div class="container">
            <div class="section-title">
                <h2>Community Facilities</h2>
                <p>Modern amenities and infrastructure serving our residents</p>
            </div>
            
            <div class="facilities-grid">
                <div class="facility-card">
                    <div class="facility-image">
                        <img src="https://images.unsplash.com/photo-1589829545856-d10d557cf95f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Barangay Hall">
                    </div>
                    <div class="facility-content">
                        <h4>Barangay Hall</h4>
                        <p>Modern administrative center with complete facilities for efficient public service delivery.</p>
                    </div>
                </div>
                
                <div class="facility-card">
                    <div class="facility-image">
                        <img src="https://images.unsplash.com/photo-1516549655669-df4f6a6f8d64?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Health Center">
                    </div>
                    <div class="facility-content">
                        <h4>Health Center</h4>
                        <p>Fully-equipped health facility providing basic medical services and emergency care.</p>
                    </div>
                </div>
                
                <div class="facility-card">
                    <div class="facility-image">
                        <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Multi-Purpose Hall">
                    </div>
                    <div class="facility-content">
                        <h4>Multi-Purpose Hall</h4>
                        <p>Spacious venue for community events, meetings, celebrations, and social activities.</p>
                    </div>
                </div>
                
                <div class="facility-card">
                    <div class="facility-image">
                        <img src="https://images.unsplash.com/photo-1519861531473-920034658307?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Sports Complex">
                    </div>
                    <div class="facility-content">
                        <h4>Sports Complex</h4>
                        <p>Basketball court, volleyball court, and open spaces for various sports activities.</p>
                    </div>
                </div>
                
                <div class="facility-card">
                    <div class="facility-image">
                        <img src="https://images.unsplash.com/photo-1501281667305-0d4ebf58b37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Chapel">
                    </div>
                    <div class="facility-content">
                        <h4>Community Chapel</h4>
                        <p>Religious center for spiritual activities and community gatherings.</p>
                    </div>
                </div>
                
                <div class="facility-card">
                    <div class="facility-image">
                        <img src="https://images.unsplash.com/photo-1523059623039-a9ed027e7fad?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Playground">
                    </div>
                    <div class="facility-content">
                        <h4>Children's Playground</h4>
                        <p>Safe and modern playground equipment for children's recreation and development.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="section-title">
                <h2>Location & Boundaries</h2>
                <p>Geographical information and neighboring barangays</p>
            </div>
            
            <div class="map-container">
                <!-- Google Maps Embed -->
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.231032969614!2d121.000123!3d14.610000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b7c1a7b1b1b1%3A0x1b1b1b1b1b1b1b1b!2sBarangay%20Dahat!5e0!3m2!1sen!2sph!4v1681234567890!5m2!1sen!2sph" 
                    width="100%" 
                    height="100%" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
            
            <div style="margin-top: 3rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div style="background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%); padding: 1.5rem; border-radius: 10px;">
                    <h4 style="color: #0d4a9e; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-map-marker-alt"></i> Geographical Data
                    </h4>
                    <p style="color: #555; line-height: 1.7;">
                        <strong>Total Land Area:</strong> 85.5 hectares<br>
                        <strong>Classification:</strong> Urban Residential<br>
                        <strong>Elevation:</strong> 45 meters above sea level<br>
                        <strong>Distance to Town Proper:</strong> 3.5 kilometers
                    </p>
                </div>
                
                <div style="background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%); padding: 1.5rem; border-radius: 10px;">
                    <h4 style="color: #0d4a9e; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-route"></i> Boundaries
                    </h4>
                    <p style="color: #555; line-height: 1.7;">
                        <strong>North:</strong> Barangay San Isidro<br>
                        <strong>South:</strong> Barangay Sta. Cruz<br>
                        <strong>East:</strong> Barangay San Antonio<br>
                        <strong>West:</strong> Barangay San Jose
                    </p>
                </div>
                
                <div style="background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%); padding: 1.5rem; border-radius: 10px;">
                    <h4 style="color: #0d4a9e; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-sitemap"></i> Administrative Division
                    </h4>
                    <p style="color: #555; line-height: 1.7;">
                        <strong>Number of Puroks:</strong> 15<br>
                        <strong>Number of Zones:</strong> 4<br>
                        <strong>Number of Sitios:</strong> 3<br>
                        <strong>SK Chairperson:</strong> Maria Clara Santos
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-column">
                    <h4>Barangay Dahat</h4>
                    <p>Progress Through Unity. Serving our community with dedication and integrity since 1985.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="home.php">Home</a></li>
                        <li><a href="portfolio.php">Portfolio</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Community Info</h4>
                    <ul class="footer-links">
                        <li><a href="#">Barangay Officials</a></li>
                        <li><a href="#">Services Offered</a></li>
                        <li><a href="#">Community Projects</a></li>
                        <li><a href="#">Events Calendar</a></li>
                        <li><a href="#">Resident Directory</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Newsletter</h4>
                    <p>Subscribe to our newsletter to receive updates on barangay events and announcements.</p>
                    <div class="newsletter-form">
                        <input type="email" placeholder="Your email address">
                        <button type="submit">Subscribe</button>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Barangay Dahat. All Rights Reserved.</p>
                <p style="margin-top: 10px; font-size: 0.85rem; color: #8a9bb2;">
                    Barangay Hall, Main Road, Dahat | Phone: (02) 1234-5678 | Email: info@barangaydahat.ph
                </p>
            </div>
        </div>
    </footer>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <ul class="mobile-nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="portfolio.php">Portfolio</a></li>
            <li><a href="about.php" class="active">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
        <div class="mobile-user-section">
            <div class="mobile-user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></span>
            </div>
            <form action="logout.php" method="POST" style="width: 100%;">
                <button type="submit" class="logout-btn" style="width: 100%;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileNav = document.getElementById('mobileNav');
            
            mobileMenu.addEventListener('click', function() {
                mobileNav.classList.toggle('active');
                this.classList.toggle('active');
                
                const icon = this.querySelector('i');
                if (mobileNav.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileMenu.contains(event.target) && !mobileNav.contains(event.target) && mobileNav.classList.contains('active')) {
                    mobileNav.classList.remove('active');
                    const icon = mobileMenu.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
            
            // Counter animation for statistics
            const counters = document.querySelectorAll('.counter');
            const speed = 200; // The lower the slower
            
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    
                    // Lower inc to slow and higher to slow
                    const inc = target / speed;
                    
                    // Check if target is reached
                    if (count < target) {
                        // Add inc to count and output in counter
                        counter.innerText = Math.ceil(count + inc);
                        // Call function every ms
                        setTimeout(updateCount, 1);
                    } else {
                        counter.innerText = target;
                    }
                };
                
                // Start counter when element is in viewport
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCount();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });
                
                observer.observe(counter);
            });
            
            // Smooth scroll for logo click
            document.getElementById('logo').addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Add animation to timeline items when they come into view
            const timelineItems = document.querySelectorAll('.timeline-item');
            const timelineObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateX(0)';
                        timelineObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });
            
            // Set initial state and observe
            timelineItems.forEach(item => {
                item.style.opacity = '0';
                if (item.classList.contains('timeline-item') && window.innerWidth > 992) {
                    if (item.classList.contains('timeline-item:nth-child(odd)')) {
                        item.style.transform = 'translateX(-50px)';
                    } else {
                        item.style.transform = 'translateX(50px)';
                    }
                } else {
                    item.style.transform = 'translateX(30px)';
                }
                item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                timelineObserver.observe(item);
            });
            
            // Add animation to facility cards
            const facilityCards = document.querySelectorAll('.facility-card');
            const facilityObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        facilityObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });
            
            facilityCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                facilityObserver.observe(card);
            });
        });
        
        // Check authentication periodically
        function checkAuth() {
            fetch('check_auth.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => {
                    console.error('Auth check failed:', error);
                });
        }
        
        // Check authentication every 5 minutes
        setInterval(checkAuth, 300000);
        
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effect to mission/vision cards
            const mvCards = document.querySelectorAll('.mv-card');
            mvCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('.mv-icon');
                    icon.style.transform = 'rotate(15deg) scale(1.1)';
                });
                
                card.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('.mv-icon');
                    icon.style.transform = 'rotate(0) scale(1)';
                });
            });
            
            // Add click effect to stat items
            const statItems = document.querySelectorAll('.stat-item');
            statItems.forEach(item => {
                item.addEventListener('click', function() {
                    this.style.transform = 'translateY(-10px) scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-10px)';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>