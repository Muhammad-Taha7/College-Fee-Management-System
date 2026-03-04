<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'libs/fpdf.php';

if (!isset($_GET['receipt'])) {
    header("Location: receipt.php");
    exit;
}

$receipt_no = $conn->real_escape_string($_GET['receipt']);
$result = $conn->query("
    SELECT p.*, s.name, s.father_name, s.course, s.phone, s.email, s.address, s.student_id as sid
    FROM payments p 
    JOIN students s ON p.student_id = s.student_id 
    WHERE p.receipt_no = '$receipt_no'
");

if ($result->num_rows === 0) {
    die("Receipt not found!");
}

$data = $result->fetch_assoc();

// Get fee structure for breakdown
$fee = $conn->query("SELECT * FROM fee_structure WHERE course='{$data['course']}' AND semester={$data['semester']}")->fetch_assoc();

// Number to words function
function numberToWords($num) {
    if ($num == 0) return 'Zero';
    $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
             'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
             'Seventeen','Eighteen','Nineteen'];
    $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    
    $num = (int)$num;
    $words = '';
    
    if ($num >= 10000000) {
        $words .= numberToWords((int)($num / 10000000)) . ' Crore ';
        $num %= 10000000;
    }
    if ($num >= 100000) {
        $words .= numberToWords((int)($num / 100000)) . ' Lakh ';
        $num %= 100000;
    }
    if ($num >= 1000) {
        $words .= numberToWords((int)($num / 1000)) . ' Thousand ';
        $num %= 1000;
    }
    if ($num >= 100) {
        $words .= $ones[(int)($num / 100)] . ' Hundred ';
        $num %= 100;
        if ($num > 0) $words .= 'and ';
    }
    if ($num >= 20) {
        $words .= $tens[(int)($num / 10)] . ' ';
        $num %= 10;
    }
    if ($num > 0) {
        $words .= $ones[$num] . ' ';
    }
    
    return trim($words);
}

// Get total paid for this semester
$total_paid_sem = $conn->query("SELECT COALESCE(SUM(amount),0) as paid FROM payments WHERE student_id='{$data['student_id']}' AND semester={$data['semester']}")->fetch_assoc()['paid'];

// ============== BUILD PDF ==============
class FeeReceipt extends FPDF {
    function Header() {
        // Blue header background
        $this->SetFillColor(37, 99, 235);
        $this->Rect(10, 10, 190, 38, 'F');
        
        // College name
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 14);
        $this->Cell(190, 10, 'ABC College of Technology', 0, 1, 'C');
        
        // Address
        $this->SetFont('Arial', '', 9);
        $this->SetXY(10, 25);
        $this->Cell(190, 5, '123 College Road, City, State - 400001 | Phone: 022-12345678 | Email: info@abccollege.edu', 0, 1, 'C');
        
        // FEE RECEIPT title
        $this->SetFont('Arial', 'B', 14);
        $this->SetXY(10, 33);
        $this->Cell(190, 10, 'FEE RECEIPT', 0, 1, 'C');
        
        // Line below header
        $this->SetDrawColor(37, 99, 235);
        $this->SetLineWidth(0.8);
        $this->Line(10, 50, 200, 50);
        $this->Ln(6);
    }
    
    function Footer() {
        $this->SetY(-30);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.3);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->Ln(3);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 5, 'This is a computer generated receipt.', 0, 1, 'C');
        $this->Cell(0, 5, 'Generated on ' . date('d-M-Y h:i A') . ' | College Fee Management System', 0, 0, 'C');
    }
    
    // Colored cell helper
    function InfoRow($label, $value, $x = null) {
        if ($x !== null) $this->SetX($x);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(40, 7, $label, 0, 0);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(30, 30, 30);
        $this->Cell(55, 7, $value, 0, 0);
    }
}

$pdf = new FeeReceipt();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 35);

// ======= Receipt Info Section =======
$pdf->SetY(55);

// Receipt No & Date row
$pdf->InfoRow('Receipt No:', $data['receipt_no']);
$pdf->InfoRow('Date:', date('d-M-Y', strtotime($data['payment_date'])), 110);
$pdf->Ln(8);

// Student ID & Semester
$pdf->InfoRow('Student ID:', $data['sid']);
$pdf->InfoRow('Semester:', 'Semester ' . $data['semester'], 110);
$pdf->Ln(8);

// Student Name & Father Name
$pdf->InfoRow('Student Name:', $data['name']);
$pdf->InfoRow("Father's Name:", $data['father_name'], 110);
$pdf->Ln(8);

// Course & Phone
$pdf->InfoRow('Course:', $data['course']);
$pdf->InfoRow('Phone:', $data['phone'], 110);
$pdf->Ln(8);

// Email & Payment Method
$pdf->InfoRow('Email:', $data['email'] ? $data['email'] : 'N/A');
$pdf->InfoRow('Payment Mode:', ucfirst($data['payment_method']), 110);
$pdf->Ln(5);

// Separator
$pdf->SetDrawColor(37, 99, 235);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

// ======= Fee Table =======
// Table Header
$pdf->SetFillColor(37, 99, 235);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 9, '#', 1, 0, 'C', true);
$pdf->Cell(120, 9, 'Description', 1, 0, 'L', true);
$pdf->Cell(60, 9, 'Amount (Rs.)', 1, 1, 'R', true);

// Table Body
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFont('Arial', '', 10);

$row_num = 1;
if ($fee) {
    $items = [
        ['Tuition Fee', $fee['tuition_fee']],
        ['Examination Fee', $fee['exam_fee']],
        ['Library Fee', $fee['library_fee']],
        ['Laboratory Fee', $fee['lab_fee']],
        ['Other Charges', $fee['other_fee']],
    ];
    foreach ($items as $item) {
        $fill = ($row_num % 2 == 0);
        if ($fill) $pdf->SetFillColor(245, 247, 250);
        $pdf->Cell(10, 8, $row_num, 1, 0, 'C', $fill);
        $pdf->Cell(120, 8, $item[0], 1, 0, 'L', $fill);
        $pdf->Cell(60, 8, 'Rs. ' . number_format($item[1], 2), 1, 1, 'R', $fill);
        $row_num++;
    }
    
    // Total Fee row
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(230, 240, 255);
    $pdf->Cell(130, 9, 'Total Semester Fee', 1, 0, 'R', true);
    $pdf->Cell(60, 9, 'Rs. ' . number_format($fee['total_fee'], 2), 1, 1, 'R', true);
} else {
    $pdf->Cell(10, 8, '1', 1, 0, 'C');
    $pdf->Cell(120, 8, 'Fee Payment - ' . $data['course'] . ' (Semester ' . $data['semester'] . ')', 1, 0, 'L');
    $pdf->Cell(60, 8, 'Rs. ' . number_format($data['amount'], 2), 1, 1, 'R');
}

// Amount Paid row (Green)
$pdf->SetFillColor(220, 252, 231);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(130, 10, 'AMOUNT PAID (This Receipt)', 1, 0, 'R', true);
$pdf->SetTextColor(16, 185, 129);
$pdf->Cell(60, 10, 'Rs. ' . number_format($data['amount'], 2), 1, 1, 'R', true);

// Total Paid & Balance Due (if fee structure exists)
if ($fee) {
    $due = $fee['total_fee'] - $total_paid_sem;
    $pdf->SetTextColor(30, 30, 30);
    $pdf->SetFillColor(240, 245, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(130, 9, 'Total Paid (Semester)', 1, 0, 'R', true);
    $pdf->SetTextColor(37, 99, 235);
    $pdf->Cell(60, 9, 'Rs. ' . number_format($total_paid_sem, 2), 1, 1, 'R', true);
    
    $pdf->SetTextColor(30, 30, 30);
    $pdf->SetFillColor(255, 245, 245);
    $pdf->Cell(130, 9, 'Balance Due', 1, 0, 'R', true);
    if ($due > 0) {
        $pdf->SetTextColor(239, 68, 68);
    } else {
        $pdf->SetTextColor(16, 185, 129);
    }
    $pdf->Cell(60, 9, 'Rs. ' . number_format(max(0, $due), 2), 1, 1, 'R', true);
}

$pdf->Ln(4);

// Amount in words
$pdf->SetTextColor(30, 30, 30);
$pdf->SetFillColor(248, 250, 252);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 8, 'In Words:', 0, 0);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(80, 80, 80);
$amount_words = numberToWords((int)$data['amount']) . ' Rupees Only';
$pdf->Cell(160, 8, $amount_words, 0, 1);

// Remarks
if ($data['remarks']) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(30, 30, 30);
    $pdf->Cell(30, 8, 'Remarks:', 0, 0);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->Cell(160, 8, $data['remarks'], 0, 1);
}

$pdf->Ln(14);

// ======= Signature Section =======
$pdf->SetDrawColor(30, 30, 30);
$pdf->SetLineWidth(0.3);

// Student Signature
$pdf->SetX(15);
$pdf->Line(15, $pdf->GetY(), 65, $pdf->GetY());
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(55, 6, 'Student Signature', 0, 0, 'C');

// Cashier
$pdf->Cell(15, 6, '', 0, 0);
$pdf->Line(85, $pdf->GetY() - 6, 135, $pdf->GetY() - 6);
$pdf->Cell(55, 6, 'Cashier', 0, 0, 'C');

// Authorized Signature
$pdf->Cell(15, 6, '', 0, 0);
$pdf->Line(150, $pdf->GetY() - 6, 200, $pdf->GetY() - 6);
$pdf->Cell(55, 6, 'Authorized Signature', 0, 1, 'C');

// Stamp area
$pdf->Ln(3);
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(180, 180, 180);
$pdf->Cell(0, 5, '[Official Stamp]', 0, 1, 'C');

// Output
$pdf->Output('I', 'Receipt_' . $data['receipt_no'] . '.pdf');
?>
