<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Print Receipt";
$page_subtitle = "Generate and print fee receipts";

$payment = null;
$fee_info = null;
$total_paid_semester = 0;

if (isset($_GET['receipt'])) {
    $receipt_no = sanitize($conn, $_GET['receipt']);
    $result = $conn->query("
        SELECT p.*, s.name, s.father_name, s.course, s.phone, s.email, s.address, s.student_id as sid
        FROM payments p 
        JOIN students s ON p.student_id = s.student_id 
        WHERE p.receipt_no = '$receipt_no'
    ");
    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();
        // Get fee structure
        $fee_result = $conn->query("SELECT * FROM fee_structure WHERE course='{$payment['course']}' AND semester={$payment['semester']}");
        if ($fee_result->num_rows > 0) {
            $fee_info = $fee_result->fetch_assoc();
        }
        // Total paid for semester
        $paid_r = $conn->query("SELECT COALESCE(SUM(amount),0) as paid FROM payments WHERE student_id='{$payment['student_id']}' AND semester={$payment['semester']}");
        $total_paid_semester = $paid_r->fetch_assoc()['paid'];
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
    <style>
        .receipt-container {
            max-width: 750px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #1e293b;
            font-size: 13px;
            color: #1e293b;
        }
        .receipt-top {
            background: linear-gradient(135deg, #1e293b, #2563eb);
            color: #fff;
            padding: 24px 32px;
            text-align: center;
        }
        .receipt-top h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; letter-spacing: 0.5px; }
        .receipt-top p { font-size: 12px; opacity: 0.85; }
        .receipt-top .receipt-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 6px 24px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 700;
            margin-top: 10px;
            letter-spacing: 1px;
        }
        .receipt-body { padding: 24px 32px; }
        .receipt-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 24px;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .receipt-meta .meta-item {
            display: flex;
            gap: 8px;
            padding: 4px 0;
        }
        .receipt-meta .meta-item strong { min-width: 110px; color: #64748b; font-weight: 500; }
        .receipt-meta .meta-item span { color: #1e293b; font-weight: 600; }
        .receipt-fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .receipt-fee-table th {
            background: #1e293b;
            color: #fff;
            padding: 10px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .receipt-fee-table th:last-child { text-align: right; }
        .receipt-fee-table td {
            padding: 9px 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .receipt-fee-table td:last-child { text-align: right; font-weight: 500; }
        .receipt-fee-table tr:nth-child(even) { background: #f8fafc; }
        .receipt-fee-table .total-row td {
            background: #f1f5f9;
            font-weight: 700;
            font-size: 14px;
            border-top: 2px solid #1e293b;
        }
        .receipt-fee-table .paid-row td {
            background: #f0fdf4;
            font-weight: 700;
            font-size: 15px;
            color: #16a34a;
        }
        .receipt-fee-table .due-row td {
            font-weight: 600;
            font-size: 13px;
        }
        .amount-words {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 12px;
        }
        .amount-words strong { color: #64748b; }
        .payment-info-row {
            display: flex;
            gap: 24px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #64748b;
        }
        .payment-info-row span { font-weight: 600; color: #1e293b; }
        .receipt-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 0;
        }
        .receipt-signatures .sig-block {
            text-align: center;
            min-width: 140px;
        }
        .receipt-signatures .sig-line {
            border-top: 1px solid #1e293b;
            margin-top: 50px;
            padding-top: 6px;
            font-size: 11px;
            color: #64748b;
        }
        .receipt-bottom {
            text-align: center;
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px dashed #cbd5e1;
            font-size: 10px;
            color: #94a3b8;
        }
        @media print {
            .receipt-top { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .receipt-fee-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .receipt-fee-table .paid-row td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 768px) {
            .receipt-body { padding: 16px; }
            .receipt-top { padding: 16px; }
            .receipt-top h1 { font-size: 17px; }
            .receipt-meta { grid-template-columns: 1fr; padding: 12px; }
            .receipt-meta .meta-item strong { min-width: 90px; }
            .payment-info-row { flex-direction: column; gap: 6px; }
            .receipt-signatures { flex-direction: column; gap: 30px; align-items: center; }
        }
        @media (max-width: 480px) {
            .receipt-body { padding: 12px; }
            .receipt-top h1 { font-size: 15px; }
            .receipt-fee-table th, .receipt-fee-table td { padding: 7px 8px; font-size: 11px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="page-wrapper">
            <?php if(!$payment): ?>
            <!-- Receipt Selection -->
            <div class="content-card">
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
                                <?php echo $r['receipt_no'] . ' - ' . htmlspecialchars($r['name']) . ' - &#8377;' . number_format($r['amount']) . ' (' . date('d M Y', strtotime($r['payment_date'])) . ')'; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php else: ?>
            
            <!-- Print Actions -->
            <div class="no-print" style="margin-bottom:20px; display:flex; gap:12px; flex-wrap:wrap;">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <a href="receipt_pdf.php?receipt=<?php echo htmlspecialchars($_GET['receipt']); ?>" target="_blank" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a href="receipt.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <!-- Receipt -->
            <div class="receipt-container" id="receiptBox">
                <div class="receipt-top">
                    <h1>ABC College of Technology</h1>
                    <p>123 College Road, City, State - 400001 | Phone: 022-12345678 | info@abccollege.edu</p>
                    <div class="receipt-badge">FEE RECEIPT</div>
                </div>
                <div class="receipt-body">
                    <!-- Student & Receipt Info -->
                    <div class="receipt-meta">
                        <div class="meta-item"><strong>Receipt No:</strong> <span><?php echo $payment['receipt_no']; ?></span></div>
                        <div class="meta-item"><strong>Date:</strong> <span><?php echo date('d-M-Y', strtotime($payment['payment_date'])); ?></span></div>
                        <div class="meta-item"><strong>Student ID:</strong> <span><?php echo $payment['student_id']; ?></span></div>
                        <div class="meta-item"><strong>Semester:</strong> <span>Semester <?php echo $payment['semester']; ?></span></div>
                        <div class="meta-item"><strong>Student Name:</strong> <span><?php echo htmlspecialchars($payment['name']); ?></span></div>
                        <div class="meta-item"><strong>Father's Name:</strong> <span><?php echo htmlspecialchars($payment['father_name']); ?></span></div>
                        <div class="meta-item"><strong>Course:</strong> <span><?php echo htmlspecialchars($payment['course']); ?></span></div>
                        <div class="meta-item"><strong>Phone:</strong> <span><?php echo htmlspecialchars($payment['phone']); ?></span></div>
                    </div>

                    <!-- Fee Table -->
                    <table class="receipt-fee-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Description</th>
                                <th style="width:140px;">Amount (&#8377;)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($fee_info): ?>
                            <tr><td>1</td><td>Tuition Fee</td><td><?php echo number_format($fee_info['tuition_fee'], 2); ?></td></tr>
                            <tr><td>2</td><td>Examination Fee</td><td><?php echo number_format($fee_info['exam_fee'], 2); ?></td></tr>
                            <tr><td>3</td><td>Library Fee</td><td><?php echo number_format($fee_info['library_fee'], 2); ?></td></tr>
                            <tr><td>4</td><td>Laboratory Fee</td><td><?php echo number_format($fee_info['lab_fee'], 2); ?></td></tr>
                            <tr><td>5</td><td>Other Charges</td><td><?php echo number_format($fee_info['other_fee'], 2); ?></td></tr>
                            <tr class="total-row">
                                <td colspan="2" style="text-align:right;">Total Semester Fee</td>
                                <td>&#8377; <?php echo number_format($fee_info['total_fee'], 2); ?></td>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <td>1</td>
                                <td>Fee Payment - <?php echo htmlspecialchars($payment['course']); ?> (Semester <?php echo $payment['semester']; ?>)
                                    <?php if($payment['remarks']): ?><br><small style="color:#64748b;"><?php echo htmlspecialchars($payment['remarks']); ?></small><?php endif; ?>
                                </td>
                                <td><?php echo number_format($payment['amount'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="paid-row">
                                <td colspan="2" style="text-align:right;">Amount Paid (This Receipt)</td>
                                <td>&#8377; <?php echo number_format($payment['amount'], 2); ?></td>
                            </tr>
                            <?php if($fee_info): 
                                $due = $fee_info['total_fee'] - $total_paid_semester;
                            ?>
                            <tr class="due-row">
                                <td colspan="2" style="text-align:right;">Total Paid (Semester)</td>
                                <td style="color:#2563eb;">&#8377; <?php echo number_format($total_paid_semester, 2); ?></td>
                            </tr>
                            <tr class="due-row">
                                <td colspan="2" style="text-align:right;">Balance Due</td>
                                <td style="color:<?php echo $due > 0 ? '#ef4444' : '#16a34a'; ?>;">&#8377; <?php echo number_format(max(0, $due), 2); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Amount in Words -->
                    <div class="amount-words">
                        <strong>Amount in Words:</strong> <span id="amountWords"></span>
                    </div>

                    <!-- Payment Info -->
                    <div class="payment-info-row">
                        <div><strong>Payment Method:</strong> <span><?php echo ucfirst($payment['payment_method']); ?></span></div>
                        <?php if($payment['remarks']): ?>
                        <div><strong>Remarks:</strong> <span><?php echo htmlspecialchars($payment['remarks']); ?></span></div>
                        <?php endif; ?>
                    </div>

                    <!-- Signatures -->
                    <div class="receipt-signatures">
                        <div class="sig-block"><div class="sig-line">Student Signature</div></div>
                        <div class="sig-block"><div class="sig-line">Cashier</div></div>
                        <div class="sig-block"><div class="sig-line">Authorized Signature</div></div>
                    </div>

                    <!-- Footer -->
                    <div class="receipt-bottom">
                        This is a computer generated receipt | Generated on <?php echo date('d-M-Y h:i A'); ?> | College Fee Management System
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
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
    if (el) { el.textContent = numberToWords(<?php echo $payment ? $payment['amount'] : 0; ?>); }
    </script>
    <?php include 'includes/confirm_popup.php'; ?>
</body>
</html>
