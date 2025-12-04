<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$user_type = $_SESSION['user_type'];

if ($user_type === 'official') {
    // Mark all resident messages as read
    $sql = "UPDATE chat_messages SET is_read = 1 WHERE is_official = 0";
} else {
    // Mark all official messages as read for the resident
    $sql = "UPDATE chat_messages SET is_read_resident = 1 WHERE is_official = 1";
}

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>