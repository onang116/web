<?php
require_once 'config.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$type = isset($input['type']) ? $conn->real_escape_string($input['type']) : '';

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if ($user_type === 'official') {
    switch ($type) {
        case 'clearance':
            // Mark all clearance requests as read? Or we need a separate table for notification status
            break;
        case 'complaint':
            // Mark all complaints as read?
            break;
        case 'chat':
            $sql = "UPDATE chat_messages SET is_read = 1 WHERE is_official = 0";
            $conn->query($sql);
            break;
        case 'message':
            $sql = "UPDATE contact_messages SET status = 'read' WHERE status = 'new'";
            $conn->query($sql);
            break;
        default:
            // Mark all notifications as read
            // This would require a notifications table
            break;
    }
} else {
    // For residents, mark chat messages as read
    if ($type === 'chat') {
        $sql = "UPDATE chat_messages SET is_read = 1 WHERE is_official = 1";
        $conn->query($sql);
    }
}

echo json_encode(['success' => true]);
?>