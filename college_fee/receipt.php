<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Print Receipt";
$page_subtitle = "Generate and print fee receipts";

$payment = null;
$student = null;

if (isset($_GET['receipt'])) {
    $receipt_no = sanitize($conn, $_GET['receipt']);
    $result = $conn->query("
        SELECT p.*, s.name, s.father_name, s.course, s.phone, s.email, s.address 
        FROM payments p 
        JOIN students s ON p.student_id = s.student_id 
        WHERE p.receipt_no = '$receipt_no'
    ");
    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();
    }
}

// Get all receipts list
$all_receipts = $conn->query("
    SELECT p.receipt_no, p.student_id, p.amount, p.payment_date, s.name 
    FROM payments p 
    JOIN students s ON p.student_id = s.student_id 
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - College Fee Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="page-wrapper">
            <?php if(!$payment): ?>
            <!-- Receipt Selection -->
            <div class="content-card animate-in">
                <div class="card-header">
                    <h3><i class="fas fa-receipt"></i> Select Receipt to Print</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Choose a Receipt</label>
                        <select class="form-control" onchange="if(this.value) window.location='?receipt='+this.value">
                            <option value="">-- Select Receipt --</option>
                            <?php while($r = $all_receipts->fetch_assoc()): ?>
                            <option value="<?php echo $r['receipt_no']; ?>">
                                <?php echo $r['receipt_no'] . ' - ' . htmlspecialchars($r['name']) . ' - ₹' . number_format($r['amount']) . ' (' . date('d M Y', strtotime($r['payment_date'])) . ')'; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php else: ?>
            
            <!-- Print Actions -->
            <div class="no-print" style="margin-bottom:20px; display:flex; gap:12px;">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <a href="receipt_pdf.php?receipt=<?php echo htmlspecialchars($_GET['receipt']); ?>" target="_blank" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a href="receipt.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Receipts
                </a>
            </div>

            <!-- Receipt -->
            <div class="content-card animate-in">
                <div class="card-body">
                    <div class="receipt-box" id="receiptBox">
                        <div class="receipt-header">
                            <h1>🎓 ABC College of Technology</h1>
                            <p style="font-size:13px;color:#555;">123 College Road, City, State - 400001 | Phone: 022-12345678</p>
                            <h3 style="margin-top:12px;background:var(--primary);color:white;display:inline-block;padding:6px 24px;border-radius:4px;">FEE RECEIPT</h3>
                        </div>

                        <div class="receipt-info">
                            <div class="info-row">
                                <strong>Receipt No:</strong>
                                <span><?php echo $payment['receipt_no']; ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Date:</strong>
                                <span><?php echo date('d-M-Y', strtotime($payment['payment_date'])); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Student ID:</strong>
                                <span><?php echo $payment['student_id']; ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Semester:</strong>
                                <span>Semester <?php echo $payment['semester']; ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Student Name:</strong>
                                <span><?php echo htmlspecialchars($payment['name']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Father's Name:</strong>
                                <span><?php echo htmlspecialchars($payment['father_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Course:</strong>
                                <span><?php echo htmlspecialchars($payment['course']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Phone:</strong>
                                <span><?php echo htmlspecialchars($payment['phone']); ?></span>
                            </div>
                        </div>

                        <table class="receipt-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Description</th>
                                    <th style="width:150px;text-align:right;">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Fee Payment - <?php echo htmlspecialchars($payment['course']); ?> (Semester <?php echo $payment['semester']; ?>)
                                        <?php if($payment['remarks']): ?>
                                        <br><small style="color:#666;"><?php echo htmlspecialchars($payment['remarks']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;"><?php echo number_format($payment['amount'], 2); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" style="text-align:right;"><strong>Total Paid:</strong></td>
                                    <td style="text-align:right;"><strong>₹ <?php echo number_format($payment['amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div style="background:#f8fafc;padding:12px;border-radius:6px;margin-bottom:20px;">
                            <strong style="font-size:13px;">Amount in words:</strong>
                            <span style="font-size:13px;color:#555;" id="amountWords"></span>
                        </div>

                        <div style="font-size:12px;color:#666;margin-bottom:8px;">
                            <strong>Payment Method:</strong> <?php echo ucfirst($payment['payment_method']); ?>
                        </div>

                        <div class="receipt-footer">
                            <div class="sign-area">
                                <div class="line" style="margin-top:40px;">Student Signature</div>
                            </div>
                            <div class="sign-area">
                                <div class="line" style="margin-top:40px;">Authorized Signature</div>
                            </div>
                        </div>

                        <div style="text-align:center;margin-top:24px;padding-top:12px;border-top:1px dashed #ccc;font-size:11px;color:#999;">
                            This is a computer generated receipt. | Generated on <?php echo date('d-M-Y h:i A'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Number to words converter
    function numberToWords(num) {
        if (num === 0) return 'Zero';
        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        function convert(n) {
            if (n < 20) return ones[n];
            if (n < 100) return tens[Math.floor(n/10)] + (n%10 ? ' ' + ones[n%10] : '');
            if (n < 1000) return ones[Math.floor(n/100)] + ' Hundred' + (n%100 ? ' and ' + convert(n%100) : '');
            if (n < 100000) return convert(Math.floor(n/1000)) + ' Thousand' + (n%1000 ? ' ' + convert(n%1000) : '');
            if (n < 10000000) return convert(Math.floor(n/100000)) + ' Lakh' + (n%100000 ? ' ' + convert(n%100000) : '');
            return convert(Math.floor(n/10000000)) + ' Crore' + (n%10000000 ? ' ' + convert(n%10000000) : '');
        }
        return convert(Math.floor(num)) + ' Rupees Only';
    }
    
    const el = document.getElementById('amountWords');
    if (el) {
        el.textContent = numberToWords(<?php echo $payment ? $payment['amount'] : 0; ?>);
    }
    </script>
</body>
</html>
