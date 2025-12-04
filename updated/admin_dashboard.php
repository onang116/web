<?php
require_once 'config.php';

// Redirect to login if not logged in
requireLogin();

// Only officials can access this page
if (!isOfficial()) {
    header('Location: home.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle clearance status update
    if (isset($_POST['update_clearance_status'])) {
        $request_id = $conn->real_escape_string($_POST['request_id']);
        $status = $conn->real_escape_string($_POST['status']);
        $notes = $conn->real_escape_string($_POST['notes']);
        
        $sql = "UPDATE clearance_requests 
                SET status = '$status', 
                    processed_date = NOW(), 
                    notes = '$notes' 
                WHERE id = '$request_id'";
        
        if ($conn->query($sql)) {
            $success_message = "Clearance request updated successfully!";
        } else {
            $error_message = "Failed to update request.";
        }
    }
    
    // Handle complaint status update
    if (isset($_POST['update_complaint_status'])) {
        $complaint_id = $conn->real_escape_string($_POST['complaint_id']);
        $status = $conn->real_escape_string($_POST['status']);
        $resolution_notes = $conn->real_escape_string($_POST['resolution_notes']);
        
        if ($status === 'resolved') {
            $sql = "UPDATE complaints 
                    SET status = '$status', 
                        resolved_at = NOW(), 
                        resolution_notes = '$resolution_notes' 
                    WHERE id = '$complaint_id'";
        } else {
            $sql = "UPDATE complaints 
                    SET status = '$status', 
                        resolution_notes = '$resolution_notes' 
                    WHERE id = '$complaint_id'";
        }
        
        if ($conn->query($sql)) {
            $success_message = "Complaint updated successfully!";
        } else {
            $error_message = "Failed to update complaint.";
        }
    }
    
    // Handle new event creation
    if (isset($_POST['create_event'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $event_date = $conn->real_escape_string($_POST['event_date']);
        $image_url = $conn->real_escape_string($_POST['image_url']);
        
        $sql = "INSERT INTO events (title, description, event_date, image_url, created_by) 
                VALUES ('$title', '$description', '$event_date', '$image_url', '$user_id')";
        
        if ($conn->query($sql)) {
            $success_message = "Event created successfully!";
        } else {
            $error_message = "Failed to create event.";
        }
    }
    
    // Handle user status update
    if (isset($_POST['update_user_status'])) {
        $target_user_id = $conn->real_escape_string($_POST['user_id']);
        $is_active = $conn->real_escape_string($_POST['is_active']);
        
        $sql = "UPDATE users SET is_active = '$is_active' WHERE id = '$target_user_id'";
        
        if ($conn->query($sql)) {
            $success_message = "User status updated successfully!";
        } else {
            $error_message = "Failed to update user status.";
        }
    }
    
    // Handle official chat message
    if (isset($_POST['official_chat_message'])) {
        $message = $conn->real_escape_string($_POST['chat_message']);
        $is_official = 1;
        
        $sql = "INSERT INTO chat_messages (sender_id, message, is_official) 
                VALUES ('$user_id', '$message', '$is_official')";
        
        if ($conn->query($sql)) {
            // Mark all unread messages from residents as read
            $update_sql = "UPDATE chat_messages SET is_read = 1 WHERE is_official = 0 AND is_read = 0";
            $conn->query($update_sql);
        }
    }
}

// Get statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM clearance_requests WHERE status = 'pending'");
$stats['pending_clearance'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM complaints WHERE status = 'pending'");
$stats['pending_complaints'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'");
$stats['new_messages'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM events WHERE is_active = 1");
$stats['active_events'] = $result->fetch_assoc()['total'];

// Get all clearance requests
$clearance_sql = "SELECT cr.*, u.full_name as requester_name, u.email as requester_email 
                  FROM clearance_requests cr 
                  LEFT JOIN users u ON cr.user_id = u.id 
                  ORDER BY cr.request_date DESC";
$clearance_result = $conn->query($clearance_sql);
$clearance_requests = [];
if ($clearance_result->num_rows > 0) {
    while($row = $clearance_result->fetch_assoc()) {
        $clearance_requests[] = $row;
    }
}

// Get all complaints
$complaint_sql = "SELECT c.*, u.full_name as complainant, u.email 
                  FROM complaints c 
                  LEFT JOIN users u ON c.user_id = u.id 
                  ORDER BY c.created_at DESC";
$complaint_result = $conn->query($complaint_sql);
$complaints = [];
if ($complaint_result->num_rows > 0) {
    while($row = $complaint_result->fetch_assoc()) {
        $complaints[] = $row;
    }
}

// Get all users
$user_sql = "SELECT * FROM users ORDER BY created_at DESC";
$user_result = $conn->query($user_sql);
$all_users = [];
if ($user_result->num_rows > 0) {
    while($row = $user_result->fetch_assoc()) {
        $all_users[] = $row;
    }
}

// Get all events
$event_sql = "SELECT e.*, u.full_name as created_by_name 
              FROM events e 
              LEFT JOIN users u ON e.created_by = u.id 
              ORDER BY e.event_date DESC";
$event_result = $conn->query($event_sql);
$all_events = [];
if ($event_result->num_rows > 0) {
    while($row = $event_result->fetch_assoc()) {
        $all_events[] = $row;
    }
}

// Get contact messages
$contact_sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$contact_result = $conn->query($contact_sql);
$contact_messages = [];
if ($contact_result->num_rows > 0) {
    while($row = $contact_result->fetch_assoc()) {
        $contact_messages[] = $row;
    }
}

// Get unread chat messages from residents
$unread_chat_sql = "SELECT COUNT(*) as unread FROM chat_messages WHERE is_official = 0 AND is_read = 0";
$unread_result = $conn->query($unread_chat_sql);
$unread_messages = $unread_result->fetch_assoc()['unread'];

// Get recent chat messages
$recent_chat_sql = "SELECT cm.*, u.full_name, u.user_type 
                    FROM chat_messages cm 
                    LEFT JOIN users u ON cm.sender_id = u.id 
                    ORDER BY cm.sent_at DESC 
                    LIMIT 20";
$recent_chat_result = $conn->query($recent_chat_sql);
$recent_chats = [];
if ($recent_chat_result->num_rows > 0) {
    while($row = $recent_chat_result->fetch_assoc()) {
        $recent_chats[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Dahat - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .logo-container {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            width: 70px;
            height: 70px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 28px;
            font-weight: 700;
            color: #0d4a9e;
        }

        .logo-text h1 {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .logo-text p {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .user-info {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background-color: white;
            color: #0d4a9e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .user-details h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .user-details p {
            font-size: 0.85rem;
            opacity: 0.9;
            color: #e0e0e0;
        }

        .nav-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid #ff7e30;
            padding-left: 1.8rem;
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        .badge {
            background-color: #ff4757;
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 0.75rem;
            margin-left: auto;
        }

        .logout-container {
            padding: 1.5rem;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            background-color: rgba(255, 71, 87, 0.9);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #ff3838;
            transform: translateY(-2px);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 0;
        }

        .topbar {
            background-color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .topbar h2 {
            color: #0d4a9e;
            font-size: 1.5rem;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            font-size: 1.2rem;
            color: #666;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .current-date {
            color: #666;
            font-size: 0.9rem;
        }

        .content-container {
            padding: 2rem;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-top: 4px solid;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card.users { border-top-color: #0d4a9e; }
        .card.pending { border-top-color: #ffa502; }
        .card.complaints { border-top-color: #ff4757; }
        .card.messages { border-top-color: #2ed573; }
        .card.events { border-top-color: #3742fa; }
        .card.chat { border-top-color: #ff7e30; }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .card.users .card-icon { background-color: #0d4a9e; }
        .card.pending .card-icon { background-color: #ffa502; }
        .card.complaints .card-icon { background-color: #ff4757; }
        .card.messages .card-icon { background-color: #2ed573; }
        .card.events .card-icon { background-color: #3742fa; }
        .card.chat .card-icon { background-color: #ff7e30; }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-title {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-trend {
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .card-trend.positive { color: #2ed573; }
        .card-trend.negative { color: #ff4757; }

        /* Sections */
        .section {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .section-header h3 {
            color: #0d4a9e;
            font-size: 1.3rem;
        }

        .btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 74, 158, 0.2);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%);
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.85rem;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-reviewing { background-color: #d1ecf1; color: #0c5460; }
        .status-resolved { background-color: #d4edda; color: #155724; }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        /* Modals */
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
            align-items: center;
        }

        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            overflow: hidden;
            animation: modalFade 0.4s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes modalFade {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .chat-messages {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 1rem;
            border: 1px solid #eee;
            padding: 1rem;
            border-radius: 5px;
        }

        .chat-message {
            margin-bottom: 1rem;
            padding: 10px;
            border-radius: 5px;
            max-width: 80%;
        }

        .chat-message.official {
            background-color: #e8f1ff;
            margin-left: auto;
        }

        .chat-message.resident {
            background-color: #f1f1f1;
            margin-right: auto;
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: visible;
            }
            
            .logo-text, .user-details p, .nav-link span, .badge {
                display: none;
            }
            
            .logo {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .user-avatar {
                width: 40px;
                height: 40px;
            }
            
            .nav-link {
                justify-content: center;
                padding: 12px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .sidebar:hover {
                width: 250px;
            }
            
            .sidebar:hover .logo-text,
            .sidebar:hover .user-details p,
            .sidebar:hover .nav-link span,
            .sidebar:hover .badge {
                display: block;
            }
            
            .sidebar:hover .nav-link {
                justify-content: flex-start;
                padding: 12px 1.5rem;
            }
            
            .sidebar:hover .logo-container {
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .dashboard-cards {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .topbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .content-container {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn-small {
                width: 100%;
            }
        }

        /* Notification */
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
            animation: slideIn 0.4s ease;
        }

        .notification.error {
            background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
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



 <!-- Add notification icon -->
<div class="notification-icon" onclick="notificationSystem.showNotificationsPanel()">
    <i class="fas fa-bell"></i>
    <span class="notification-badge" style="display: none;">0</span>
</div>

<!-- Add notifications panel container -->
<div id="notificationsPanel" style="display: none;"></div>

<!-- Include CSS and JS -->
<link rel="stylesheet" href="notification_style.css">
<script src="notification.js"></script>











    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">BD</div>
            <div class="logo-text">
                <h1>Barangay Dahat</h1>
                <p>Admin Panel</p>
            </div>
        </div>
        
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
            </div>
            <div class="user-details">
                <h3><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></h3>
                <p>Barangay Official</p>
            </div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#dashboard" class="nav-link active" onclick="showSection('dashboard')">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#clearance" class="nav-link" onclick="showSection('clearance')">
                    <i class="fas fa-file-certificate"></i>
                    <span>Clearance Requests</span>
                    <?php if ($stats['pending_clearance'] > 0): ?>
                        <span class="badge"><?php echo $stats['pending_clearance']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="#complaints" class="nav-link" onclick="showSection('complaints')">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Complaints</span>
                    <?php if ($stats['pending_complaints'] > 0): ?>
                        <span class="badge"><?php echo $stats['pending_complaints']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="#users" class="nav-link" onclick="showSection('users')">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#events" class="nav-link" onclick="showSection('events')">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#messages" class="nav-link" onclick="showSection('messages')">
                    <i class="fas fa-envelope"></i>
                    <span>Contact Messages</span>
                    <?php if ($stats['new_messages'] > 0): ?>
                        <span class="badge"><?php echo $stats['new_messages']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="#chat" class="nav-link" onclick="showSection('chat')">
                    <i class="fas fa-comments"></i>
                    <span>Live Chat</span>
                    <?php if ($unread_messages > 0): ?>
                        <span class="badge"><?php echo $unread_messages; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
        
        <div class="logout-container">
            <form action="logout.php" method="POST">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2 id="pageTitle">Admin Dashboard</h2>
            <div class="topbar-actions">
                <div class="notification-icon" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_messages > 0 || $stats['new_messages'] > 0): ?>
                        <span class="notification-badge"><?php echo $unread_messages + $stats['new_messages']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="current-date">
                    <i class="fas fa-calendar-day"></i>
                    <?php echo date('F j, Y'); ?>
                </div>
            </div>
        </div>
        
        <div class="content-container">
            <!-- Dashboard Section -->
            <div id="dashboard" class="section-content">
                <h3 style="color: #0d4a9e; margin-bottom: 1.5rem;">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h3>
                
                <div class="dashboard-cards">
                    <div class="card users">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $stats['total_users']; ?></div>
                        <div class="card-title">Total Users</div>
                        <div class="card-trend positive">
                            <i class="fas fa-arrow-up"></i> Active residents
                        </div>
                    </div>
                    
                    <div class="card pending">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $stats['pending_clearance']; ?></div>
                        <div class="card-title">Pending Clearance</div>
                        <div class="card-trend negative" onclick="showSection('clearance')" style="cursor: pointer;">
                            <i class="fas fa-eye"></i> View requests
                        </div>
                    </div>
                    
                    <div class="card complaints">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $stats['pending_complaints']; ?></div>
                        <div class="card-title">Pending Complaints</div>
                        <div class="card-trend negative" onclick="showSection('complaints')" style="cursor: pointer;">
                            <i class="fas fa-eye"></i> View complaints
                        </div>
                    </div>
                    
                    <div class="card messages">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $stats['new_messages']; ?></div>
                        <div class="card-title">New Messages</div>
                        <div class="card-trend negative" onclick="showSection('messages')" style="cursor: pointer;">
                            <i class="fas fa-eye"></i> View messages
                        </div>
                    </div>
                    
                    <div class="card events">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $stats['active_events']; ?></div>
                        <div class="card-title">Active Events</div>
                        <div class="card-trend positive" onclick="showSection('events')" style="cursor: pointer;">
                            <i class="fas fa-plus"></i> Create new
                        </div>
                    </div>
                    
                    <div class="card chat">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo $unread_messages; ?></div>
                        <div class="card-title">Unread Chats</div>
                        <div class="card-trend negative" onclick="showSection('chat')" style="cursor: pointer;">
                            <i class="fas fa-comments"></i> Open chat
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="section">
                    <div class="section-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <button class="btn" onclick="showSection('clearance')">
                            <i class="fas fa-file-signature"></i> Process Clearance
                        </button>
                        <button class="btn" onclick="showSection('complaints')">
                            <i class="fas fa-tasks"></i> Review Complaints
                        </button>
                        <button class="btn" onclick="openModal('createEventModal')">
                            <i class="fas fa-calendar-plus"></i> Create Event
                        </button>
                        <button class="btn" onclick="showSection('chat')">
                            <i class="fas fa-headset"></i> Support Chat
                        </button>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="section">
                    <div class="section-header">
                        <h3>Recent Clearance Requests</h3>
                        <button class="btn btn-small" onclick="showSection('clearance')">View All</button>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Purpose</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 0; $i < min(5, count($clearance_requests)); $i++): 
                                    $request = $clearance_requests[$i];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['purpose']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                    <td>
                                        <span class="status status-<?php echo $request['status']; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Clearance Requests Section -->
            <div id="clearance" class="section-content" style="display: none;">
                <div class="section">
                    <div class="section-header">
                        <h3>Clearance Requests Management</h3>
                        <div>
                            <span style="margin-right: 1rem; color: #666;">
                                <i class="fas fa-filter"></i> Filter: 
                                <select id="filterClearance" onchange="filterClearanceRequests()">
                                    <option value="all">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Purpose</th>
                                    <th>Date Requested</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="clearanceTableBody">
                                <?php foreach ($clearance_requests as $request): ?>
                                <tr data-status="<?php echo $request['status']; ?>">
                                    <td>#CR<?php echo str_pad($request['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($request['contact_number']); ?></div>
                                        <small><?php echo htmlspecialchars($request['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['purpose']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                    <td>
                                        <span class="status status-<?php echo $request['status']; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-small" onclick="viewClearance(<?php echo $request['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-small btn-secondary" onclick="editClearance(<?php echo $request['id']; ?>)">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Complaints Section -->
            <div id="complaints" class="section-content" style="display: none;">
                <div class="section">
                    <div class="section-header">
                        <h3>Complaints Management</h3>
                        <div>
                            <span style="margin-right: 1rem; color: #666;">
                                <i class="fas fa-filter"></i> Filter: 
                                <select id="filterComplaints" onchange="filterComplaints()">
                                    <option value="all">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="reviewing">Reviewing</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Complaint ID</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Complainant</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="complaintsTableBody">
                                <?php foreach ($complaints as $complaint): ?>
                                <tr data-status="<?php echo $complaint['status']; ?>">
                                    <td>#CP<?php echo str_pad($complaint['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($complaint['complaint_type']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($complaint['location'], 0, 30)) . '...'; ?></td>
                                    <td>
                                        <?php if ($complaint['complainant']): ?>
                                            <?php echo htmlspecialchars($complaint['complainant']); ?>
                                        <?php else: ?>
                                            <span style="color: #999;">Anonymous</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                                    <td>
                                        <span class="status status-<?php echo $complaint['status']; ?>">
                                            <?php echo ucfirst($complaint['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-small" onclick="viewComplaint(<?php echo $complaint['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-small btn-secondary" onclick="updateComplaint(<?php echo $complaint['id']; ?>)">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users" class="section-content" style="display: none;">
                <div class="section">
                    <div class="section-header">
                        <h3>User Management</h3>
                        <div>
                            <span style="margin-right: 1rem; color: #666;">
                                <i class="fas fa-filter"></i> Filter: 
                                <select id="filterUsers" onchange="filterUsers()">
                                    <option value="all">All</option>
                                    <option value="resident">Residents</option>
                                    <option value="official">Officials</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <?php foreach ($all_users as $user): ?>
                                <tr data-type="<?php echo $user['user_type']; ?>" data-status="<?php echo $user['is_active']; ?>">
                                    <td>#U<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($user['full_name']); ?></div>
                                        <small style="color: #666;">@<?php echo htmlspecialchars($user['username']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <span class="status <?php echo $user['user_type'] === 'official' ? 'status-approved' : 'status-processing'; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="status status-approved">Active</span>
                                        <?php else: ?>
                                            <span class="status status-rejected">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($user['id'] != $user_id): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="is_active" value="<?php echo $user['is_active'] ? '0' : '1'; ?>">
                                                    <button type="submit" name="update_user_status" class="btn btn-small <?php echo $user['is_active'] ? 'btn-secondary' : ''; ?>">
                                                        <?php if ($user['is_active']): ?>
                                                            <i class="fas fa-user-slash"></i> Deactivate
                                                        <?php else: ?>
                                                            <i class="fas fa-user-check"></i> Activate
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-small" disabled>
                                                    <i class="fas fa-user"></i> You
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Events Section -->
            <div id="events" class="section-content" style="display: none;">
                <div class="section">
                    <div class="section-header">
                        <h3>Events Management</h3>
                        <button class="btn" onclick="openModal('createEventModal')">
                            <i class="fas fa-plus"></i> Create New Event
                        </button>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Event ID</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_events as $event): 
                                    $event_date = new DateTime($event['event_date']);
                                    $current_date = new DateTime();
                                    $is_past = $event_date < $current_date;
                                ?>
                                <tr>
                                    <td>#E<?php echo str_pad($event['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td>
                                        <div><?php echo $event_date->format('M d, Y'); ?></div>
                                        <?php if ($is_past): ?>
                                            <small style="color: #ff4757;">(Past Event)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($event['description'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($event['created_by_name'] ?: 'System'); ?></td>
                                    <td>
                                        <?php if ($event['is_active']): ?>
                                            <span class="status status-approved">Active</span>
                                        <?php else: ?>
                                            <span class="status status-rejected">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-small" onclick="viewEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-small btn-secondary" onclick="editEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Contact Messages Section -->
            <div id="messages" class="section-content" style="display: none;">
                <div class="section">
                    <div class="section-header">
                        <h3>Contact Messages</h3>
                        <div>
                            <span style="margin-right: 1rem; color: #666;">
                                <i class="fas fa-filter"></i> Filter: 
                                <select id="filterMessages" onchange="filterMessages()">
                                    <option value="all">All</option>
                                    <option value="new">New</option>
                                    <option value="read">Read</option>
                                    <option value="replied">Replied</option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Message ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="messagesTableBody">
                                <?php foreach ($contact_messages as $msg): ?>
                                <tr data-status="<?php echo $msg['status']; ?>">
                                    <td>#MSG<?php echo str_pad($msg['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($msg['subject'], 0, 30)) . '...'; ?></td>
                                    <td>
                                        <span class="status status-<?php echo $msg['priority']; ?>">
                                            <?php echo ucfirst($msg['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $msg['status']; ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-small" onclick="viewMessage(<?php echo $msg['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-small btn-secondary" onclick="replyMessage(<?php echo $msg['id']; ?>)">
                                                <i class="fas fa-reply"></i> Reply
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Live Chat Section -->
            <div id="chat" class="section-content" style="display: none;">
                <div class="section">
                    <div class="section-header">
                        <h3>Live Chat Support</h3>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="color: #666;">
                                <i class="fas fa-circle" style="color: #2ed573; font-size: 0.8rem;"></i>
                                Support System
                            </span>
                            <button class="btn" onclick="refreshChat()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                        <!-- Chat Messages -->
                        <div>
                            <div class="chat-messages" id="adminChatMessages" style="height: 400px;">
                                <?php foreach (array_reverse($recent_chats) as $chat): 
                                    $is_official = $chat['is_official'] || $chat['user_type'] === 'official';
                                    $sender_name = $chat['full_name'] ?: ($is_official ? 'Barangay Official' : 'Resident');
                                    $sent_time = date('h:i A', strtotime($chat['sent_at']));
                                ?>
                                <div class="chat-message <?php echo $is_official ? 'official' : 'resident'; ?>">
                                    <strong><?php echo htmlspecialchars($sender_name); ?></strong>
                                    <small style="color: #666;">(<?php echo $sent_time; ?>)</small>
                                    <p><?php echo htmlspecialchars($chat['message']); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <form method="POST" style="margin-top: 1rem; display: flex; gap: 10px;">
                                <input type="text" name="chat_message" class="form-control" placeholder="Type your response..." required>
                                <button type="submit" name="official_chat_message" class="btn">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </form>
                        </div>
                        
                        <!-- Online Users -->
                        <div>
                            <h4 style="margin-bottom: 1rem; color: #0d4a9e;">Recent Chatters</h4>
                            <?php
                            // Get unique users from recent chats
                            $unique_users = [];
                            foreach ($recent_chats as $chat) {
                                if ($chat['sender_id'] && !isset($unique_users[$chat['sender_id']])) {
                                    $unique_users[$chat['sender_id']] = $chat;
                                }
                            }
                            ?>
                            <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px; max-height: 350px; overflow-y: auto;">
                                <?php foreach ($unique_users as $user_id => $chat): 
                                    if ($chat['is_official']) continue;
                                ?>
                                <div style="display: flex; align-items: center; gap: 10px; padding: 10px; border-bottom: 1px solid #eee;">
                                    <div style="width: 40px; height: 40px; background-color: #0d4a9e; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <?php echo strtoupper(substr($chat['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($chat['full_name']); ?></div>
                                        <small style="color: #666;">Last message: <?php echo date('h:i A', strtotime($chat['sent_at'])); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals will be added here -->
    <!-- Create Event Modal -->
    <div class="modal" id="createEventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Event</h3>
                <button class="close-modal" onclick="closeModal('createEventModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="create_event" value="1">
                    
                    <div class="form-group">
                        <label for="eventTitle">Event Title *</label>
                        <input type="text" id="eventTitle" name="title" class="form-control" required placeholder="Enter event title">
                    </div>
                    
                    <div class="form-group">
                        <label for="eventDescription">Description *</label>
                        <textarea id="eventDescription" name="description" class="form-control" required placeholder="Enter event description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="eventDate">Event Date *</label>
                        <input type="date" id="eventDate" name="event_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="eventImage">Image URL (Optional)</label>
                        <input type="text" id="eventImage" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                        <small style="color: #666;">Leave empty to use default image</small>
                    </div>
                    
                    <button type="submit" class="btn">Create Event</button>
                </form>
            </div>
        </div>
    </div>

    <!-- View Clearance Modal -->
    <div class="modal" id="viewClearanceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Clearance Request Details</h3>
                <button class="close-modal" onclick="closeModal('viewClearanceModal')">&times;</button>
            </div>
            <div class="modal-body" id="clearanceDetails">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Update Clearance Modal -->
    <div class="modal" id="updateClearanceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Clearance Request</h3>
                <button class="close-modal" onclick="closeModal('updateClearanceModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="updateClearanceForm">
                    <input type="hidden" name="update_clearance_status" value="1">
                    <input type="hidden" name="request_id" id="clearanceRequestId">
                    
                    <div class="form-group">
                        <label for="clearanceStatus">Status *</label>
                        <select id="clearanceStatus" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="clearanceNotes">Notes</label>
                        <textarea id="clearanceNotes" name="notes" class="form-control" placeholder="Add notes or comments"></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Update Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- View Complaint Modal -->
    <div class="modal" id="viewComplaintModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Complaint Details</h3>
                <button class="close-modal" onclick="closeModal('viewComplaintModal')">&times;</button>
            </div>
            <div class="modal-body" id="complaintDetails">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Update Complaint Modal -->
    <div class="modal" id="updateComplaintModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Complaint Status</h3>
                <button class="close-modal" onclick="closeModal('updateComplaintModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="updateComplaintForm">
                    <input type="hidden" name="update_complaint_status" value="1">
                    <input type="hidden" name="complaint_id" id="complaintId">
                    
                    <div class="form-group">
                        <label for="complaintStatus">Status *</label>
                        <select id="complaintStatus" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="reviewing">Reviewing</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="resolutionNotes">Resolution Notes</label>
                        <textarea id="resolutionNotes" name="resolution_notes" class="form-control" placeholder="Add resolution notes or comments"></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Update Complaint</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show/Hide Sections
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            document.getElementById(sectionId).style.display = 'block';
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Find and activate the corresponding nav link
            const navLink = document.querySelector(`a[href="#${sectionId}"]`);
            if (navLink) {
                navLink.classList.add('active');
            }
            
            // Update page title
            const pageTitle = document.getElementById('pageTitle');
            const sectionTitles = {
                'dashboard': 'Admin Dashboard',
                'clearance': 'Clearance Requests',
                'complaints': 'Complaints Management',
                'users': 'User Management',
                'events': 'Events Management',
                'messages': 'Contact Messages',
                'chat': 'Live Chat Support'
            };
            
            if (sectionTitles[sectionId]) {
                pageTitle.textContent = sectionTitles[sectionId];
            }
        }

        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Filter Functions
        function filterClearanceRequests() {
            const filter = document.getElementById('filterClearance').value;
            const rows = document.querySelectorAll('#clearanceTableBody tr');
            
            rows.forEach(row => {
                if (filter === 'all' || row.getAttribute('data-status') === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function filterComplaints() {
            const filter = document.getElementById('filterComplaints').value;
            const rows = document.querySelectorAll('#complaintsTableBody tr');
            
            rows.forEach(row => {
                if (filter === 'all' || row.getAttribute('data-status') === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function filterUsers() {
            const filter = document.getElementById('filterUsers').value;
            const rows = document.querySelectorAll('#usersTableBody tr');
            
            rows.forEach(row => {
                let show = false;
                
                if (filter === 'all') {
                    show = true;
                } else if (filter === 'resident' || filter === 'official') {
                    show = row.getAttribute('data-type') === filter;
                } else if (filter === 'active') {
                    show = row.getAttribute('data-status') === '1';
                } else if (filter === 'inactive') {
                    show = row.getAttribute('data-status') === '0';
                }
                
                row.style.display = show ? '' : 'none';
            });
        }

        function filterMessages() {
            const filter = document.getElementById('filterMessages').value;
            const rows = document.querySelectorAll('#messagesTableBody tr');
            
            rows.forEach(row => {
                if (filter === 'all' || row.getAttribute('data-status') === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Clearance Functions
        function viewClearance(requestId) {
            fetch(`get_clearance_details.php?id=${requestId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('clearanceDetails').innerHTML = data;
                    openModal('viewClearanceModal');
                })
                .catch(error => {
                    alert('Error loading clearance details');
                });
        }

        function editClearance(requestId) {
            document.getElementById('clearanceRequestId').value = requestId;
            openModal('updateClearanceModal');
        }

        // Complaint Functions
        function viewComplaint(complaintId) {
            fetch(`get_complaint_details.php?id=${complaintId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('complaintDetails').innerHTML = data;
                    openModal('viewComplaintModal');
                })
                .catch(error => {
                    alert('Error loading complaint details');
                });
        }

        function updateComplaint(complaintId) {
            document.getElementById('complaintId').value = complaintId;
            openModal('updateComplaintModal');
        }

        // Event Functions
        function viewEvent(eventId) {
            // Implement view event functionality
            alert('View Event ID: ' + eventId);
        }

        function editEvent(eventId) {
            // Implement edit event functionality
            alert('Edit Event ID: ' + eventId);
        }

        // Message Functions
        function viewMessage(messageId) {
            // Implement view message functionality
            alert('View Message ID: ' + messageId);
        }

        function replyMessage(messageId) {
            // Implement reply message functionality
            alert('Reply to Message ID: ' + messageId);
        }

        // Chat Functions
        function refreshChat() {
            location.reload();
        }

        // Toggle Notifications
        function toggleNotifications() {
            alert('Notifications feature coming soon!');
        }

        // Auto-refresh chat every 30 seconds
        setInterval(function() {
            if (document.getElementById('chat').style.display !== 'none') {
                // Refresh chat messages
                fetch('get_recent_chats.php')
                    .then(response => response.json())
                    .then(data => {
                        // Update chat interface
                        console.log('Chat refreshed');
                    });
            }
        }, 30000);

        // Set today's date as minimum for event date
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const eventDateInput = document.getElementById('eventDate');
            if (eventDateInput) {
                eventDateInput.min = today;
                eventDateInput.value = today;
            }

            // Show PHP notifications
            const successNotification = document.getElementById('successNotification');
            const errorNotification = document.getElementById('errorNotification');
            
            if (successNotification) {
                successNotification.style.display = 'flex';
                setTimeout(() => {
                    successNotification.style.display = 'none';
                }, 5000);
            }
            
            if (errorNotification) {
                errorNotification.style.display = 'flex';
                setTimeout(() => {
                    errorNotification.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>