<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread_count' => 0]);
    exit();
}

$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];

if ($user_type === 'official') {
    // Count unread messages from residents
    $sql = "SELECT COUNT(*) as unread_count 
            FROM chat_messages 
            WHERE is_official = 0 AND is_read = 0";
} else {
    // Count unread messages from officials
    $sql = "SELECT COUNT(*) as unread_count 
            FROM chat_messages 
            WHERE is_official = 1 AND is_read_resident = 0";
}

$result = $conn->query($sql);
$row = $result->fetch_assoc();
$unread_count = $row['unread_count'];

echo json_encode(['unread_count' => $unread_count]);
?>