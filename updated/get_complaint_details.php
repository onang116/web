<?php
require_once 'config.php';

// Only officials can access this
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'official') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Get the complaint ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid complaint ID');
}

$complaint_id = $conn->real_escape_string($_GET['id']);

// Fetch complaint details with user information
$sql = "SELECT c.*, u.username, u.email as user_email, u.phone as user_phone, 
               u.full_name as account_name, u.created_at as user_joined, u.user_type as complainant_type
        FROM complaints c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.id = '$complaint_id'";

$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    echo '<div style="padding: 2rem; text-align: center;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ffa502;"></i>
            <h3 style="margin-top: 1rem;">Complaint Not Found</h3>
            <p>The requested complaint details could not be found.</p>
          </div>';
    exit();
}

$complaint = $result->fetch_assoc();

// Format dates
$created_date = date('F d, Y h:i A', strtotime($complaint['created_at']));
$resolved_date = $complaint['resolved_at'] ? 
    date('F d, Y h:i A', strtotime($complaint['resolved_at'])) : 'Not yet resolved';
$user_joined = $complaint['user_joined'] ? 
    date('F d, Y', strtotime($complaint['user_joined'])) : 'N/A';

// Determine status color and icon
$status_config = [
    'pending' => ['color' => '#ffa502', 'icon' => 'fas fa-clock', 'label' => 'Pending Review'],
    'reviewing' => ['color' => '#1e90ff', 'icon' => 'fas fa-search', 'label' => 'Under Review'],
    'resolved' => ['color' => '#2ed573', 'icon' => 'fas fa-check-circle', 'label' => 'Resolved']
];

$status_info = $status_config[$complaint['status']] ?? ['color' => '#666', 'icon' => 'fas fa-question-circle', 'label' => 'Unknown'];
?>

<div style="max-width: 100%;">
    <!-- Complaint Header -->
    <div style="background: linear-gradient(135deg, <?php echo $status_info['color']; ?> 0%, #ffffff 100%); padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; color: #333;">
                    <i class="<?php echo $status_info['icon']; ?>"></i>
                    Complaint #CP<?php echo str_pad($complaint['id'], 4, '0', STR_PAD_LEFT); ?>
                </h3>
                <p style="margin: 0.25rem 0 0 0; color: #666;">
                    Submitted on <?php echo $created_date; ?>
                    <?php if ($complaint['status'] === 'resolved'): ?>
                        • Resolved on <?php echo $resolved_date; ?>
                    <?php endif; ?>
                </p>
            </div>
            <div style="text-align: right;">
                <span style="display: inline-block; padding: 5px 15px; background-color: <?php echo $status_info['color']; ?>; color: white; border-radius: 20px; font-weight: 600;">
                    <?php echo strtoupper($complaint['status']); ?>
                </span>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #666;">
                    <?php echo $status_info['label']; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Three Column Layout -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        
        <!-- Complaint Information -->
        <div>
            <h4 style="color: #0d4a9e; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eee;">
                <i class="fas fa-exclamation-circle"></i> Complaint Details
            </h4>
            
            <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666; width: 35%; vertical-align: top;"><strong>Type of Concern:</strong></td>
                        <td style="padding: 8px 0;">
                            <span style="background-color: #ffeaa7; color: #856404; padding: 3px 10px; border-radius: 15px; font-weight: 500;">
                                <?php echo htmlspecialchars($complaint['complaint_type']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; vertical-align: top;"><strong>Location:</strong></td>
                        <td style="padding: 8px 0;">
                            <i class="fas fa-map-marker-alt" style="color: #ff4757;"></i>
                            <?php echo htmlspecialchars($complaint['location']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; vertical-align: top;"><strong>Complainant Name:</strong></td>
                        <td style="padding: 8px 0;">
                            <?php if (!empty($complaint['complainant_name'])): ?>
                                <?php echo htmlspecialchars($complaint['complainant_name']); ?>
                            <?php else: ?>
                                <span style="color: #999; font-style: italic;">Not provided</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; vertical-align: top;"><strong>Contact Information:</strong></td>
                        <td style="padding: 8px 0;">
                            <?php if (!empty($complaint['contact_info'])): ?>
                                <?php 
                                $contact_info = htmlspecialchars($complaint['contact_info']);
                                if (filter_var($contact_info, FILTER_VALIDATE_EMAIL)): ?>
                                    <a href="mailto:<?php echo $contact_info; ?>" style="color: #0d4a9e;">
                                        <i class="fas fa-envelope"></i> <?php echo $contact_info; ?>
                                    </a>
                                <?php elseif (preg_match('/^[0-9+\s\-]+$/', $contact_info)): ?>
                                    <a href="tel:<?php echo $contact_info; ?>" style="color: #0d4a9e;">
                                        <i class="fas fa-phone"></i> <?php echo $contact_info; ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo $contact_info; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #999; font-style: italic;">Not provided</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Complaint Details -->
            <h4 style="color: #0d4a9e; margin-top: 1.5rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eee;">
                <i class="fas fa-align-left"></i> Detailed Description
            </h4>
            
            <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 5px; border-left: 4px solid #0d4a9e;">
                <?php echo nl2br(htmlspecialchars($complaint['details'])); ?>
            </div>
        </div>

        <!-- Account Information & Resolution -->
        <div>
            <!-- Account Information -->
            <h4 style="color: #0d4a9e; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eee;">
                <i class="fas fa-user-circle"></i> Account Information
            </h4>
            
            <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
                <?php if ($complaint['user_id']): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #666; width: 40%;"><strong>Account Name:</strong></td>
                            <td style="padding: 8px 0;"><?php echo htmlspecialchars($complaint['account_name']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>Username:</strong></td>
                            <td style="padding: 8px 0;"><?php echo htmlspecialchars($complaint['username']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>Email:</strong></td>
                            <td style="padding: 8px 0;">
                                <a href="mailto:<?php echo htmlspecialchars($complaint['user_email']); ?>" style="color: #0d4a9e;">
                                    <?php echo htmlspecialchars($complaint['user_email']); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #666;"><strong>Phone:</strong></td>
                            <td style="padding: 8px 0;">
                                <a href="tel:<?php echo htmlspecialchars($complaint['user_phone']); ?>" style="color: #0d4a9e;">
                                    <?php echo htmlspecialchars($complaint['user_phone']); ?>
                                </a>
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
                        This complaint was submitted anonymously or by a non-registered user.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Resolution Notes -->
            <h4 style="color: #0d4a9e; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #eee;">
                <i class="fas fa-clipboard-check"></i> Resolution Information
            </h4>
            
            <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 5px;">
                <?php if (!empty($complaint['resolution_notes'])): ?>
                    <div style="background-color: white; padding: 1rem; border-radius: 5px; border-left: 4px solid #2ed573;">
                        <h5 style="margin-top: 0; color: #2ed573;">
                            <i class="fas fa-check"></i> Resolution Notes
                        </h5>
                        <?php echo nl2br(htmlspecialchars($complaint['resolution_notes'])); ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #999; font-style: italic; padding: 1rem;">
                        <i class="fas fa-edit" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                        No resolution notes have been added yet.
                    </p>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div style="display: flex; gap: 10px; margin-top: 1.5rem; flex-wrap: wrap;">
                    <?php if ($complaint['status'] === 'pending'): ?>
                        <button onclick="startReview(<?php echo $complaint['id']; ?>)" 
                                style="background: linear-gradient(135deg, #1e90ff 0%, #1e6bc4 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                            <i class="fas fa-search"></i> Start Review
                        </button>
                        <button onclick="markAsResolved(<?php echo $complaint['id']; ?>)" 
                                style="background: linear-gradient(135deg, #2ed573 0%, #1e9e5a 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                            <i class="fas fa-check-circle"></i> Mark Resolved
                        </button>
                    <?php elseif ($complaint['status'] === 'reviewing'): ?>
                        <button onclick="markAsResolved(<?php echo $complaint['id']; ?>)" 
                                style="background: linear-gradient(135deg, #2ed573 0%, #1e9e5a 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                            <i class="fas fa-check-circle"></i> Mark Resolved
                        </button>
                        <button onclick="setToPending(<?php echo $complaint['id']; ?>)" 
                                style="background: linear-gradient(135deg, #ffa502 0%, #ff7e30 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                            <i class="fas fa-undo"></i> Back to Pending
                        </button>
                    <?php elseif ($complaint['status'] === 'resolved'): ?>
                        <button onclick="setToReviewing(<?php echo $complaint['id']; ?>)" 
                                style="background: linear-gradient(135deg, #1e90ff 0%, #1e6bc4 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                            <i class="fas fa-redo"></i> Re-open
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($complaint['status'] !== 'resolved'): ?>
                        <button onclick="addResolutionNotes(<?php echo $complaint['id']; ?>)" 
                                style="background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif;">
                            <i class="fas fa-sticky-note"></i> Add Notes
                        </button>
                    <?php endif; ?>
                    
                    <button onclick="closeModal('viewComplaintModal')" 
                            style="background-color: #f1f1f1; color: #333; border: 1px solid #ddd; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; margin-left: auto;">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority & Additional Actions -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        
        <!-- Priority Indicator -->
        <div style="padding: 1rem; background-color: #f0f5ff; border-radius: 5px;">
            <h4 style="color: #0d4a9e; margin-bottom: 0.5rem;">
                <i class="fas fa-flag"></i> Priority Assessment
            </h4>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 20px; height: 20px; border-radius: 50%; 
                            background-color: 
                            <?php 
                            $type = strtolower($complaint['complaint_type']);
                            if (strpos($type, 'safety') !== false || strpos($type, 'emergency') !== false) {
                                echo '#ff4757'; // Red - High priority
                            } elseif (strpos($type, 'garbage') !== false || strpos($type, 'health') !== false) {
                                echo '#ffa502'; // Orange - Medium priority
                            } else {
                                echo '#2ed573'; // Green - Normal priority
                            }
                            ?>;">
                </div>
                <div>
                    <strong>
                        <?php
                        if (strpos($type, 'safety') !== false || strpos($type, 'emergency') !== false) {
                            echo 'HIGH PRIORITY - Safety Concern';
                        } elseif (strpos($type, 'garbage') !== false || strpos($type, 'health') !== false) {
                            echo 'MEDIUM PRIORITY - Health/Environmental';
                        } else {
                            echo 'NORMAL PRIORITY';
                        }
                        ?>
                    </strong>
                    <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.9rem;">
                        Based on complaint type: <?php echo htmlspecialchars($complaint['complaint_type']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="padding: 1rem; background-color: #fff8e1; border-radius: 5px;">
            <h4 style="color: #0d4a9e; margin-bottom: 0.5rem;">
                <i class="fas fa-bolt"></i> Quick Actions
            </h4>
            <div style="display: flex; gap: 10px;">
                <button onclick="contactComplainant(<?php echo $complaint['id']; ?>)" 
                        style="background: linear-gradient(135deg, #0d4a9e 0%, #1e6bc4 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; flex: 1;">
                    <i class="fas fa-phone"></i> Contact
                </button>
                <button onclick="createFollowupTask(<?php echo $complaint['id']; ?>)" 
                        style="background: linear-gradient(135deg, #ff7e30 0%, #ff9a52 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; flex: 1;">
                    <i class="fas fa-tasks"></i> Create Task
                </button>
                <button onclick="printComplaint(<?php echo $complaint['id']; ?>)" 
                        style="background: linear-gradient(135deg, #2ed573 0%, #1e9e5a 100%); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; flex: 1;">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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
        font-size: 0.9rem;
    }
    
    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        
        div[style*="display: flex; gap: 10px;"] {
            flex-direction: column;
        }
        
        button {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // JavaScript functions for complaint actions
    
    function startReview(complaintId) {
        updateComplaintStatus(complaintId, 'reviewing');
    }
    
    function markAsResolved(complaintId) {
        const notes = prompt('Please add resolution notes (optional):', '');
        if (notes !== null) {
            updateComplaintStatus(complaintId, 'resolved', notes);
        }
    }
    
    function setToPending(complaintId) {
        if (confirm('Set this complaint back to pending status?')) {
            updateComplaintStatus(complaintId, 'pending');
        }
    }
    
    function setToReviewing(complaintId) {
        if (confirm('Re-open this complaint for review?')) {
            updateComplaintStatus(complaintId, 'reviewing');
        }
    }
    
    function addResolutionNotes(complaintId) {
        const currentNotes = document.querySelector('#resolutionNotes') ? 
            document.querySelector('#resolutionNotes').value : '';
        const notes = prompt('Enter resolution notes:', currentNotes);
        if (notes !== null) {
            updateComplaintNotes(complaintId, notes);
        }
    }
    
    function updateComplaintStatus(complaintId, status, notes = '') {
        const formData = new FormData();
        formData.append('complaint_id', complaintId);
        formData.append('status', status);
        if (notes) {
            formData.append('resolution_notes', notes);
        }
        formData.append('update_complaint_status', '1');
        
        fetch(window.location.pathname.replace('get_complaint_details.php', 'admin_dashboard.php'), {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            alert('Complaint status updated successfully!');
            closeModal('viewComplaintModal');
            location.reload();
        })
        .catch(error => {
            alert('Error updating status: ' + error);
        });
    }
    
    function updateComplaintNotes(complaintId, notes) {
        const formData = new FormData();
        formData.append('complaint_id', complaintId);
        formData.append('resolution_notes', notes);
        formData.append('update_complaint_notes', '1');
        
        fetch(window.location.pathname.replace('get_complaint_details.php', 'admin_dashboard.php'), {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            alert('Notes updated successfully!');
            // Refresh the modal content
            location.reload();
        })
        .catch(error => {
            alert('Error updating notes: ' + error);
        });
    }
    
    function contactComplainant(complaintId) {
        // This would open a contact modal or page
        alert('Contact functionality for complaint #' + complaintId + ' would open here.');
    }
    
    function createFollowupTask(complaintId) {
        const task = prompt('Enter follow-up task description:', 'Follow up on complaint #' + complaintId);
        if (task) {
            alert('Task created: ' + task);
        }
    }
    
    function printComplaint(complaintId) {
        window.open('print_complaint.php?id=' + complaintId, '_blank');
    }
</script>