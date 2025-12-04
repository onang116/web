<?php
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in and is an official
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get parameters
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] == 'true';

// Build SQL query
$sql = "SELECT cm.*, u.full_name, u.user_type, 
               CASE 
                   WHEN cm.is_official = 1 THEN 'official'
                   WHEN u.user_type = 'official' THEN 'official'
                   ELSE 'resident'
               END as sender_type
        FROM chat_messages cm
        LEFT JOIN users u ON cm.sender_id = u.id
        WHERE 1=1";

// If unread only and user is official, get unread messages from residents
if ($unread_only && $_SESSION['user_type'] === 'official') {
    $sql .= " AND cm.is_official = 0 AND cm.is_read = 0";
}

$sql .= " ORDER BY cm.sent_at DESC LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$messages = [];
$unread_count = 0;

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Format the message for JSON
        $message = [
            'id' => $row['id'],
            'sender_id' => $row['sender_id'],
            'message' => htmlspecialchars($row['message']),
            'sender_name' => $row['full_name'] ? htmlspecialchars($row['full_name']) : 
                            ($row['is_official'] ? 'Barangay Official' : 'Resident'),
            'sender_type' => $row['sender_type'],
            'is_official' => $row['is_official'],
            'is_read' => $row['is_read'],
            'sent_at' => $row['sent_at'],
            'formatted_time' => date('h:i A', strtotime($row['sent_at'])),
            'formatted_date' => date('M d, Y', strtotime($row['sent_at']))
        ];
        $messages[] = $message;
        
        if (!$row['is_read'] && $row['is_official'] == 0) {
            $unread_count++;
        }
    }
}

// If this is an official viewing, mark messages as read
if ($_SESSION['user_type'] === 'official' && !$unread_only) {
    // Mark all unread resident messages as read
    $update_sql = "UPDATE chat_messages SET is_read = 1 WHERE is_official = 0 AND is_read = 0";
    $conn->query($update_sql);
}

// Get total unread count for notification badge
$unread_sql = "SELECT COUNT(*) as total FROM chat_messages WHERE is_official = 0 AND is_read = 0";
$unread_result = $conn->query($unread_sql);
$total_unread = $unread_result->fetch_assoc()['total'];

// Get online users (users who sent messages in last 15 minutes)
$online_time = date('Y-m-d H:i:s', strtotime('-15 minutes'));
$online_sql = "SELECT DISTINCT u.id, u.full_name, u.user_type, 
                      MAX(cm.sent_at) as last_active
               FROM chat_messages cm
               LEFT JOIN users u ON cm.sender_id = u.id
               WHERE cm.sent_at >= '$online_time' 
                     AND u.id IS NOT NULL
                     AND u.user_type = 'resident'
               GROUP BY u.id, u.full_name, u.user_type
               ORDER BY last_active DESC";

$online_result = $conn->query($online_sql);
$online_users = [];

if ($online_result->num_rows > 0) {
    while($row = $online_result->fetch_assoc()) {
        $online_users[] = [
            'id' => $row['id'],
            'full_name' => htmlspecialchars($row['full_name']),
            'user_type' => $row['user_type'],
            'last_active' => $row['last_active'],
            'formatted_last_active' => date('h:i A', strtotime($row['last_active']))
        ];
    }
}

// Get chat statistics
$stats_sql = "SELECT 
                COUNT(*) as total_messages,
                COUNT(CASE WHEN is_official = 1 THEN 1 END) as official_messages,
                COUNT(CASE WHEN is_official = 0 THEN 1 END) as resident_messages,
                COUNT(CASE WHEN DATE(sent_at) = CURDATE() THEN 1 END) as today_messages
              FROM chat_messages";

$stats_result = $conn->query($stats_sql);
$chat_stats = $stats_result->fetch_assoc();

// Get top chatters (residents only)
$top_chatters_sql = "SELECT u.id, u.full_name, COUNT(cm.id) as message_count
                     FROM chat_messages cm
                     LEFT JOIN users u ON cm.sender_id = u.id
                     WHERE u.user_type = 'resident'
                     GROUP BY u.id, u.full_name
                     ORDER BY message_count DESC
                     LIMIT 5";

$top_chatters_result = $conn->query($top_chatters_sql);
$top_chatters = [];

if ($top_chatters_result->num_rows > 0) {
    while($row = $top_chatters_result->fetch_assoc()) {
        $top_chatters[] = [
            'id' => $row['id'],
            'full_name' => htmlspecialchars($row['full_name']),
            'message_count' => $row['message_count']
        ];
    }
}

// Return JSON response
echo json_encode([
    'success' => true,
    'messages' => array_reverse($messages), // Reverse to show oldest first
    'unread_count' => $total_unread,
    'online_users' => $online_users,
    'chat_stats' => $chat_stats,
    'top_chatters' => $top_chatters,
    'total_messages' => count($messages),
    'current_user' => [
        'id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'],
        'user_type' => $_SESSION['user_type']
    ]
]);
?>