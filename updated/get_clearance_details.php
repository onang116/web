<?php
require_once 'config.php';

// Only officials can access this
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'official') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Get the request ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid request ID');
}

$request_id = $conn->real_escape_string($_GET['id']);

// Fetch clearance request details with user information
$sql = "SELECT cr.*, u.username, u.email as user_email, u.phone as user_phone, 
               u.created_at as user_joined, u.user_type as requester_type
        FROM clearance_requests cr
        LEFT JOIN users u ON cr.user_id = u.id
        WHERE cr.id = '$request_id'";

$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    echo '<div style="padding: 2rem; text-align: center;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ffa502;"></i>
            <h3 style="margin-top: 1rem;">Clearance Request Not Found</h3>
            <p>The requested clearance details could not be found.</p>
          </div>';
    exit();
}

$request = $result->fetch_assoc();

// Format dates
$birth_date = date('F d, Y', strtotime($request['birth_date']));
$request_date = date('F d, Y h:i A', strtotime($request['request_date']));
$processed_date = $request['processed_date'] ? 
    date('F d, Y h:i A', strtotime($request['processed_date'])) : 'Not yet processed';
$user_joined = date('F d, Y', strtotime($request['user_joined']));

// Determine status color
$status_colors = [
    'pending' => '#ffa502',
    'processing' => '#1e90ff',
    'approved' => '#2ed573',
    'rejected' => '#ff4757'
];

$status_color = $status_colors[$request['status']] ?? '#666';
?>

<div style="max-width: 100%;">
    <!-- Request Header -->
    <div style="background: linear-gradient(135deg, <?php echo $status_color; ?> 0%, #ffffff 100%); padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; color: #333;">Clearance Request #CR<?php echo str_pad($request['id'], 4, '0', STR_PAD_LEFT); ?></h3>
                <p style="margin: 0.25rem 0 0 0; color: #666;">
                    Submitted on <?php echo $request_date; ?>
                </p>
            </div>
            <div style="text-align: right;">
                <span style="display: inline-block; padding: 5px 15px; background-color: <?php echo $status_color; ?>; color: white; border-radius: 20px; font-weight: 600;">
                    <?php echo strtoupper($request['status']); ?>
                </span>
                <?php if ($request['status'] !== 'pending'): ?>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #666;">
                        Processed: <?php echo $processed_date; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        
        <!-- Applicant Information -->
        <div>
            <h4 style="color: #0d4a9e; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eee;">
                <i class="fas fa-user"></i> Applicant Information
            </h4>
            
            <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666; width: 40%;"><strong>Full Name:</strong></td>
                        <td style="padding: 8px 0;"><?php echo htmlspecialchars($request['full_name']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Date of Birth:</strong></td>
                        <td style="padding: 8px 0;"><?php echo $birth_date; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Complete Address:</strong></td>
                        <td style="padding: 8px 0;"><?php echo nl2br(htmlspecialchars($request['address'])); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Contact Number:</strong></td>
                        <td style="padding: 8px 0;">
                            <a href="tel:<?php echo htmlspecialchars($request['contact_number']); ?>" style="color: #0d4a9e;">
                                <?php echo htmlspecialchars($request['contact_number']); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Email Address:</strong></td>
                        <td style="padding: 8px 0;">
                            <a href="mailto:<?php echo htmlspecialchars($request['email']); ?>" style="color: #0d4a9e;">
                                <?php echo htmlspecialchars($request['email']); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666;"><strong>Purpose:</strong></td>
                        <td style="padding: 8px 0;">
                            <span style="background-color: #e8f1ff; color: #0d4a9e; padding: 3px 10px; border-radius: 15px; font-weight: 500;">
                                <?php echo htmlspecialchars($request['purpose']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Requester Account Information -->
        <div>
            <h4 style="color: #0d4a9e; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eee;">
                <i class="fas fa-id-card"></i> Account Information
            </h4>
            
            <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px;">
                <?php if ($request['user_id']): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #666; width: 40%;"><strong>Username:</strong></td>
                            <td style="padding: 8px 0;"><?php echo htmlspecialchars($request['username']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>Account Email:</strong></td>
                            <td style="padding: 8px 0;">
                                <a href="mailto:<?php echo htmlspecialchars($request['user_email']); ?>" style="color: #0d4a9e;">
                                    <?php echo htmlspecialchars($request['user_email']); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>Account Phone:</strong></td>
                            <td style="padding: 8px 0;">
                                <a href="tel:<?php echo htmlspecialchars($request['user_phone']); ?>" style="color: #0d4a9e;">
                                    <?php echo htmlspecialchars($request['user_phone']); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>User Type:</strong></td>
                            <td style="padding: 8px 0;">
                                <span style="background-color: <?php echo $request['requester_type'] === 'official' ? '#d4edda' : '#cce5ff'; ?>; 
                                      color: <?php echo $request['requester_type'] === 'official' ? '#155724' : '#004085'; ?>; 
                                      padding: 3px 10px; border-radius: 15px; font-weight: 500;">
                                    <?php echo ucfirst($request['requester_type']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>Member Since:</strong></td>
                            <td style="padding: 8px 0;"><?php echo $user_joined; ?></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 1rem;">
                        <i class="fas fa-user-slash" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                        This request was submitted by a non-registered user or the account has been deleted.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div style="margin-top: 1.5rem;">
        <h4 style="color: #0d4a9e; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eee;">
            <i class="fas fa-sticky-note"></i> Notes & Processing Information
        </h4>
        
        <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px;">
            <?php if (!empty($request['notes'])): ?>
                <div style="background-color: white; padding: 1rem; border-radius: 5px; border-left: 4px solid #0d4a9e;">
                    <?php echo nl2br(htmlspecialchars($request['notes'])); ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #999; font-style: italic;">
                    No notes or comments have been added to this request yet.
                </p>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <?php if ($request['status'] === 'pending'): ?>
                    <button onclick="processRequest(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #1e90ff 0%, #1e6bc4 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-cog"></i> Start Processing
                    </button>
                    <button onclick="approveRequest(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #2ed573 0%, #1e9e5a 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                    <button onclick="rejectRequest(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-times-circle"></i> Reject
                    </button>
                <?php elseif ($request['status'] === 'processing'): ?>
                    <button onclick="approveRequest(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #2ed573 0%, #1e9e5a 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                    <button onclick="rejectRequest(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-times-circle"></i> Reject
                    </button>
                    <button onclick="setToPending(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #ffa502 0%, #ff7e30 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-undo"></i> Back to Pending
                    </button>
                <?php elseif ($request['status'] === 'approved'): ?>
                    <button onclick="printClearance(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-print"></i> Print Clearance
                    </button>
                    <button onclick="setToProcessing(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #1e90ff 0%, #1e6bc4 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-redo"></i> Re-open
                    </button>
                <?php elseif ($request['status'] === 'rejected'): ?>
                    <button onclick="setToPending(<?php echo $request['id']; ?>)" 
                            style="background: linear-gradient(135deg, #ffa502 0%, #ff7e30 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-redo"></i> Re-open Request
                    </button>
                <?php endif; ?>
                
                <button onclick="closeModal('viewClearanceModal')" 
                        style="background-color: #f1f1f1; color: #333; border: 1px solid #ddd; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; margin-left: auto;">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Download/Print Options -->
    <div style="margin-top: 1.5rem; padding: 1rem; background-color: #f0f5ff; border-radius: 5px;">
        <h4 style="color: #0d4a9e; margin-bottom: 0.5rem;">
            <i class="fas fa-download"></i> Export Options
        </h4>
        <div style="display: flex; gap: 10px;">
            <button onclick="exportToPDF(<?php echo $request['id']; ?>)" 
                    style="background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                <i class="fas fa-file-pdf"></i> Export as PDF
            </button>
            <button onclick="copyToClipboard(<?php echo $request['id']; ?>)" 
                    style="background: linear-gradient(135deg, #2ed573 0%, #1e9e5a 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                <i class="fas fa-copy"></i> Copy Details
            </button>
            <a href="mailto:<?php echo htmlspecialchars($request['email']); ?>?subject=Barangay%20Clearance%20Update%20-%20Request%20#CR<?php echo str_pad($request['id'], 4, '0', STR_PAD_LEFT); ?>" 
               style="background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%); color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                <i class="fas fa-envelope"></i> Email Applicant
            </a>
        </div>
    </div>
</div>

<style>
    /* Inline CSS for modal content */
    h4 {
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }
    
    table tr:not(:last-child) {
        border-bottom: 1px solid #eee;
    }
    
    button {
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        button {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // JavaScript functions for button actions
    function processRequest(requestId) {
        if (confirm('Start processing this clearance request?')) {
            updateRequestStatus(requestId, 'processing');
        }
    }
    
    function approveRequest(requestId) {
        if (confirm('Approve this clearance request?')) {
            updateRequestStatus(requestId, 'approved');
        }
    }
    
    function rejectRequest(requestId) {
        if (confirm('Reject this clearance request?')) {
            updateRequestStatus(requestId, 'rejected');
        }
    }
    
    function setToPending(requestId) {
        if (confirm('Set this request back to pending status?')) {
            updateRequestStatus(requestId, 'pending');
        }
    }
    
    function setToProcessing(requestId) {
        if (confirm('Re-open this request for processing?')) {
            updateRequestStatus(requestId, 'processing');
        }
    }
    
    function updateRequestStatus(requestId, status) {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('status', status);
        formData.append('update_clearance_status', '1');
        
        fetch(window.location.pathname.replace('get_clearance_details.php', 'admin_dashboard.php'), {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            alert('Status updated successfully!');
            closeModal('viewClearanceModal');
            location.reload();
        })
        .catch(error => {
            alert('Error updating status: ' + error);
        });
    }
    
    function printClearance(requestId) {
        window.open('print_clearance.php?id=' + requestId, '_blank');
    }
    
    function exportToPDF(requestId) {
        alert('PDF export for request #' + requestId + ' would be generated here.');
        // In a real application, this would call a PDF generation script
    }
    
    function copyToClipboard(requestId) {
        // Create a text string with request details
        const text = `Clearance Request #CR${requestId.toString().padStart(4, '0')}
Name: <?php echo $request['full_name']; ?>
Status: <?php echo ucfirst($request['status']); ?>
Purpose: <?php echo $request['purpose']; ?>
Date Submitted: <?php echo $request_date; ?>`;
        
        // Copy to clipboard
        navigator.clipboard.writeText(text).then(() => {
            alert('Request details copied to clipboard!');
        });
    }
</script>