<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barangay_dahat');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is an official
function isOfficial() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'official';
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect officials to admin panel
function redirectOfficials() {
    if (isOfficial()) {
        header('Location: admin_dashboard.php');
        exit();
    }
}

// ==================== NEW NOTIFICATION FUNCTIONS ====================

// Send notification to user
function sendNotification($user_id, $title, $message, $type = 'info', $link = null) {
    global $conn;
    
    // In a production system, you would insert into a notifications table
    // For now, we'll log it or use existing chat/notification system
    
    $type = $conn->real_escape_string($type);
    $title = $conn->real_escape_string($title);
    $message = $conn->real_escape_string($message);
    $link = $link ? $conn->real_escape_string($link) : null;
    
    // Example: Insert into chat_messages as a system notification
    $sql = "INSERT INTO chat_messages (sender_id, message, is_official) 
            VALUES (1, '[$title] $message', 1)";
    
    return $conn->query($sql);
}

// Get unread notification count for user
function getUnreadNotificationCount($user_id) {
    global $conn;
    
    // For officials: count pending requests, complaints, and unread chats
    // For residents: count unread official messages and status updates
    
    $user_type_sql = "SELECT user_type FROM users WHERE id = '$user_id'";
    $result = $conn->query($user_type_sql);
    
    if ($result->num_rows === 0) return 0;
    
    $user = $result->fetch_assoc();
    $user_type = $user['user_type'];
    
    $count = 0;
    
    if ($user_type === 'official') {
        // Pending clearance requests
        $sql = "SELECT COUNT(*) as c FROM clearance_requests WHERE status = 'pending'";
        $result = $conn->query($sql);
        $count += $result->fetch_assoc()['c'];
        
        // Pending complaints
        $sql = "SELECT COUNT(*) as c FROM complaints WHERE status = 'pending'";
        $result = $conn->query($sql);
        $count += $result->fetch_assoc()['c'];
        
        // Unread chat messages from residents
        $sql = "SELECT COUNT(*) as c FROM chat_messages WHERE is_official = 0 AND is_read = 0";
        $result = $conn->query($sql);
        $count += $result->fetch_assoc()['c'];
        
        // New contact messages
        $sql = "SELECT COUNT(*) as c FROM contact_messages WHERE status = 'new'";
        $result = $conn->query($sql);
        $count += $result->fetch_assoc()['c'];
    } else {
        // Unread official messages
        $sql = "SELECT COUNT(*) as c FROM chat_messages 
                WHERE is_official = 1 AND is_read = 0 
                AND sent_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $result = $conn->query($sql);
        $count += $result->fetch_assoc()['c'];
        
        // Recent status updates (last 3 days)
        $sql = "SELECT COUNT(*) as c FROM clearance_requests 
                WHERE user_id = '$user_id' 
                AND status != 'pending' 
                AND processed_date > DATE_SUB(NOW(), INTERVAL 3 DAY)";
        $result = $conn->query($sql);
        $count += $result->fetch_assoc()['c'];
    }
    
    return $count;
}

// ==================== PASSWORD SECURITY FUNCTIONS ====================

// Hash password (use this when creating/updating passwords)
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

// ==================== VALIDATION FUNCTIONS ====================

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number (Philippines format)
function validatePhone($phone) {
    return preg_match('/^09[0-9]{9}$/', $phone);
}

// Sanitize input
function sanitizeInput($input) {
    global $conn;
    return $conn->real_escape_string(trim($input));
}

// ==================== HELPER FUNCTIONS ====================

// Get current user info
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null,
            'user_type' => $_SESSION['user_type'] ?? null
        ];
    }
    return null;
}

// Redirect with message
function redirectWithMessage($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit();
}

// Get flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Format date for display
function formatDate($date_string, $format = 'F j, Y') {
    $date = new DateTime($date_string);
    return $date->format($format);
}

// Calculate time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}

// Check if request is AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// JSON response helper
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// ==================== ERROR HANDLING ====================

// Log error
function logError($error) {
    $log_file = __DIR__ . '/error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] $error\n";
    file_put_contents($log_file, $message, FILE_APPEND);
}

// Handle database error
function handleDatabaseError($error) {
    logError("Database error: $error");
    
    // In production, show generic error message
    if (isAjaxRequest()) {
        jsonResponse(false, 'A database error occurred. Please try again.');
    } else {
        die('A database error occurred. Please try again later.');
    }
}

// Set error handler for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    // Connection already established
} catch (mysqli_sql_exception $e) {
    handleDatabaseError($e->getMessage());
}

// ==================== SESSION SECURITY ====================

// Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Set secure session cookie parameters
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
$httponly = true;
$samesite = 'Strict';

if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params(0, '/', '', $secure, $httponly);
}
?>