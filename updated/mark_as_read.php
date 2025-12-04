<?php
require_once 'config.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'official') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$message_id = isset($data['message_id']) ? intval($data['message_id']) : 0;

if ($message_id > 0) {
    $sql = "UPDATE chat_messages SET is_read = 1 WHERE id = '$message_id'";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark as read']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
}
?>