<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$complaint_type = $conn->real_escape_string($_POST['complaint_type']);
$location = $conn->real_escape_string($_POST['location']);
$details = $conn->real_escape_string($_POST['details']);
$complainant_name = $conn->real_escape_string($_POST['complainant_name']);
$contact_info = $conn->real_escape_string($_POST['contact_info']);

// Validate required fields
if (empty($complaint_type) || empty($location) || empty($details)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit();
}

$sql = "INSERT INTO complaints (user_id, complaint_type, location, details, complainant_name, contact_info, status) 
        VALUES ('$user_id', '$complaint_type', '$location', '$details', '$complainant_name', '$contact_info', 'pending')";

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Complaint submitted successfully! Our officials will review it and take appropriate action.',
        'complaint_id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit complaint. Please try again.']);
}
?>