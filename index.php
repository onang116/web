<?php
require_once 'config.php';
requireLogin();
redirectOfficials();

// Get user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];
$username = $_SESSION['username'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clearance_request'])) {
        // Handle clearance request
        $full_name = $conn->real_escape_string($_POST['full_name']);
        $address = $conn->real_escape_string($_POST['address']);
        $birth_date = $conn->real_escape_string($_POST['birth_date']);
        $purpose = $conn->real_escape_string($_POST['purpose']);
        $contact_number = $conn->real_escape_string($_POST['contact_number']);
        $email = $conn->real_escape_string($_POST['email']);
        
        $sql = "INSERT INTO clearance_requests (user_id, full_name, address, birth_date, purpose, contact_number, email) 
                VALUES ('$user_id', '$full_name', '$address', '$birth_date', '$purpose', '$contact_number', '$email')";
        
        if ($conn->query($sql)) {
            $success_message = "Clearance request submitted successfully!";
        } else {
            $error_message = "Failed to submit request. Please try again.";
        }
    }
    
    if (isset($_POST['complaint_request'])) {
        // Handle complaint submission
        $complaint_type = $conn->real_escape_string($_POST['complaint_type']);
        $location = $conn->real_escape_string($_POST['location']);
        $details = $conn->real_escape_string($_POST['details']);
        $complainant_name = $conn->real_escape_string($_POST['complainant_name']);
        $contact_info = $conn->real_escape_string($_POST['contact_info']);
        
        $sql = "INSERT INTO complaints (user_id, complaint_type, location, details, complainant_name, contact_info) 
                VALUES ('$user_id', '$complaint_type', '$location', '$details', '$complainant_name', '$contact_info')";
        
        if ($conn->query($sql)) {
            $success_message = "Complaint submitted successfully!";
        } else {
            $error_message = "Failed to submit complaint. Please try again.";
        }
    }
    
    if (isset($_POST['chat_message'])) {
        // Handle chat message
        $message = $conn->real_escape_string($_POST['chat_message']);
        $is_official = 0; // Message from resident
        
        $sql = "INSERT INTO chat_messages (sender_id, message, is_official) 
                VALUES ('$user_id', '$message', '$is_official')";
        
        $conn->query($sql);
    }
}

// Get events from database
$events_sql = "SELECT * FROM events WHERE is_active = 1 ORDER BY event_date ASC";
$events_result = $conn->query($events_sql);
$events = [];
if ($events_result->num_rows > 0) {
    while($row = $events_result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Get chat messages
$chat_sql = "SELECT cm.*, u.full_name, u.user_type 
             FROM chat_messages cm 
             LEFT JOIN users u ON cm.sender_id = u.id 
             WHERE cm.sender_id = '$user_id' OR cm.is_official = 1 
             ORDER BY cm.sent_at ASC 
             LIMIT 50";
$chat_result = $conn->query($chat_sql);
$chat_messages = [];
if ($chat_result->num_rows > 0) {
    while($row = $chat_result->fetch_assoc()) {
        $chat_messages[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Dahat - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
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

        /* Header Styles */
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

        /* Hero Section */
        .hero-section {
            padding: 3rem 0;
            background-color: #fff;
            border-bottom: 1px solid #eaeaea;
        }

        .hero-container {
            display: flex;
            align-items: center;
            gap: 3rem;
        }

        .hero-content {
            flex: 1;
        }

        .hero-content h2 {
            font-size: 2.5rem;
            color: #0d4a9e;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 2rem;
        }

        .hero-image {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.5s ease, box-shadow 0.5s ease;
        }

        .hero-image:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .hero-image img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .hero-image:hover img {
            transform: scale(1.05);
        }

        .image-caption {
            padding: 10px;
            background: rgba(13, 74, 158, 0.9);
            color: white;
            text-align: center;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(13, 74, 158, 0.3);
            background: linear-gradient(135deg, #1e6bc4 0%, #2a7cd6 100%);
        }

        /* Features Section */
        .features-section {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.2rem;
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

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
            border-top: 5px solid #0d4a9e;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(13, 74, 158, 0.05) 0%, rgba(255, 126, 48, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
            color: white;
            transition: all 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            transform: rotate(15deg) scale(1.1);
            background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
            position: relative;
            z-index: 1;
        }

        .feature-card p {
            color: #666;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .btn-feature {
            background-color: #0d4a9e;
            color: white;
            padding: 10px 25px;
            font-size: 0.9rem;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .btn-feature:hover {
            background-color: #1e6bc4;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 74, 158, 0.2);
        }

        /* Events Section */
        .events-section {
            padding: 4rem 0;
            background-color: #f0f5ff;
        }

        .events-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }

        .event-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .event-image {
            height: 200px;
            overflow: hidden;
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .event-card:hover .event-image img {
            transform: scale(1.1);
        }

        .event-content {
            padding: 1.5rem;
        }

        .event-date {
            display: inline-block;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .event-content h3 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .event-content p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .event-expired {
            position: relative;
            opacity: 0.8;
        }

        .event-expired::after {
            content: "EXPIRED";
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff4757;
            color: white;
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        /* Modal Styles - FIXED SCROLLING ISSUE */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            overflow: hidden;
            animation: modalFade 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin: 50px auto;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        @keyframes modalFade {
            from {opacity: 0; transform: translateY(-30px) scale(0.95);}
            to {opacity: 1; transform: translateY(0) scale(1);}
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
            font-size: 1.5rem;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            transition: transform 0.3s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close-modal:hover {
            transform: scale(1.2);
            background-color: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d4a9e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 74, 158, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            width: 100%;
            padding: 14px;
            font-size: 1.1rem;
            margin-top: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #1e6bc4 0%, #2a7cd6 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 74, 158, 0.2);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        /* Chat Styles */
        .chat-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .chat-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(13, 74, 158, 0.3);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-button:hover {
            background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%);
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 8px 25px rgba(255, 126, 48, 0.4);
        }

        .chat-window {
            display: none;
            position: absolute;
            bottom: 70px;
            right: 0;
            width: 350px;
            height: 450px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            flex-direction: column;
            overflow: hidden;
            animation: chatSlideUp 0.3s ease;
        }

        @keyframes chatSlideUp {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .chat-header {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header h4 {
            font-size: 1.2rem;
        }

        .close-chat {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .close-chat:hover {
            transform: scale(1.2);
        }

        .chat-messages {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            background-color: #f9f9f9;
        }

        .message {
            margin-bottom: 1rem;
            max-width: 80%;
            animation: messageAppear 0.3s ease;
        }

        @keyframes messageAppear {
            from {opacity: 0; transform: translateY(10px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .message.user {
            margin-left: auto;
        }

        .message.official-message .message-content {
            background-color: #e8f1ff;
            color: #333;
            border-bottom-left-radius: 5px;
        }

        .message.user-message .message-content {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message-content {
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 0.95rem;
            word-wrap: break-word;
        }

        .message-sender {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 3px;
            padding-left: 5px;
        }

        .chat-input {
            padding: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .chat-input input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .chat-input input:focus {
            border-color: #0d4a9e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13, 74, 158, 0.1);
        }

        .chat-input button {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-input button:hover {
            background: linear-gradient(135deg, #1e6bc4 0%, #2a7cd6 100%);
            transform: scale(1.1);
        }

        /* Footer Styles */
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
            from {opacity: 0; transform: translateY(-10px);}
            to {opacity: 1; transform: translateY(0);}
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

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: linear-gradient(135deg, #4cd964 0%, #2ecc71 100%);
            color: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 3000;
            display: none;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-width: 350px;
        }

        @keyframes slideIn {
            from {transform: translateX(100%); opacity: 0;}
            to {transform: translateX(0); opacity: 1;}
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
            
            .hero-container {
                flex-direction: column;
            }
            
            .hero-content, .hero-image {
                width: 100%;
            }
            
            .hero-image {
                margin-top: 2rem;
            }
            
            .nav-links, .user-section {
                display: none;
            }
            
            .mobile-menu {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 20px auto;
                max-height: 85vh;
            }
            
            .chat-window {
                width: 300px;
                height: 400px;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .hero-content h2 {
                font-size: 2rem;
            }
            
            .features-grid, .events-container {
                grid-template-columns: 1fr;
            }
            
            .user-name {
                max-width: 100px;
            }
        }

        @media (max-width: 576px) {
            .footer-container {
                grid-template-columns: 1fr;
            }
            
            .chat-window {
                width: 280px;
                right: -10px;
            }
            
            .chat-container {
                bottom: 20px;
                right: 20px;
            }
            
            .modal-content {
                padding: 0;
                margin: 10px auto;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
            
            .hero-section {
                padding: 2rem 0;
            }
            
            .features-section, .events-section {
                padding: 3rem 0;
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

        @media (max-width: 400px) {
            .container {
                width: 95%;
                padding: 0 10px;
            }
            
            .chat-window {
                width: 260px;
                height: 380px;
            }
        }

        /* Events Modal Content */
        .events-modal-content {
            padding: 1rem;
        }

        .no-events {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-size: 1.1rem;
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

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php if (isset($success_message)): ?>
        <div class="notification" id="successNotification">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_message; ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
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
                    <li><a href="home.php" class="active">Home</a></li>
                    <li><a href="portfolio.php">Portfolio</a></li>
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



 <!-- Add notification bell for residents -->
<div class="notification-icon" style="margin-right: 15px; position: relative; cursor: pointer;" 
     onclick="notificationSystem.showNotificationsPanel()">
    <i class="fas fa-bell" style="font-size: 1.2rem; color: white;"></i>
    <span class="notification-badge" style="position: absolute; top: -5px; right: -5px; 
           background: #ff4757; color: white; border-radius: 50%; width: 18px; height: 18px; 
           font-size: 0.7rem; display: none; align-items: center; justify-content: center;">
        0
    </span>
</div>

<!-- Include the notification system -->
<script src="notification.js"></script>










    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-container">
            <div class="hero-content">
                <h2>Welcome to Barangay Dahat</h2>
                <p>A progressive community dedicated to serving its residents with integrity, transparency, and efficiency. Together we build a safer, cleaner, and more prosperous neighborhood.</p>
                <button class="btn btn-primary" id="viewEventsBtn">View Upcoming Events</button>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Barangay Officials">
                <div class="image-caption">Barangay Officials of Dahat</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>Barangay Dahat offers a range of services to meet the needs of our community members. Access these services conveniently through our online portal.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Events & Announcements</h3>
                    <p>Stay updated with the latest barangay events, activities, and important announcements from our officials.</p>
                    <button class="btn btn-feature" id="eventsBtn">View Events</button>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-certificate"></i>
                    </div>
                    <h3>Barangay Clearance</h3>
                    <p>Request barangay clearance online. Submit your requirements and track the status of your application.</p>
                    <button class="btn btn-feature" id="clearanceBtn">Request Clearance</button>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Report & Complaints</h3>
                    <p>Report concerns or file complaints securely. Our officials will address your issues promptly.</p>
                    <button class="btn btn-feature" id="complaintBtn">File a Complaint</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section class="events-section">
        <div class="container">
            <div class="section-title">
                <h2>Upcoming Events</h2>
                <p>Join us in our community activities. Events are automatically removed after their scheduled date.</p>
            </div>
            
            <div class="events-container" id="eventsContainer">
                <?php if (empty($events)): ?>
                    <p style="text-align: center; grid-column: 1/-1; font-size: 1.2rem; color: #666; padding: 2rem;">
                        No upcoming events at the moment. Please check back later.
                    </p>
                <?php else: ?>
                    <?php foreach ($events as $event): 
                        $event_date = new DateTime($event['event_date']);
                        $current_date = new DateTime();
                        $is_expired = $event_date < $current_date;
                        $formatted_date = $event_date->format('F j, Y');
                    ?>
                        <div class="event-card <?php echo $is_expired ? 'event-expired' : ''; ?>">
                            <div class="event-image">
                                <img src="<?php echo htmlspecialchars($event['image_url'] ?: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                            </div>
                            <div class="event-content">
                                <div class="event-date"><?php echo $formatted_date; ?></div>
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <?php if ($is_expired): ?>
                                    <p style="color: #ff4757; font-weight: 600; margin-top: 10px;">
                                        <i class="fas fa-clock"></i> This event has ended
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Modals -->
    
    <!-- Clearance Request Modal -->
    <div class="modal" id="clearanceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Request Barangay Clearance</h3>
                <button class="close-modal" id="closeClearanceModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="clearanceForm" method="POST">
                    <input type="hidden" name="clearance_request" value="1">
                    
                    <div class="form-group">
                        <label for="fullName">Full Name <span style="color: #ff4757;">*</span></label>
                        <input type="text" id="fullName" name="full_name" class="form-control" required 
                               placeholder="Juan Dela Cruz" value="<?php echo htmlspecialchars($user_name); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Complete Address <span style="color: #ff4757;">*</span></label>
                        <?php
                        // Get user address from database
                        $address_sql = "SELECT address FROM users WHERE id = '$user_id'";
                        $address_result = $conn->query($address_sql);
                        $user_address = $address_result->fetch_assoc()['address'];
                        ?>
                        <input type="text" id="address" name="address" class="form-control" required 
                               placeholder="Street, Zone, Barangay Dahat" value="<?php echo htmlspecialchars($user_address); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="birthDate">Date of Birth <span style="color: #ff4757;">*</span></label>
                        <input type="date" id="birthDate" name="birth_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="purpose">Purpose of Clearance <span style="color: #ff4757;">*</span></label>
                        <select id="purpose" name="purpose" class="form-control" required>
                            <option value="">Select Purpose</option>
                            <option value="Employment">Employment</option>
                            <option value="Business Permit">Business Permit</option>
                            <option value="School Requirement">School Requirement</option>
                            <option value="Government Transaction">Government Transaction</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="contactNumber">Contact Number <span style="color: #ff4757;">*</span></label>
                        <?php
                        // Get user phone from database
                        $phone_sql = "SELECT phone FROM users WHERE id = '$user_id'";
                        $phone_result = $conn->query($phone_sql);
                        $user_phone = $phone_result->fetch_assoc()['phone'];
                        ?>
                        <input type="tel" id="contactNumber" name="contact_number" class="form-control" required 
                               placeholder="09123456789" value="<?php echo htmlspecialchars($user_phone); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span style="color: #ff4757;">*</span></label>
                        <?php
                        // Get user email from database
                        $email_sql = "SELECT email FROM users WHERE id = '$user_id'";
                        $email_result = $conn->query($email_sql);
                        $user_email = $email_result->fetch_assoc()['email'];
                        ?>
                        <input type="email" id="email" name="email" class="form-control" required 
                               placeholder="juandelacruz@email.com" value="<?php echo htmlspecialchars($user_email); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-submit" id="submitClearanceBtn">
                        <span id="clearanceBtnText">Submit Request</span>
                        <span id="clearanceSpinner" class="spinner" style="display: none;"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Complaint Modal -->
    <div class="modal" id="complaintModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>File a Complaint or Report</h3>
                <button class="close-modal" id="closeComplaintModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="complaintForm" method="POST">
                    <input type="hidden" name="complaint_request" value="1">
                    
                    <div class="form-group">
                        <label for="complaintType">Type of Concern <span style="color: #ff4757;">*</span></label>
                        <select id="complaintType" name="complaint_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Noise Complaint">Noise Complaint</option>
                            <option value="Garbage Issue">Garbage Issue</option>
                            <option value="Streetlight Problem">Streetlight Problem</option>
                            <option value="Road Maintenance">Road Maintenance</option>
                            <option value="Safety Concern">Safety Concern</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location of Concern <span style="color: #ff4757;">*</span></label>
                        <input type="text" id="location" name="location" class="form-control" required 
                               placeholder="Street, Zone, Landmark">
                    </div>
                    
                    <div class="form-group">
                        <label for="complaintDetails">Details of Complaint/Report <span style="color: #ff4757;">*</span></label>
                        <textarea id="complaintDetails" name="details" class="form-control" required 
                                  placeholder="Please provide detailed information about your concern..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="complaintName">Your Name (Optional)</label>
                        <input type="text" id="complaintName" name="complainant_name" class="form-control" 
                               placeholder="Your Name" value="<?php echo htmlspecialchars($user_name); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="complaintContact">Contact Information (Optional)</label>
                        <input type="text" id="complaintContact" name="contact_info" class="form-control" 
                               placeholder="Phone or Email" value="<?php echo htmlspecialchars($user_email); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-submit" id="submitComplaintBtn">
                        <span id="complaintBtnText">Submit Report</span>
                        <span id="complaintSpinner" class="spinner" style="display: none;"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Events Modal -->
    <div class="modal" id="eventsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>All Barangay Events</h3>
                <button class="close-modal" id="closeEventsModal">&times;</button>
            </div>
            <div class="modal-body events-modal-content">
                <div id="allEventsContainer">
                    <?php if (empty($events)): ?>
                        <p class="no-events">No events available at the moment.</p>
                    <?php else: ?>
                        <?php foreach ($events as $event): 
                            $event_date = new DateTime($event['event_date']);
                            $current_date = new DateTime();
                            $is_expired = $event_date < $current_date;
                            $formatted_date = $event_date->format('F j, Y');
                        ?>
                            <div class="event-card <?php echo $is_expired ? 'event-expired' : ''; ?>" style="margin-bottom: 1.5rem;">
                                <div class="event-image">
                                    <img src="<?php echo htmlspecialchars($event['image_url'] ?: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                </div>
                                <div class="event-content">
                                    <div class="event-date"><?php echo $formatted_date; ?></div>
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                                    <?php if ($is_expired): ?>
                                        <p style="color: #ff4757; font-weight: 600; margin-top: 10px;">
                                            <i class="fas fa-clock"></i> This event has ended
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Chat -->
    <div class="chat-container">
        <button class="chat-button" id="chatButton">
            <i class="fas fa-comments"></i>
        </button>
        
        <div class="chat-window" id="chatWindow">
            <div class="chat-header">
                <h4>Barangay Dahat Support</h4>
                <button class="close-chat" id="closeChat">&times;</button>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($chat_messages as $message): 
                    $is_user = $message['sender_id'] == $user_id;
                    $sender_name = $message['full_name'] ?: ($message['is_official'] ? 'Barangay Official' : 'User');
                    $sent_time = date('h:i A', strtotime($message['sent_at']));
                ?>
                    <div class="message <?php echo $is_user ? 'user-message' : 'official-message'; ?>">
                        <div class="message-sender">
                            <?php echo htmlspecialchars($sender_name); ?> • <?php echo $sent_time; ?>
                        </div>
                        <div class="message-content">
                            <?php echo htmlspecialchars($message['message']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="Type your message...">
                <button id="sendMessage"><i class="fas fa-paper-plane"></i></button>
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
                    <h4>Our Services</h4>
                    <ul class="footer-links">
                        <li><a href="#" id="footerClearanceBtn">Barangay Clearance</a></li>
                        <li><a href="#" id="footerComplaintBtn">Complaint & Reports</a></li>
                        <li><a href="#" id="footerEventsBtn">Community Events</a></li>
                        <li><a href="#">Business Permit</a></li>
                        <li><a href="#">Health Services</a></li>
                        <li><a href="#">Social Services</a></li>
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

    <!-- Mobile Navigation (Appears below header) -->
    <div class="mobile-nav" id="mobileNav">
        <ul class="mobile-nav-links">
            <li><a href="home.php" class="active">Home</a></li>
            <li><a href="portfolio.php">Portfolio</a></li>
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

    <!-- Notification Template -->
    <div class="notification" id="notificationTemplate" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <span id="notificationText">Your request has been submitted successfully!</span>
    </div>

    <script>
        // DOM Elements
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileNav = document.getElementById('mobileNav');
        const clearanceBtn = document.getElementById('clearanceBtn');
        const complaintBtn = document.getElementById('complaintBtn');
        const eventsBtn = document.getElementById('eventsBtn');
        const viewEventsBtn = document.getElementById('viewEventsBtn');
        const chatButton = document.getElementById('chatButton');
        const closeChat = document.getElementById('closeChat');
        const sendMessage = document.getElementById('sendMessage');
        const chatInput = document.getElementById('chatInput');
        const chatWindow = document.getElementById('chatWindow');
        const chatMessages = document.getElementById('chatMessages');
        const logo = document.getElementById('logo');
        
        // Footer button elements
        const footerClearanceBtn = document.getElementById('footerClearanceBtn');
        const footerComplaintBtn = document.getElementById('footerComplaintBtn');
        const footerEventsBtn = document.getElementById('footerEventsBtn');
        
        // Modal Elements
        const clearanceModal = document.getElementById('clearanceModal');
        const complaintModal = document.getElementById('complaintModal');
        const eventsModal = document.getElementById('eventsModal');
        
        // Close Modal Buttons
        const closeClearanceModal = document.getElementById('closeClearanceModal');
        const closeComplaintModal = document.getElementById('closeComplaintModal');
        const closeEventsModal = document.getElementById('closeEventsModal');
        
        // Form Elements
        const clearanceForm = document.getElementById('clearanceForm');
        const complaintForm = document.getElementById('complaintForm');
        
        // Form buttons
        const submitClearanceBtn = document.getElementById('submitClearanceBtn');
        const clearanceBtnText = document.getElementById('clearanceBtnText');
        const clearanceSpinner = document.getElementById('clearanceSpinner');
        
        const submitComplaintBtn = document.getElementById('submitComplaintBtn');
        const complaintBtnText = document.getElementById('complaintBtnText');
        const complaintSpinner = document.getElementById('complaintSpinner');
        
        // PHP notifications
        const successNotification = document.getElementById('successNotification');
        const errorNotification = document.getElementById('errorNotification');
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Show PHP notifications if they exist
            if (successNotification) {
                showNotification(successNotification.querySelector('span').textContent, 'success');
            }
            if (errorNotification) {
                showNotification(errorNotification.querySelector('span').textContent, 'error');
            }
            
            // Add click animation to all buttons
            const buttons = document.querySelectorAll('.btn, .logout-btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.7);
                        transform: scale(0);
                        animation: ripple-animation 0.6s linear;
                        width: ${size}px;
                        height: ${size}px;
                        top: ${y}px;
                        left: ${x}px;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    // Remove ripple after animation
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple-animation {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Auto-scroll chat to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
        
        // Mobile Menu Toggle
        mobileMenu.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            this.classList.toggle('active');
            
            // Change icon
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
        
        // Logo click - scroll to top
        logo.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Modal Open Functions
        function openModal(modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            document.body.style.overflow = 'auto';
        }
        
        clearanceBtn.addEventListener('click', function() {
            openModal(clearanceModal);
        });
        
        complaintBtn.addEventListener('click', function() {
            openModal(complaintModal);
        });
        
        eventsBtn.addEventListener('click', function() {
            openModal(eventsModal);
        });
        
        viewEventsBtn.addEventListener('click', function() {
            openModal(eventsModal);
        });
        
        // Footer button handlers
        footerClearanceBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(clearanceModal);
        });
        
        footerComplaintBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(complaintModal);
        });
        
        footerEventsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(eventsModal);
        });
        
        // Modal Close Functions
        closeClearanceModal.addEventListener('click', function() {
            closeModal(clearanceModal);
        });
        
        closeComplaintModal.addEventListener('click', function() {
            closeModal(complaintModal);
        });
        
        closeEventsModal.addEventListener('click', function() {
            closeModal(eventsModal);
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === clearanceModal) {
                closeModal(clearanceModal);
            }
            if (event.target === complaintModal) {
                closeModal(complaintModal);
            }
            if (event.target === eventsModal) {
                closeModal(eventsModal);
            }
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (modal.style.display === 'flex') {
                        closeModal(modal);
                    }
                });
                
                // Close chat window
                if (chatWindow.style.display === 'flex') {
                    chatWindow.style.display = 'none';
                }
                
                // Close mobile nav
                if (mobileNav.classList.contains('active')) {
                    mobileNav.classList.remove('active');
                    const icon = mobileMenu.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
        
        // Form Submission with AJAX for better UX
        if (clearanceForm) {
            clearanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                clearanceBtnText.style.display = 'none';
                clearanceSpinner.style.display = 'inline-block';
                submitClearanceBtn.disabled = true;
                
                // Submit form via AJAX
                const formData = new FormData(this);
                
                fetch('submit_clearance.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message || "Clearance request submitted successfully!", "success");
                        closeModal(clearanceModal);
                        clearanceForm.reset();
                    } else {
                        showNotification(data.message || "Failed to submit request. Please try again.", "error");
                    }
                })
                .catch(error => {
                    showNotification("Network error. Please try again.", "error");
                })
                .finally(() => {
                    // Reset button state
                    clearanceBtnText.style.display = 'inline';
                    clearanceSpinner.style.display = 'none';
                    submitClearanceBtn.disabled = false;
                });
            });
        }
        
        if (complaintForm) {
            complaintForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                complaintBtnText.style.display = 'none';
                complaintSpinner.style.display = 'inline-block';
                submitComplaintBtn.disabled = true;
                
                // Submit form via AJAX
                const formData = new FormData(this);
                
                fetch('submit_complaint.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message || "Complaint submitted successfully!", "success");
                        closeModal(complaintModal);
                        complaintForm.reset();
                    } else {
                        showNotification(data.message || "Failed to submit complaint. Please try again.", "error");
                    }
                })
                .catch(error => {
                    showNotification("Network error. Please try again.", "error");
                })
                .finally(() => {
                    // Reset button state
                    complaintBtnText.style.display = 'inline';
                    complaintSpinner.style.display = 'none';
                    submitComplaintBtn.disabled = false;
                });
            });
        }
        
        // Chat Functions
        chatButton.addEventListener('click', function() {
            chatWindow.style.display = 'flex';
            chatInput.focus();
        });
        
        closeChat.addEventListener('click', function() {
            chatWindow.style.display = 'none';
        });
        
        sendMessage.addEventListener('click', function() {
            sendChatMessage();
        });
        
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendChatMessage();
            }
        });
        
        function sendChatMessage() {
            const message = chatInput.value.trim();
            
            if (message !== '') {
                // Add user message immediately
                const timestamp = new Date().toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
                
                addMessageToChat('user-message', '<?php echo htmlspecialchars(explode(" ", $user_name)[0]); ?>', message, timestamp);
                
                // Clear input
                chatInput.value = '';
                
                // Send to server via AJAX
                const formData = new FormData();
                formData.append('chat_message', message);
                formData.append('user_id', '<?php echo $user_id; ?>');
                
                fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Official response will come via polling or WebSocket
                        // For now, we'll simulate an official response
                        setTimeout(() => {
                            const responses = [
                                "Thank you for your message. Our barangay officials will review your concern.",
                                "We have received your message. Someone will get back to you shortly.",
                                "Your concern has been noted. Thank you for reaching out to Barangay Dahat.",
                                "We appreciate you bringing this to our attention. We will look into this matter.",
                                "Thank you for contacting Barangay Dahat support. We'll address your concern as soon as possible."
                            ];
                            
                            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                            const officialTimestamp = new Date().toLocaleTimeString('en-US', { 
                                hour: 'numeric', 
                                minute: '2-digit',
                                hour12: true 
                            });
                            
                            addMessageToChat('official-message', 'Barangay Official', randomResponse, officialTimestamp);
                        }, 1000 + Math.random() * 2000);
                    }
                })
                .catch(error => {
                    console.error('Chat error:', error);
                });
            }
        }
        
        function addMessageToChat(type, sender, message, timestamp) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', type);
            
            const senderElement = document.createElement('div');
            senderElement.classList.add('message-sender');
            senderElement.textContent = `${sender} • ${timestamp}`;
            
            const messageContent = document.createElement('div');
            messageContent.classList.add('message-content');
            messageContent.textContent = message;
            
            messageElement.appendChild(senderElement);
            messageElement.appendChild(messageContent);
            chatMessages.appendChild(messageElement);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
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
        
        // Check for expired events
        function checkExpiredEvents() {
            const currentDate = new Date();
            const eventCards = document.querySelectorAll('.event-card');
            
            eventCards.forEach(card => {
                const dateText = card.querySelector('.event-date').textContent;
                const eventDate = new Date(dateText);
                
                if (eventDate < currentDate && !card.classList.contains('event-expired')) {
                    card.classList.add('event-expired');
                    
                    // Add expired badge if not already present
                    if (!card.querySelector('.event-expired-badge')) {
                        const expiredBadge = document.createElement('div');
                        expiredBadge.className = 'event-expired-badge';
                        expiredBadge.innerHTML = '<i class="fas fa-clock"></i> This event has ended';
                        expiredBadge.style.cssText = 'color: #ff4757; font-weight: 600; margin-top: 10px;';
                        card.querySelector('.event-content').appendChild(expiredBadge);
                    }
                }
            });
        }
        
        // Check for expired events every minute
        setInterval(checkExpiredEvents, 60000);
        // Initial check
        setTimeout(checkExpiredEvents, 1000);
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Auto-fill today's date in birth date field if empty
        const birthDateInput = document.getElementById('birthDate');
        if (birthDateInput && !birthDateInput.value) {
            const today = new Date();
            const eighteenYearsAgo = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
            birthDateInput.max = eighteenYearsAgo.toISOString().split('T')[0];
        }
    </script>
</body>
</html>