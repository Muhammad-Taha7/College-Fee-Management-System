<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Fee Payment";
$page_subtitle = "Process student fee payments";

$message = '';
$msg_type = '';
$selected_student = null;
$fee_info = null;
$paid_amount = 0;

// If student selected
if (isset($_GET['sid'])) {
    $sid = sanitize($conn, $_GET['sid']);
    $result = $conn->query("SELECT * FROM students WHERE student_id = '$sid' AND status='active'");
    if ($result->num_rows > 0) {
        $selected_student = $result->fetch_assoc();
        
        // Get fee structure
        $fee_result = $conn->query("SELECT * FROM fee_structure WHERE course='{$selected_student['course']}' AND semester={$selected_student['semester']}");
        if ($fee_result->num_rows > 0) {
            $fee_info = $fee_result->fetch_assoc();
        }
        
        // Get paid amount for current semester 
        $paid_result = $conn->query("SELECT COALESCE(SUM(amount),0) as paid FROM payments WHERE student_id='$sid' AND semester={$selected_student['semester']}");
        $paid_amount = $paid_result->fetch_assoc()['paid'];
    }
}

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = sanitize($conn, $_POST['student_id']);
    $amount = floatval($_POST['amount']);
    $payment_method = sanitize($conn, $_POST['payment_method']);
    $semester = intval($_POST['semester']);
    $remarks = sanitize($conn, $_POST['remarks']);
    $receipt_no = generateReceiptNo($conn);
    $payment_date = date('Y-m-d');

    if ($amount <= 0) {
        $message = "Please enter a valid amount!";
        $msg_type = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO payments (student_id, receipt_no, amount, payment_date, payment_method, semester, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssis", $student_id, $receipt_no, $amount, $payment_date, $payment_method, $semester, $remarks);
        
        if ($stmt->execute()) {
            $message = "Payment processed successfully! Receipt No: <strong>$receipt_no</strong> &nbsp; <a href='receipt.php?receipt=$receipt_no' class='btn btn-sm btn-success'><i class='fas fa-print'></i> Print Receipt</a>";
            $msg_type = "success";
            // Refresh data
            header("Location: fee_payment.php?sid=$student_id&success=$receipt_no");
            exit;
        } else {
            $message = "Error: " . $stmt->error;
            $msg_type = "danger";
        }
        $stmt->close();
    }
}

// Success message after redirect
if (isset($_GET['success'])) {
    $receipt = htmlspecialchars($_GET['success']);
    $message = "Payment processed successfully! Receipt No: <strong>$receipt</strong> &nbsp; <a href='receipt.php?receipt=$receipt' class='btn btn-sm btn-success'><i class='fas fa-print'></i> Print Receipt</a>";
    $msg_type = "success";
}

// Get all active students for dropdown
$all_students = $conn->query("SELECT student_id, name, course, semester FROM students WHERE status='active' ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Payment - College Fee Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="page-wrapper">
            <?php if($message): ?>
                <div class="alert alert-<?php echo $msg_type; ?>">
                    <i class="fas fa-<?php echo $msg_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Student Selection -->
            <div class="content-card animate-in">
                <div class="card-header">
                    <h3><i class="fas fa-search"></i> Select Student</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label>Choose Student <span class="required">*</span></label>
                            <select name="sid" class="form-control" onchange="this.form.submit()" required>
                                <option value="">-- Select Student --</option>
                                <?php while($s = $all_students->fetch_assoc()): ?>
                                <option value="<?php echo $s['student_id']; ?>" <?php echo (isset($_GET['sid']) && $_GET['sid']==$s['student_id'])?'selected':''; ?>>
                                    <?php echo $s['student_id'] . ' - ' . htmlspecialchars($s['name']) . ' (' . $s['course'] . ' - Sem ' . $s['semester'] . ')'; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <?php if($selected_student): ?>
            <div class="charts-grid">
                <!-- Student Info -->
                <div class="content-card animate-in">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Student Details</h3>
                        <span class="badge badge-success"><?php echo $selected_student['status']; ?></span>
                    </div>
                    <div class="card-body">
                        <table style="width:100%;">
                            <tr><td style="padding:8px;color:var(--text-muted);width:140px;">Student ID</td><td style="padding:8px;"><strong><?php echo $selected_student['student_id']; ?></strong></td></tr>
                            <tr><td style="padding:8px;color:var(--text-muted);">Name</td><td style="padding:8px;"><strong><?php echo htmlspecialchars($selected_student['name']); ?></strong></td></tr>
                            <tr><td style="padding:8px;color:var(--text-muted);">Father</td><td style="padding:8px;"><?php echo htmlspecialchars($selected_student['father_name']); ?></td></tr>
                            <tr><td style="padding:8px;color:var(--text-muted);">Course</td><td style="padding:8px;"><?php echo $selected_student['course']; ?></td></tr>
                            <tr><td style="padding:8px;color:var(--text-muted);">Semester</td><td style="padding:8px;">Semester <?php echo $selected_student['semester']; ?></td></tr>
                            <tr><td style="padding:8px;color:var(--text-muted);">Phone</td><td style="padding:8px;"><?php echo htmlspecialchars($selected_student['phone']); ?></td></tr>
                        </table>
                    </div>
                </div>

                <!-- Fee Details & Payment Form -->
                <div class="content-card animate-in">
                    <div class="card-header">
                        <h3><i class="fas fa-money-bill-wave"></i> Fee Payment</h3>
                    </div>
                    <div class="card-body">
                        <?php if($fee_info): ?>
                        <!-- Fee Breakdown -->
                        <div style="background:var(--light-bg);border-radius:var(--radius-sm);padding:16px;margin-bottom:20px;">
                            <h4 style="font-size:14px;margin-bottom:12px;color:var(--primary);">Fee Structure - <?php echo $selected_student['course']; ?> (Sem <?php echo $selected_student['semester']; ?>)</h4>
                            <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;"><span>Tuition Fee</span><span>₹<?php echo number_format($fee_info['tuition_fee']); ?></span></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;"><span>Exam Fee</span><span>₹<?php echo number_format($fee_info['exam_fee']); ?></span></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;"><span>Library Fee</span><span>₹<?php echo number_format($fee_info['library_fee']); ?></span></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;"><span>Lab Fee</span><span>₹<?php echo number_format($fee_info['lab_fee']); ?></span></div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;"><span>Other Fee</span><span>₹<?php echo number_format($fee_info['other_fee']); ?></span></div>
                            <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;border-top:2px solid var(--border);margin-top:8px;font-weight:700;">
                                <span>Total Fee</span><span>₹<?php echo number_format($fee_info['total_fee']); ?></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;color:var(--success);font-weight:600;">
                                <span>Paid</span><span>₹<?php echo number_format($paid_amount); ?></span>
                            </div>
                            <?php $due = $fee_info['total_fee'] - $paid_amount; ?>
                            <div style="display:flex;justify-content:space-between;font-size:14px;padding:4px 0;color:<?php echo $due > 0 ? 'var(--danger)' : 'var(--success)'; ?>;font-weight:700;">
                                <span>Due Amount</span><span>₹<?php echo number_format($due); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(!$fee_info || ($fee_info && ($fee_info['total_fee'] - $paid_amount) > 0)): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="student_id" value="<?php echo $selected_student['student_id']; ?>">
                            <input type="hidden" name="semester" value="<?php echo $selected_student['semester']; ?>">
                            
                            <div class="form-group">
                                <label>Amount (₹) <span class="required">*</span></label>
                                <input type="number" name="amount" class="form-control" placeholder="Enter amount" 
                                       value="<?php echo $fee_info ? max(0, $fee_info['total_fee'] - $paid_amount) : ''; ?>" 
                                       min="1" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="cash">Cash</option>
                                    <option value="online">Online</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Remarks</label>
                                <input type="text" name="remarks" class="form-control" placeholder="Payment remarks...">
                            </div>
                            <button type="submit" class="btn btn-success" style="width:100%;">
                                <i class="fas fa-check-circle"></i> Process Payment
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-success" style="margin:0;">
                            <i class="fas fa-check-circle"></i>
                            All fees for this semester are paid!
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Payment History for this student -->
            <?php 
            $history = $conn->query("SELECT * FROM payments WHERE student_id='{$selected_student['student_id']}' ORDER BY payment_date DESC");
            if($history->num_rows > 0):
            ?>
            <div class="content-card animate-in">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Payment History - <?php echo htmlspecialchars($selected_student['name']); ?></h3>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Receipt No</th>
                                    <th>Amount</th>
                                    <th>Semester</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($p = $history->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge badge-primary"><?php echo $p['receipt_no']; ?></span></td>
                                    <td><strong>₹<?php echo number_format($p['amount']); ?></strong></td>
                                    <td>Sem <?php echo $p['semester']; ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($p['payment_method']); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($p['payment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($p['remarks']); ?></td>
                                    <td style="display:flex;gap:4px;">
                                        <a href="receipt.php?receipt=<?php echo $p['receipt_no']; ?>" class="btn btn-sm btn-primary" title="View Receipt">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="receipt_pdf.php?receipt=<?php echo $p['receipt_no']; ?>" target="_blank" class="btn btn-sm btn-success" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
