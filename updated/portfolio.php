<?php
require_once 'config.php';
requireLogin();
redirectOfficials();

$user_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Dahat - Portfolio</title>
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

        /* Hero Section for Portfolio */
        .portfolio-hero {
            padding: 5rem 0;
            background: linear-gradient(rgba(13, 74, 158, 0.9), rgba(13, 74, 158, 0.7)), 
                        url('https://images.unsplash.com/photo-1563089145-599997674d42?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .portfolio-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(13, 74, 158, 0.8) 0%, rgba(255, 126, 48, 0.6) 100%);
            z-index: 1;
        }

        .portfolio-hero-content {
            position: relative;
            z-index: 2;
        }

        .portfolio-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 1s ease;
        }

        .portfolio-hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.9;
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

        /* Portfolio Section */
        .portfolio-section {
            padding: 5rem 0;
            background-color: #fff;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
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

        /* Filter Buttons */
        .portfolio-filter {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 12px 30px;
            background-color: #f0f5ff;
            color: #0d4a9e;
            border: 2px solid #e0e7ff;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .filter-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            transition: left 0.4s ease;
            z-index: -1;
        }

        .filter-btn:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(13, 74, 158, 0.2);
            border-color: transparent;
        }

        .filter-btn:hover::before {
            left: 0;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(13, 74, 158, 0.3);
        }

        /* Portfolio Grid */
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .portfolio-item {
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: 300px;
        }

        .portfolio-item:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .portfolio-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .portfolio-item:hover img {
            transform: scale(1.1);
        }

        .portfolio-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(13, 74, 158, 0.9), transparent);
            color: white;
            padding: 2rem;
            transform: translateY(100%);
            transition: transform 0.4s ease;
        }

        .portfolio-item:hover .portfolio-overlay {
            transform: translateY(0);
        }

        .portfolio-overlay h3 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .portfolio-overlay p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .portfolio-category {
            display: inline-block;
            background-color: rgba(255, 126, 48, 0.9);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Officials Section */
        .officials-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%);
        }

        .officials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .official-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
        }

        .official-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .official-image {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .official-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .official-card:hover .official-image img {
            transform: scale(1.1);
        }

        .official-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(13, 74, 158, 0.3);
        }

        .official-content {
            padding: 1.5rem;
            text-align: center;
        }

        .official-content h3 {
            color: #0d4a9e;
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        .official-content .position {
            color: #ff7e30;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .official-content p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .view-profile-btn {
            display: inline-block;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .view-profile-btn:hover {
            background: linear-gradient(135deg, #1e6bc4 0%, #2a7cd6 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(13, 74, 158, 0.3);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 800px;
            border-radius: 15px;
            overflow: hidden;
            animation: modalFade 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        @keyframes modalFade {
            from { opacity: 0; transform: scale(0.9) translateY(-20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .modal-header h3 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close-modal:hover {
            transform: rotate(90deg);
            background-color: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 2rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        /* Gallery Modal */
        .gallery-modal-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .gallery-main-image {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .gallery-main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-details {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%);
            border-radius: 10px;
        }

        .gallery-details h4 {
            color: #0d4a9e;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .gallery-details p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .gallery-meta {
            display: flex;
            gap: 2rem;
            color: #666;
            font-size: 0.9rem;
        }

        .gallery-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Officials Modal */
        .official-modal-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .official-modal-image {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            height: 350px;
        }

        .official-modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .official-modal-info h4 {
            color: #0d4a9e;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .official-position {
            color: #ff7e30;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: block;
        }

        .official-details {
            margin-bottom: 2rem;
        }

        .detail-item {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #555;
        }

        .detail-item i {
            color: #0d4a9e;
            width: 20px;
        }

        .official-bio {
            background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }

        .official-bio h5 {
            color: #0d4a9e;
            margin-bottom: 1rem;
            font-size: 1.2rem;
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
            
            .official-modal-content {
                grid-template-columns: 1fr;
            }
            
            .portfolio-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 20px auto;
            }
            
            .portfolio-hero h1 {
                font-size: 2.5rem;
            }
            
            .portfolio-hero p {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .portfolio-filter {
                gap: 0.5rem;
            }
            
            .filter-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .footer-container {
                grid-template-columns: 1fr;
            }
            
            .portfolio-grid {
                grid-template-columns: 1fr;
            }
            
            .officials-grid {
                grid-template-columns: 1fr;
            }
            
            .gallery-main-image {
                height: 300px;
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
        }

        /* Image Gallery */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .gallery-thumb {
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 150px;
        }

        .gallery-thumb:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                    <li><a href="portfolio.php" class="active">Portfolio</a></li>
                    <li><a href="about.php">About Us</a></li>
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
    <section class="portfolio-hero">
        <div class="container portfolio-hero-content">
            <h1>Our Community Gallery</h1>
            <p>Explore photos from our community events, sports activities, and see our dedicated barangay officials in action.</p>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="portfolio-section">
        <div class="container">
            <div class="section-title">
                <h2>Event Gallery</h2>
                <p>Browse through our collection of memorable moments from various barangay activities</p>
            </div>
            
            <div class="portfolio-filter">
                <button class="filter-btn active" data-filter="all">All Events</button>
                <button class="filter-btn" data-filter="sports">Sports</button>
                <button class="filter-btn" data-filter="mass">Mass & Religious</button>
                <button class="filter-btn" data-filter="festival">Festivals</button>
                <button class="filter-btn" data-filter="cleanup">Clean-up Drives</button>
                <button class="filter-btn" data-filter="medical">Medical Missions</button>
            </div>
            
            <div class="portfolio-grid">
                <!-- Sports Events -->
                <div class="portfolio-item" data-category="sports">
                    <img src="https://images.unsplash.com/photo-1546519638-68e109498ffc?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Basketball Tournament">
                    <div class="portfolio-overlay">
                        <h3>Annual Basketball Tournament</h3>
                        <p>Inter-zone basketball competition finals</p>
                        <span class="portfolio-category">Sports</span>
                    </div>
                </div>
                
                <div class="portfolio-item" data-category="sports">
                    <img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Volleyball Game">
                    <div class="portfolio-overlay">
                        <h3>Women's Volleyball League</h3>
                        <p>Championship match of the women's volleyball tournament</p>
                        <span class="portfolio-category">Sports</span>
                    </div>
                </div>
                
                <div class="portfolio-item" data-category="sports">
                    <img src="https://images.unsplash.com/photo-1511882150382-421056c89033?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Table Tennis">
                    <div class="portfolio-overlay">
                        <h3>Table Tennis Championship</h3>
                        <p>Youth table tennis competition winners</p>
                        <span class="portfolio-category">Sports</span>
                    </div>
                </div>
                
                <!-- Mass Events -->
                <div class="portfolio-item" data-category="mass">
                    <img src="https://images.unsplash.com/photo-1541643600914-78b084683601?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Community Mass">
                    <div class="portfolio-overlay">
                        <h3>Sunday Community Mass</h3>
                        <p>Weekly mass celebration at barangay chapel</p>
                        <span class="portfolio-category">Mass & Religious</span>
                    </div>
                </div>
                
                <div class="portfolio-item" data-category="mass">
                    <img src="https://images.unsplash.com/photo-1606055854320-12c6b0a4a52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Procession">
                    <div class="portfolio-overlay">
                        <h3>Flores de Mayo Procession</h3>
                        <p>Annual religious procession for Flores de Mayo</p>
                        <span class="portfolio-category">Mass & Religious</span>
                    </div>
                </div>
                
                <!-- Festival Events -->
                <div class="portfolio-item" data-category="festival">
                    <img src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Barangay Fiesta">
                    <div class="portfolio-overlay">
                        <h3>Barangay Fiesta Celebration</h3>
                        <p>Annual fiesta street dancing and parade</p>
                        <span class="portfolio-category">Festivals</span>
                    </div>
                </div>
                
                <!-- Clean-up Events -->
                <div class="portfolio-item" data-category="cleanup">
                    <img src="https://images.unsplash.com/photo-1593113598332-cd288d649433?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Clean-up Drive">
                    <div class="portfolio-overlay">
                        <h3>Community Clean-up Drive</h3>
                        <p>Monthly barangay-wide cleaning activity</p>
                        <span class="portfolio-category">Clean-up Drives</span>
                    </div>
                </div>
                
                <!-- Medical Events -->
                <div class="portfolio-item" data-category="medical">
                    <img src="https://images.unsplash.com/photo-1516549655669-df4f6a6f8d64?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Medical Mission">
                    <div class="portfolio-overlay">
                        <h3>Free Medical Check-up</h3>
                        <p>Quarterly medical mission for residents</p>
                        <span class="portfolio-category">Medical Missions</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Officials Section -->
    <section class="officials-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Barangay Officials</h2>
                <p>Meet the dedicated individuals serving our community</p>
            </div>
            
            <div class="officials-grid">
                <!-- Barangay Captain -->
                <div class="official-card" data-official="captain">
                    <div class="official-image">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Barangay Captain">
                        <div class="official-badge">Captain</div>
                    </div>
                    <div class="official-content">
                        <h3>Juan Dela Cruz</h3>
                        <p class="position">Barangay Captain</p>
                        <p>Serving the community since 2018 with dedication and integrity.</p>
                        <button class="view-profile-btn" onclick="openOfficialModal('captain')">View Profile</button>
                    </div>
                </div>
                
                <!-- Secretary -->
                <div class="official-card" data-official="secretary">
                    <div class="official-image">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Barangay Secretary">
                        <div class="official-badge">Secretary</div>
                    </div>
                    <div class="official-content">
                        <h3>Maria Santos</h3>
                        <p class="position">Barangay Secretary</p>
                        <p>Managing barangay records and documentation since 2016.</p>
                        <button class="view-profile-btn" onclick="openOfficialModal('secretary')">View Profile</button>
                    </div>
                </div>
                
                <!-- Treasurer -->
                <div class="official-card" data-official="treasurer">
                    <div class="official-image">
                        <img src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Barangay Treasurer">
                        <div class="official-badge">Treasurer</div>
                    </div>
                    <div class="official-content">
                        <h3>Roberto Garcia</h3>
                        <p class="position">Barangay Treasurer</p>
                        <p>Managing barangay funds and financial records with transparency.</p>
                        <button class="view-profile-btn" onclick="openOfficialModal('treasurer')">View Profile</button>
                    </div>
                </div>
                
                <!-- Kagawad -->
                <div class="official-card" data-official="kagawad">
                    <div class="official-image">
                        <img src="https://images.unsplash.com/photo-1564564321837-a57b7070ac4f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Barangay Kagawad">
                        <div class="official-badge">Kagawad</div>
                    </div>
                    <div class="official-content">
                        <h3>Antonio Reyes</h3>
                        <p class="position">Barangay Kagawad</p>
                        <p>Committee chair for peace and order, serving since 2019.</p>
                        <button class="view-profile-btn" onclick="openOfficialModal('kagawad')">View Profile</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Modal -->
    <div class="modal" id="galleryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="galleryTitle">Event Title</h3>
                <button class="close-modal" id="closeGalleryModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="gallery-modal-content">
                    <div class="gallery-main-image">
                        <img id="mainGalleryImage" src="" alt="">
                    </div>
                    <div class="gallery-details">
                        <h4 id="galleryEventName">Event Name</h4>
                        <p id="galleryDescription">Event description will appear here.</p>
                        <div class="gallery-meta">
                            <span><i class="far fa-calendar"></i> <span id="galleryDate">December 15, 2023</span></span>
                            <span><i class="fas fa-users"></i> <span id="galleryParticipants">150 Participants</span></span>
                            <span><i class="fas fa-tag"></i> <span id="galleryCategory">Sports</span></span>
                        </div>
                    </div>
                    
                    <!-- Additional Images -->
                    <div class="image-gallery" id="additionalImages">
                        <!-- Additional images will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Official Modal -->
    <div class="modal" id="officialModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="officialModalTitle">Official Name</h3>
                <button class="close-modal" id="closeOfficialModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="official-modal-content" id="officialModalContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

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
                    <h4>Gallery Categories</h4>
                    <ul class="footer-links">
                        <li><a href="#" data-filter="sports">Sports Events</a></li>
                        <li><a href="#" data-filter="mass">Mass & Religious</a></li>
                        <li><a href="#" data-filter="festival">Festivals</a></li>
                        <li><a href="#" data-filter="cleanup">Clean-up Drives</a></li>
                        <li><a href="#" data-filter="medical">Medical Missions</a></li>
                        <li><a href="#" data-filter="all">View All</a></li>
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
            </div>
        </div>
    </footer>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <ul class="mobile-nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="portfolio.php" class="active">Portfolio</a></li>
            <li><a href="about.php">About Us</a></li>
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
        // Portfolio Filtering
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const portfolioItems = document.querySelectorAll('.portfolio-item');
            const footerLinks = document.querySelectorAll('.footer-links a[data-filter]');
            
            // Filter button click handler
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const filterValue = this.getAttribute('data-filter');
                    
                    // Filter portfolio items
                    portfolioItems.forEach(item => {
                        if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                            item.style.display = 'block';
                            setTimeout(() => {
                                item.style.opacity = '1';
                                item.style.transform = 'translateY(0)';
                            }, 100);
                        } else {
                            item.style.opacity = '0';
                            item.style.transform = 'translateY(20px)';
                            setTimeout(() => {
                                item.style.display = 'none';
                            }, 300);
                        }
                    });
                });
            });
            
            // Footer link filter handlers
            footerLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filterValue = this.getAttribute('data-filter');
                    
                    // Find and click corresponding filter button
                    filterButtons.forEach(button => {
                        if (button.getAttribute('data-filter') === filterValue) {
                            button.click();
                            
                            // Smooth scroll to portfolio section
                            document.querySelector('.portfolio-section').scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            });
            
            // Portfolio item click handlers
            portfolioItems.forEach(item => {
                item.addEventListener('click', function() {
                    const category = this.getAttribute('data-category');
                    const title = this.querySelector('h3').textContent;
                    const description = this.querySelector('p').textContent;
                    const imageSrc = this.querySelector('img').src;
                    
                    openGalleryModal(title, description, imageSrc, category);
                });
            });
            
            // Mobile menu toggle
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
        });
        
        // Gallery Data
        const galleryData = {
            sports: [
                {
                    title: "Annual Basketball Tournament",
                    description: "The finals of our annual inter-zone basketball competition featuring teams from all 15 zones of Barangay Dahat. This year's championship was won by Zone 7 after an exciting overtime game.",
                    date: "November 15, 2023",
                    participants: "200 Players, 500 Spectators",
                    mainImage: "https://images.unsplash.com/photo-1546519638-68e109498ffc?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                    additionalImages: [
                        "https://images.unsplash.com/photo-1519861531473-920034658307?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                        "https://images.unsplash.com/photo-1549060279-7e168fce7090?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                        "https://images.unsplash.com/photo-1515523110800-9415d13b84a8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"
                    ]
                },
                {
                    title: "Women's Volleyball League",
                    description: "Championship match of the women's volleyball tournament showcasing the athletic skills of our female residents. The event promotes women's participation in sports.",
                    date: "October 28, 2023",
                    participants: "12 Teams, 300 Spectators",
                    mainImage: "https://images.unsplash.com/photo-1575361204480-aadea25e6e68?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                    additionalImages: [
                        "https://images.unsplash.com/photo-1622279457486-62dcc4a431cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                        "https://images.unsplash.com/photo-1592656094267-764a8c9f3b3c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"
                    ]
                }
            ],
            mass: [
                {
                    title: "Sunday Community Mass",
                    description: "Weekly mass celebration held at the Barangay Dahat chapel, bringing together residents for spiritual nourishment and community bonding.",
                    date: "Every Sunday",
                    participants: "Regular Attendance: 150+",
                    mainImage: "https://images.unsplash.com/photo-1541643600914-78b084683601?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                    additionalImages: [
                        "https://images.unsplash.com/photo-1501281667305-0d4ebf58b37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                        "https://images.unsplash.com/photo-1563642421748-0a8f14f2c0a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"
                    ]
                }
            ],
            festival: [
                {
                    title: "Barangay Fiesta Celebration",
                    description: "Annual fiesta celebration featuring street dancing, cultural presentations, and a grand parade showcasing the rich traditions of Barangay Dahat.",
                    date: "May 15, 2023",
                    participants: "Whole Community",
                    mainImage: "https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                    additionalImages: [
                        "https://images.unsplash.com/photo-1511795409834-ef04bbd61622?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                        "https://images.unsplash.com/photo-1531058020387-3be344556be6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"
                    ]
                }
            ]
        };
        
        // Officials Data
        const officialsData = {
            captain: {
                name: "Juan Dela Cruz",
                position: "Barangay Captain",
                image: "https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80",
                email: "captain.dahat@barangay.ph",
                phone: "0917-123-4567",
                term: "2018 - Present",
                bio: "Captain Juan Dela Cruz has been serving Barangay Dahat since 2018. With over 20 years of public service experience, he has implemented numerous community development projects including the renovation of the barangay hall, establishment of the community learning center, and improvement of drainage systems. His leadership focuses on transparency, community participation, and sustainable development."
            },
            secretary: {
                name: "Maria Santos",
                position: "Barangay Secretary",
                image: "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80",
                email: "secretary.dahat@barangay.ph",
                phone: "0918-234-5678",
                term: "2016 - Present",
                bio: "Maria Santos has been the Barangay Secretary since 2016. She manages all barangay documentation, minutes of meetings, and official correspondence. With her background in public administration, she has modernized the barangay's record-keeping system and implemented digital archiving for better accessibility and transparency."
            },
            treasurer: {
                name: "Roberto Garcia",
                position: "Barangay Treasurer",
                image: "https://images.unsplash.com/photo-1582750433449-648ed127bb54?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80",
                email: "treasurer.dahat@barangay.ph",
                phone: "0919-345-6789",
                term: "2019 - Present",
                bio: "Roberto Garcia serves as the Barangay Treasurer, overseeing all financial matters with integrity and transparency. He has implemented a computerized accounting system that provides real-time financial reporting. Under his management, the barangay has maintained clean financial records and received commendations for financial management excellence."
            },
            kagawad: {
                name: "Antonio Reyes",
                position: "Barangay Kagawad",
                image: "https://images.unsplash.com/photo-1564564321837-a57b7070ac4f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80",
                email: "kagawad.reyes@barangay.ph",
                phone: "0920-456-7890",
                term: "2019 - Present",
                bio: "Kagawad Antonio Reyes chairs the Committee on Peace and Order. He has been instrumental in establishing the Barangay Peacekeeping Action Team (BPAT) and implementing the community-based drug rehabilitation program. His efforts have contributed to a 40% reduction in crime rates within the barangay since 2019."
            }
        };
        
        // Open Gallery Modal
        function openGalleryModal(title, description, imageSrc, category) {
            const modal = document.getElementById('galleryModal');
            const data = galleryData[category] ? galleryData[category][0] : {
                title: title,
                description: description,
                date: "Recent Event",
                participants: "Community Participants",
                mainImage: imageSrc,
                additionalImages: []
            };
            
            document.getElementById('galleryTitle').textContent = title;
            document.getElementById('mainGalleryImage').src = imageSrc;
            document.getElementById('mainGalleryImage').alt = title;
            document.getElementById('galleryEventName').textContent = title;
            document.getElementById('galleryDescription').textContent = description;
            document.getElementById('galleryDate').textContent = data.date;
            document.getElementById('galleryParticipants').textContent = data.participants;
            document.getElementById('galleryCategory').textContent = category.charAt(0).toUpperCase() + category.slice(1);
            
            // Load additional images
            const additionalImagesContainer = document.getElementById('additionalImages');
            additionalImagesContainer.innerHTML = '';
            
            if (data.additionalImages && data.additionalImages.length > 0) {
                data.additionalImages.forEach(imgSrc => {
                    const thumb = document.createElement('div');
                    thumb.className = 'gallery-thumb';
                    thumb.innerHTML = `<img src="${imgSrc}" alt="${title}">`;
                    thumb.addEventListener('click', function() {
                        document.getElementById('mainGalleryImage').src = imgSrc;
                    });
                    additionalImagesContainer.appendChild(thumb);
                });
            }
            
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }
        
        // Open Official Modal
        function openOfficialModal(officialType) {
            const modal = document.getElementById('officialModal');
            const data = officialsData[officialType];
            
            if (!data) return;
            
            document.getElementById('officialModalTitle').textContent = data.name;
            
            const content = `
                <div class="official-modal-image">
                    <img src="${data.image}" alt="${data.name}">
                </div>
                <div class="official-modal-info">
                    <h4>${data.name}</h4>
                    <span class="official-position">${data.position}</span>
                    
                    <div class="official-details">
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span>${data.email}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span>${data.phone}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Term: ${data.term}</span>
                        </div>
                    </div>
                    
                    <div class="official-bio">
                        <h5>About</h5>
                        <p>${data.bio}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('officialModalContent').innerHTML = content;
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }
        
        // Close Modal Functions
        document.getElementById('closeGalleryModal').addEventListener('click', function() {
            closeModal('galleryModal');
        });
        
        document.getElementById('closeOfficialModal').addEventListener('click', function() {
            closeModal('officialModal');
        });
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('galleryModal')) {
                closeModal('galleryModal');
            }
            if (event.target === document.getElementById('officialModal')) {
                closeModal('officialModal');
            }
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal('galleryModal');
                closeModal('officialModal');
                
                // Close mobile nav
                const mobileNav = document.getElementById('mobileNav');
                if (mobileNav.classList.contains('active')) {
                    mobileNav.classList.remove('active');
                    const icon = document.querySelector('#mobileMenu i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
        
        // Smooth scroll for logo click
        document.getElementById('logo').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
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
    </script>
</body>
</html>