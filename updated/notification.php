<?php
require_once 'config.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$response = [];

// Get notifications based on user type
if ($user_type === 'official') {
    // For officials: Get pending requests, complaints, and unread chats
    $response = getOfficialNotifications($user_id, $conn);
} else {
    // For residents: Get status updates and unread official messages
    $response = getResidentNotifications($user_id, $conn);
}

echo json_encode($response);

function getOfficialNotifications($user_id, $conn) {
    $notifications = [];
    
    // Pending clearance requests
    $sql = "SELECT COUNT(*) as count FROM clearance_requests WHERE status = 'pending'";
    $result = $conn->query($sql);
    $pending_clearance = $result->fetch_assoc()['count'];
    
    if ($pending_clearance > 0) {
        $notifications[] = [
            'type' => 'clearance',
            'title' => 'Pending Clearance Requests',
            'message' => "You have $pending_clearance pending clearance request(s) to review",
            'count' => $pending_clearance,
            'icon' => 'fas fa-file-certificate',
            'color' => '#ffa502',
            'link' => '#clearance',
            'time' => 'Just now',
            'priority' => $pending_clearance > 5 ? 'high' : 'normal'
        ];
    }
    
    // Pending complaints
    $sql = "SELECT COUNT(*) as count FROM complaints WHERE status = 'pending'";
    $result = $conn->query($sql);
    $pending_complaints = $result->fetch_assoc()['count'];
    
    if ($pending_complaints > 0) {
        $notifications[] = [
            'type' => 'complaint',
            'title' => 'Pending Complaints',
            'message' => "You have $pending_complaints pending complaint(s) to review",
            'count' => $pending_complaints,
            'icon' => 'fas fa-exclamation-triangle',
            'color' => '#ff4757',
            'link' => '#complaints',
            'time' => 'Just now',
            'priority' => 'high'
        ];
    }
    
    // New contact messages
    $sql = "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'";
    $result = $conn->query($sql);
    $new_messages = $result->fetch_assoc()['count'];
    
    if ($new_messages > 0) {
        $notifications[] = [
            'type' => 'message',
            'title' => 'New Contact Messages',
            'message' => "You have $new_messages new message(s) from residents",
            'count' => $new_messages,
            'icon' => 'fas fa-envelope',
            'color' => '#2ed573',
            'link' => '#messages',
            'time' => 'Just now',
            'priority' => 'normal'
        ];
    }
    
    // Unread chat messages from residents
    $sql = "SELECT COUNT(*) as count FROM chat_messages WHERE is_official = 0 AND is_read = 0";
    $result = $conn->query($sql);
    $unread_chats = $result->fetch_assoc()['count'];
    
    if ($unread_chats > 0) {
        $notifications[] = [
            'type' => 'chat',
            'title' => 'Unread Chat Messages',
            'message' => "You have $unread_chats unread chat message(s)",
            'count' => $unread_chats,
            'icon' => 'fas fa-comments',
            'color' => '#0d4a9e',
            'link' => '#chat',
            'time' => 'Just now',
            'priority' => 'normal'
        ];
    }
    
    // Upcoming events (tomorrow or today)
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $sql = "SELECT COUNT(*) as count FROM events WHERE event_date IN ('$today', '$tomorrow') AND is_active = 1";
    $result = $conn->query($sql);
    $upcoming_events = $result->fetch_assoc()['count'];
    
    if ($upcoming_events > 0) {
        $notifications[] = [
            'type' => 'event',
            'title' => 'Upcoming Events',
            'message' => "You have $upcoming_events event(s) today or tomorrow",
            'count' => $upcoming_events,
            'icon' => 'fas fa-calendar-alt',
            'color' => '#ff7e30',
            'link' => '#events',
            'time' => 'Today',
            'priority' => 'medium'
        ];
    }
    
    return [
        'success' => true,
        'notifications' => $notifications,
        'total_count' => count($notifications),
        'unread_total' => $pending_clearance + $pending_complaints + $new_messages + $unread_chats
    ];
}

function getResidentNotifications($user_id, $conn) {
    $notifications = [];
    
    // Clearance request status updates
    $sql = "SELECT * FROM clearance_requests 
            WHERE user_id = '$user_id' 
            AND status != 'pending' 
            AND processed_date > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY processed_date DESC";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'type' => 'clearance',
            'title' => 'Clearance Request Update',
            'message' => "Your clearance request #CR" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . 
                        " has been " . strtoupper($row['status']),
            'status' => $row['status'],
            'icon' => $row['status'] === 'approved' ? 'fas fa-check-circle' : 
                     ($row['status'] === 'rejected' ? 'fas fa-times-circle' : 'fas fa-cog'),
            'color' => $row['status'] === 'approved' ? '#2ed573' : 
                      ($row['status'] === 'rejected' ? '#ff4757' : '#1e90ff'),
            'link' => 'my_requests.php',
            'time' => timeAgo($row['processed_date']),
            'data' => $row
        ];
    }
    
    // Complaint status updates
    $sql = "SELECT * FROM complaints 
            WHERE user_id = '$user_id' 
            AND status = 'resolved' 
            AND resolved_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY resolved_at DESC";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'type' => 'complaint',
            'title' => 'Complaint Resolved',
            'message' => "Your complaint #CP" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . 
                        " has been resolved",
            'icon' => 'fas fa-check-circle',
            'color' => '#2ed573',
            'link' => 'my_complaints.php',
            'time' => timeAgo($row['resolved_at']),
            'data' => $row
        ];
    }
    
    // Unread official chat messages
    $sql = "SELECT cm.* FROM chat_messages cm
            WHERE cm.is_official = 1 
            AND cm.is_read = 0 
            AND cm.sent_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
            ORDER BY cm.sent_at DESC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $unread_count = $result->num_rows;
        $notifications[] = [
            'type' => 'chat',
            'title' => 'New Message from Barangay',
            'message' => "You have $unread_count unread message(s) from barangay officials",
            'count' => $unread_count,
            'icon' => 'fas fa-comment-dots',
            'color' => '#0d4a9e',
            'link' => '#chat',
            'time' => 'Just now',
            'priority' => 'normal'
        ];
    }
    
    // Upcoming events
    $today = date('Y-m-d');
    $next_week = date('Y-m-d', strtotime('+7 days'));
    $sql = "SELECT * FROM events 
            WHERE event_date BETWEEN '$today' AND '$next_week' 
            AND is_active = 1
            ORDER BY event_date ASC
            LIMIT 3";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $event_date = new DateTime($row['event_date']);
        $days_diff = $event_date->diff(new DateTime())->days;
        
        if ($days_diff <= 2) { // Only show events within 2 days
            $notifications[] = [
                'type' => 'event',
                'title' => 'Upcoming Event',
                'message' => "Don't forget: " . $row['title'] . " on " . 
                            $event_date->format('F j, Y'),
                'icon' => 'fas fa-calendar-day',
                'color' => '#ff7e30',
                'link' => '#events',
                'time' => $days_diff == 0 ? 'Today' : 
                         ($days_diff == 1 ? 'Tomorrow' : "In $days_diff days"),
                'data' => $row
            ];
        }
    }
    
    return [
        'success' => true,
        'notifications' => $notifications,
        'total_count' => count($notifications)
    ];
}

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
?>