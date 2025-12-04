<?php
require_once 'config.php';
requireLogin();
redirectOfficials();

$user_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];
$user_email = '';
$user_phone = '';

// Get user contact info from database
$user_sql = "SELECT email, phone FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_sql);
if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $user_email = $user_data['email'];
    $user_phone = $user_data['phone'];
}

// Handle contact form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    $priority = $conn->real_escape_string($_POST['priority']);
    
    // Validate form
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Save to database
        $sql = "INSERT INTO contact_messages (user_id, name, email, subject, message, priority, status) 
                VALUES ('$user_id', '$name', '$email', '$subject', '$message', '$priority', 'new')";
        
        if ($conn->query($sql)) {
            $success_message = "Your message has been sent successfully! We'll get back to you within 24 hours.";
            
            // Send email notification (in real implementation)
            // mail($email, "Message Received - Barangay Dahat", "Thank you for contacting us...");
            
            // Clear form
            $_POST = array();
        } else {
            $error_message = "Failed to send message. Please try again.";
        }
    }
}

// Create contact_messages table if not exists
$table_sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('new', 'read', 'replied', 'resolved') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL,
    reply_message TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
$conn->query($table_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Dahat - Contact Us</title>
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

        /* Hero Section for Contact */
        .contact-hero {
            padding: 5rem 0;
            background: linear-gradient(rgba(13, 74, 158, 0.9), rgba(13, 74, 158, 0.7)), 
                        url('https://images.unsplash.com/photo-1553877522-43269d4ea984?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(13, 74, 158, 0.8) 0%, rgba(255, 126, 48, 0.6) 100%);
            z-index: 1;
        }

        .contact-hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .contact-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 1s ease;
        }

        .contact-hero p {
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

        /* Contact Section */
        .contact-section {
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

        /* Contact Container */
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            margin-top: 3rem;
        }

        @media (max-width: 992px) {
            .contact-container {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
        }

        /* Contact Info */
        .contact-info {
            background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .contact-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, #0d4a9e, #ff7e30);
        }

        .contact-info h3 {
            color: #0d4a9e;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            position: relative;
            padding-left: 15px;
        }

        .contact-info h3::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 30px;
            background: linear-gradient(to bottom, #0d4a9e, #ff7e30);
            border-radius: 2px;
        }

        /* Contact Items */
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: 1px solid #eaeaea;
        }

        .contact-item:hover {
            transform: translateX(10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #0d4a9e;
        }

        .contact-item i {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .contact-item:hover i {
            background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%);
            transform: rotate(15deg) scale(1.1);
        }

        .contact-item-content h4 {
            color: #0d4a9e;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .contact-item-content p {
            color: #555;
            line-height: 1.7;
        }

        .contact-item-content a {
            color: #0d4a9e;
            text-decoration: none;
            transition: color 0.3s ease;
            display: inline-block;
            margin-top: 5px;
        }

        .contact-item-content a:hover {
            color: #ff7e30;
            text-decoration: underline;
        }

        /* Emergency Contact */
        .emergency-contact {
            margin-top: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, #fff0f0 0%, #ffe6e6 100%);
            border-radius: 10px;
            border-left: 5px solid #ff4757;
            position: relative;
            overflow: hidden;
        }

        .emergency-contact h4 {
            color: #ff4757;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .emergency-contact p {
            color: #721c24;
            line-height: 1.8;
            font-weight: 500;
        }

        .emergency-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ff4757;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            transform: rotate(15deg);
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3);
        }

        /* Contact Form */
        .contact-form {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #eaeaea;
        }

        .contact-form h3 {
            color: #0d4a9e;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
            font-size: 1rem;
        }

        .form-group label .required {
            color: #ff4757;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }

        .form-control:focus {
            border-color: #0d4a9e;
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(13, 74, 158, 0.1);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d4a9e' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 40px;
        }

        /* Priority Badges */
        .priority-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

        .priority-option {
            flex: 1;
            min-width: 120px;
        }

        .priority-option input {
            display: none;
        }

        .priority-option label {
            display: block;
            padding: 12px 15px;
            background-color: #f0f5ff;
            border: 2px solid #e0e7ff;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #555;
        }

        .priority-option input:checked + label {
            border-color: #0d4a9e;
            background-color: #e8f1ff;
            color: #0d4a9e;
            font-weight: 600;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 74, 158, 0.1);
        }

        .priority-option label.low {
            border-color: #4cd964;
        }

        .priority-option label.normal {
            border-color: #0d4a9e;
        }

        .priority-option label.high {
            border-color: #ffa502;
        }

        .priority-option label.urgent {
            border-color: #ff4757;
        }

        .priority-option input:checked + label.low {
            background-color: #e8f8ed;
            color: #27ae60;
        }

        .priority-option input:checked + label.normal {
            background-color: #e8f1ff;
            color: #0d4a9e;
        }

        .priority-option input:checked + label.high {
            background-color: #fff8e1;
            color: #e67e22;
        }

        .priority-option input:checked + label.urgent {
            background-color: #ffeaea;
            color: #e74c3c;
        }

        /* Submit Button */
        .btn-submit {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            width: 100%;
            padding: 16px;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #1e6bc4 0%, #2a7cd6 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(13, 74, 158, 0.3);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        .btn-submit:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Office Hours */
        .office-hours {
            margin-top: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            border: 1px solid #eaeaea;
        }

        .office-hours h4 {
            color: #0d4a9e;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hours-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .hour-item {
            padding: 0.8rem;
            background: #f9f9f9;
            border-radius: 5px;
            font-size: 0.95rem;
        }

        .hour-item .day {
            font-weight: 600;
            color: #333;
        }

        .hour-item .time {
            color: #0d4a9e;
            font-weight: 500;
        }

        /* Social Media */
        .social-contact {
            margin-top: 2rem;
            text-align: center;
        }

        .social-contact h4 {
            color: #0d4a9e;
            margin-bottom: 1rem;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .social-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .social-icon.facebook {
            background: linear-gradient(135deg, #1877f2 0%, #0d4a9e 100%);
        }

        .social-icon.messenger {
            background: linear-gradient(135deg, #006aff 0%, #0084ff 100%);
        }

        .social-icon.email {
            background: linear-gradient(135deg, #ea4335 0%, #d14836 100%);
        }

        .social-icon.whatsapp {
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        }

        .social-icon.viber {
            background: linear-gradient(135deg, #7360f2 0%, #6652e0 100%);
        }

        .social-icon:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Map Section */
        .map-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%);
        }

        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            height: 400px;
            margin-top: 2rem;
        }

        /* FAQ Section */
        .faq-section {
            padding: 5rem 0;
            background-color: #fff;
        }

        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .faq-item {
            margin-bottom: 1rem;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .faq-question {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8faff 0%, #e8f1ff 100%);
            color: #0d4a9e;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .faq-question:hover {
            background: linear-gradient(135deg, #e8f1ff 0%, #d8e7ff 100%);
        }

        .faq-question i {
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            background: white;
            transition: all 0.3s ease;
        }

        .faq-item.active .faq-answer {
            padding: 1.5rem;
            max-height: 1000px;
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

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: linear-gradient(135deg, #4cd964 0%, #2ecc71 100%);
            color: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            z-index: 3000;
            display: none;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-width: 400px;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .notification.error {
            background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%);
        }

        .notification.warning {
            background: linear-gradient(135deg, #ffa502 0%, #ff7f00 100%);
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
            
            .contact-container {
                grid-template-columns: 1fr;
            }
            
            .priority-options {
                flex-direction: column;
            }
            
            .priority-option {
                min-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .contact-hero h1 {
                font-size: 2.5rem;
            }
            
            .contact-hero p {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .contact-info, .contact-form {
                padding: 2rem;
            }
            
            .map-container {
                height: 300px;
            }
            
            .hours-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .footer-container {
                grid-template-columns: 1fr;
            }
            
            .contact-item {
                flex-direction: column;
                text-align: center;
            }
            
            .contact-item i {
                margin: 0 auto;
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
            
            .social-icons {
                gap: 0.5rem;
            }
            
            .social-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
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

        /* Character Counter */
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .char-counter.warning {
            color: #ffa502;
        }

        .char-counter.error {
            color: #ff4757;
        }
    </style>
</head>
<body>
    <?php if ($success_message): ?>
        <div class="notification" id="successNotification">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_message; ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="notification error" id="errorNotification">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_message; ?></span>
        </div>
    <?php endif; ?>

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
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php" class="active">Contact Us</a></li>
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
    <section class="contact-hero">
        <div class="container contact-hero-content">
            <h1>Get In Touch With Us</h1>
            <p>Have questions, concerns, or suggestions? We're here to help! Reach out to Barangay Dahat through any of our communication channels or fill out the contact form below.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="section-title">
                <h2>Contact Information</h2>
                <p>Multiple ways to reach Barangay Dahat officials and staff</p>
            </div>
            
            <div class="contact-container">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h3>Contact Details</h3>
                    
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-item-content">
                            <h4>Barangay Hall Address</h4>
                            <p>Barangay Dahat Hall<br>
                               Main Road, Purok 1, Dahat<br>
                               City/Municipality, Province 1234</p>
                            <a href="#" id="getDirections">
                                <i class="fas fa-directions"></i> Get Directions
                            </a>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div class="contact-item-content">
                            <h4>Phone Numbers</h4>
                            <p><strong>Office Landline:</strong> (02) 1234-5678<br>
                               <strong>Globe:</strong> 0917-123-4567<br>
                               <strong>Smart:</strong> 0918-234-5678<br>
                               <strong>Sun:</strong> 0933-345-6789</p>
                            <a href="tel:+63212345678">
                                <i class="fas fa-phone-alt"></i> Call Now
                            </a>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-item-content">
                            <h4>Email Addresses</h4>
                            <p><strong>General Inquiries:</strong> info@barangaydahat.ph<br>
                               <strong>Concerns & Complaints:</strong> concerns@barangaydahat.ph<br>
                               <strong>Document Requests:</strong> documents@barangaydahat.ph<br>
                               <strong>Barangay Captain:</strong> captain@barangaydahat.ph</p>
                            <a href="mailto:info@barangaydahat.ph">
                                <i class="fas fa-paper-plane"></i> Send Email
                            </a>
                        </div>
                    </div>
                    
                    <!-- Office Hours -->
                    <div class="office-hours">
                        <h4><i class="far fa-clock"></i> Office Hours</h4>
                        <div class="hours-grid">
                            <div class="hour-item">
                                <div class="day">Monday - Friday</div>
                                <div class="time">8:00 AM - 5:00 PM</div>
                            </div>
                            <div class="hour-item">
                                <div class="day">Saturday</div>
                                <div class="time">8:00 AM - 12:00 PM</div>
                            </div>
                            <div class="hour-item">
                                <div class="day">Sunday</div>
                                <div class="time">Emergency Only</div>
                            </div>
                            <div class="hour-item">
                                <div class="day">Holidays</div>
                                <div class="time">Closed</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Emergency Contact -->
                    <div class="emergency-contact">
                        <div class="emergency-badge">EMERGENCY</div>
                        <h4><i class="fas fa-exclamation-triangle"></i> Emergency Hotlines</h4>
                        <p><strong>Police Station:</strong> 117 / (02) 123-4567<br>
                           <strong>Fire Department:</strong> 160 / (02) 987-6543<br>
                           <strong>Ambulance:</strong> (02) 555-1234 / 0919-876-5432<br>
                           <strong>Disaster Management:</strong> (02) 777-8888</p>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="social-contact">
                        <h4>Connect With Us</h4>
                        <div class="social-icons">
                            <a href="#" class="social-icon facebook" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-icon messenger" title="Messenger">
                                <i class="fab fa-facebook-messenger"></i>
                            </a>
                            <a href="#" class="social-icon email" title="Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="#" class="social-icon whatsapp" title="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="#" class="social-icon viber" title="Viber">
                                <i class="fab fa-viber"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form">
                    <h3>Send Us a Message</h3>
                    <form method="POST" action="" id="contactForm">
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required 
                                   placeholder="Enter your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($user_name); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" required 
                                   placeholder="your.email@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user_email); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number (Optional)</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   placeholder="0912 345 6789" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($user_phone); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <select id="subject" name="subject" class="form-control" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Barangay Clearance" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Barangay Clearance') ? 'selected' : ''; ?>>Barangay Clearance Request</option>
                                <option value="Business Permit" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Business Permit') ? 'selected' : ''; ?>>Business Permit Inquiry</option>
                                <option value="Complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Complaint') ? 'selected' : ''; ?>>Complaint or Concern</option>
                                <option value="Suggestion" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Suggestion') ? 'selected' : ''; ?>>Suggestion or Feedback</option>
                                <option value="Event Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Event Inquiry') ? 'selected' : ''; ?>>Event or Activity Inquiry</option>
                                <option value="Document Request" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Document Request') ? 'selected' : ''; ?>>Document Request</option>
                                <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority">Priority Level</label>
                            <div class="priority-options">
                                <div class="priority-option">
                                    <input type="radio" id="priority-low" name="priority" value="low" <?php echo (!isset($_POST['priority']) || $_POST['priority'] == 'low') ? 'checked' : ''; ?>>
                                    <label for="priority-low" class="low">Low</label>
                                </div>
                                <div class="priority-option">
                                    <input type="radio" id="priority-normal" name="priority" value="normal" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'normal') ? 'checked' : ''; ?>>
                                    <label for="priority-normal" class="normal">Normal</label>
                                </div>
                                <div class="priority-option">
                                    <input type="radio" id="priority-high" name="priority" value="high" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'high') ? 'checked' : ''; ?>>
                                    <label for="priority-high" class="high">High</label>
                                </div>
                                <div class="priority-option">
                                    <input type="radio" id="priority-urgent" name="priority" value="urgent" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'urgent') ? 'checked' : ''; ?>>
                                    <label for="priority-urgent" class="urgent">Urgent</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" class="form-control" required 
                                      placeholder="Please type your message here... Minimum 20 characters." 
                                      rows="6"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            <div class="char-counter" id="charCounter">0/1000 characters</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="attachment">Attachment (Optional)</label>
                            <input type="file" id="attachment" name="attachment" class="form-control" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Maximum file size: 5MB. Allowed formats: PDF, DOC, JPG, PNG
                            </small>
                        </div>
                        
                        <button type="submit" name="contact_submit" class="btn-submit" id="submitBtn">
                            <span id="submitText">Send Message</span>
                            <span id="submitSpinner" class="spinner" style="display: none;"></span>
                        </button>
                        
                        <p style="text-align: center; margin-top: 1.5rem; color: #666; font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i> We aim to respond within 24 hours on weekdays.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div class="section-title">
                <h2>Find Our Location</h2>
                <p>Visit us at the Barangay Dahat Hall or use the map for directions</p>
            </div>
            
            <div class="map-container">
                <!-- Google Maps Embed -->
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.231032969614!2d121.000123!3d14.610000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b7c1a7b1b1b1%3A0x1b1b1b1b1b1b1b1b!2sBarangay%20Dahat%20Hall!5e0!3m2!1sen!2sph!4v1681234567890!5m2!1sen!2sph" 
                    width="100%" 
                    height="100%" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 2rem; flex-wrap: wrap;">
                <button id="openMaps" class="btn-submit" style="width: auto; padding: 12px 30px;">
                    <i class="fas fa-map-marked-alt"></i> Open in Google Maps
                </button>
                <button id="getRoute" class="btn-submit" style="width: auto; padding: 12px 30px; background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%);">
                    <i class="fas fa-route"></i> Get Directions
                </button>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to common questions about contacting Barangay Dahat</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>What are the operating hours of the Barangay Hall?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>The Barangay Hall is open from Monday to Friday, 8:00 AM to 5:00 PM, and on Saturdays from 8:00 AM to 12:00 PM. We are closed on Sundays and public holidays, but emergency services remain available.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How long does it take to get a response to my message?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We aim to respond to all inquiries within 24 hours on weekdays. Urgent matters are prioritized and may receive a response within 2-4 hours during office hours. Messages received on weekends or holidays will be addressed on the next business day.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>What documents do I need for barangay clearance?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>For barangay clearance, you typically need: 1) Valid ID (any government-issued ID), 2) Proof of residency (utility bill, lease agreement), 3) Completed application form (available at the barangay hall), and 4) Purpose of clearance. Processing usually takes 1-3 business days.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Can I follow up on my complaint or request?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you can follow up on your request by calling our office, sending an email, or visiting the barangay hall. Please have your reference number ready (provided when you submitted your request) for faster assistance.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Is there a way to contact barangay officials after hours?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>For emergencies only, you can contact our 24/7 hotline at 0919-876-5432. For non-emergency matters after hours, you may send an email or use our contact form, and we will respond on the next business day.</p>
                    </div>
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
                        <li><a href="#">Services</a></li>
                        <li><a href="#">Documents</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Contact Info</h4>
                    <ul class="footer-links">
                        <li><a href="tel:+63212345678"><i class="fas fa-phone"></i> (02) 1234-5678</a></li>
                        <li><a href="mailto:info@barangaydahat.ph"><i class="fas fa-envelope"></i> info@barangaydahat.ph</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> Barangay Dahat Hall</a></li>
                        <li><a href="#"><i class="fas fa-clock"></i> Mon-Fri: 8AM-5PM</a></li>
                        <li><a href="#"><i class="fas fa-exclamation-triangle"></i> Emergency: 0919-876-5432</a></li>
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
                    <i class="fas fa-shield-alt"></i> Your privacy and security are important to us. All communications are confidential.
                </p>
            </div>
        </div>
    </footer>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <ul class="mobile-nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="portfolio.php">Portfolio</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php" class="active">Contact Us</a></li>
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

    <!-- Notification Template -->
    <div class="notification" id="notificationTemplate" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <span id="notificationText">Your message has been sent successfully!</span>
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
            
            // Character counter for message
            const messageInput = document.getElementById('message');
            const charCounter = document.getElementById('charCounter');
            
            messageInput.addEventListener('input', function() {
                const length = this.value.length;
                charCounter.textContent = `${length}/1000 characters`;
                
                if (length < 20) {
                    charCounter.className = 'char-counter error';
                } else if (length > 900) {
                    charCounter.className = 'char-counter warning';
                } else {
                    charCounter.className = 'char-counter';
                }
            });
            
            // Initialize character counter
            messageInput.dispatchEvent(new Event('input'));
            
            // Form validation
            const contactForm = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            contactForm.addEventListener('submit', function(e) {
                const message = messageInput.value.trim();
                
                if (message.length < 20) {
                    e.preventDefault();
                    showNotification('Message must be at least 20 characters long.', 'error');
                    messageInput.focus();
                    return false;
                }
                
                // Show loading state
                submitText.style.display = 'none';
                submitSpinner.style.display = 'inline-block';
                submitBtn.disabled = true;
                
                // In real implementation, this would be an AJAX call
                // For now, just allow form submission
                return true;
            });
            
            // FAQ toggle
            const faqQuestions = document.querySelectorAll('.faq-question');
            faqQuestions.forEach(question => {
                question.addEventListener('click', function() {
                    const item = this.parentElement;
                    item.classList.toggle('active');
                });
            });
            
            // Get Directions button
            document.getElementById('getDirections').addEventListener('click', function(e) {
                e.preventDefault();
                window.open('https://www.google.com/maps/dir//Barangay+Dahat+Hall', '_blank');
            });
            
            // Open Maps button
            document.getElementById('openMaps').addEventListener('click', function() {
                window.open('https://www.google.com/maps/place/Barangay+Dahat+Hall', '_blank');
            });
            
            // Get Route button
            document.getElementById('getRoute').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        window.open(`https://www.google.com/maps/dir/${lat},${lng}/Barangay+Dahat+Hall`, '_blank');
                    }, function() {
                        window.open('https://www.google.com/maps/dir//Barangay+Dahat+Hall', '_blank');
                    });
                } else {
                    window.open('https://www.google.com/maps/dir//Barangay+Dahat+Hall', '_blank');
                }
            });
            
            // Auto-fill phone format
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 0) {
                    if (value.length <= 4) {
                        value = value;
                    } else if (value.length <= 7) {
                        value = value.slice(0, 4) + ' ' + value.slice(4);
                    } else if (value.length <= 10) {
                        value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7);
                    } else {
                        value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 11);
                    }
                }
                this.value = value;
            });
            
            // Subject change handler
            const subjectSelect = document.getElementById('subject');
            subjectSelect.addEventListener('change', function() {
                const selected = this.value;
                const message = messageInput.value;
                
                // If message is empty or very short, suggest content based on subject
                if (message.length < 10) {
                    let suggestion = '';
                    switch(selected) {
                        case 'General Inquiry':
                            suggestion = 'I would like to inquire about...';
                            break;
                        case 'Barangay Clearance':
                            suggestion = 'I would like to request a barangay clearance for the purpose of...\n\nRequired information:\n- Full Name:\n- Address:\n- Birth Date:\n- Purpose:';
                            break;
                        case 'Complaint':
                            suggestion = 'I would like to file a complaint regarding...\n\nDetails:\n- Location:\n- Date/Time:\n- Description:';
                            break;
                        case 'Suggestion':
                            suggestion = 'I would like to suggest...\n\nSuggestion details:';
                            break;
                    }
                    
                    if (suggestion) {
                        messageInput.value = suggestion;
                        messageInput.dispatchEvent(new Event('input'));
                    }
                }
            });
            
            // Show PHP notifications if they exist
            const successNotification = document.getElementById('successNotification');
            const errorNotification = document.getElementById('errorNotification');
            
            if (successNotification) {
                showNotification(successNotification.querySelector('span').textContent, 'success');
            }
            if (errorNotification) {
                showNotification(errorNotification.querySelector('span').textContent, 'error');
            }
            
            // Smooth scroll for logo click
            document.getElementById('logo').addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Add hover animation to contact items
            const contactItems = document.querySelectorAll('.contact-item');
            contactItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('i');
                    icon.style.transform = 'rotate(15deg) scale(1.1)';
                });
                
                item.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('i');
                    icon.style.transform = 'rotate(0) scale(1)';
                });
            });
        });
        
        // Show Notification Function
        function showNotification(message, type = "success") {
            const notification = document.getElementById('notificationTemplate').cloneNode(true);
            notification.id = 'tempNotification';
            notification.style.display = 'flex';
            notification.classList.add(type);
            
            const icon = notification.querySelector('i');
            const text = notification.querySelector('span');
            text.textContent = message;
            
            if (type === 'error') {
                icon.className = 'fas fa-exclamation-circle';
            } else if (type === 'warning') {
                icon.className = 'fas fa-exclamation-triangle';
            }
            
            document.body.appendChild(notification);
            
            // Hide notification after 5 seconds
            setTimeout(function() {
                notification.style.animation = 'slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) reverse';
                setTimeout(() => {
                    notification.remove();
                }, 400);
            }, 5000);
        }
        
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
        
        // File attachment preview (simplified)
        document.getElementById('attachment').addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // in MB
                if (fileSize > 5) {
                    showNotification('File size exceeds 5MB limit. Please choose a smaller file.', 'error');
                    this.value = '';
                }
                
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                const allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                
                if (!allowedExts.includes(fileExt)) {
                    showNotification('File type not allowed. Please upload PDF, DOC, JPG, or PNG files only.', 'error');
                    this.value = '';
                }
            }
        });
    </script>
</body>
</html>