<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$message = $conn->real_escape_string($_POST['chat_message']);

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

$sql = "INSERT INTO chat_messages (sender_id, message, is_official) 
        VALUES ('$user_id', '$message', 0)";

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true, 
        'message_id' => $conn->insert_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>