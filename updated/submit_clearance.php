<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $conn->real_escape_string($_POST['full_name']);
$address = $conn->real_escape_string($_POST['address']);
$birth_date = $conn->real_escape_string($_POST['birth_date']);
$purpose = $conn->real_escape_string($_POST['purpose']);
$contact_number = $conn->real_escape_string($_POST['contact_number']);
$email = $conn->real_escape_string($_POST['email']);

// Validate required fields
if (empty($full_name) || empty($address) || empty($birth_date) || empty($purpose) || empty($contact_number) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$sql = "INSERT INTO clearance_requests (user_id, full_name, address, birth_date, purpose, contact_number, email, status) 
        VALUES ('$user_id', '$full_name', '$address', '$birth_date', '$purpose', '$contact_number', '$email', 'pending')";

if ($conn->query($sql)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Clearance request submitted successfully! We will contact you within 3 business days.',
        'request_id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit request. Please try again.']);
}
?>