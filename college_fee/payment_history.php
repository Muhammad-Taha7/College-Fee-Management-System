<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Payment History";
$page_subtitle = "View all payment transactions";

// Filters
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$filter_method = isset($_GET['method']) ? sanitize($conn, $_GET['method']) : '';
$date_from = isset($_GET['date_from']) ? sanitize($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($conn, $_GET['date_to']) : '';

$where = "WHERE 1=1";
if ($search) $where .= " AND (s.name LIKE '%$search%' OR p.student_id LIKE '%$search%' OR p.receipt_no LIKE '%$search%')";
if ($filter_method) $where .= " AND p.payment_method = '$filter_method'";
if ($date_from) $where .= " AND p.payment_date >= '$date_from'";
if ($date_to) $where .= " AND p.payment_date <= '$date_to'";

$payments = $conn->query("
    SELECT p.*, s.name, s.course 
    FROM payments p 
    JOIN students s ON p.student_id = s.student_id 
    $where
    ORDER BY p.created_at DESC
");

// Total
$total_result = $conn->query("
    SELECT COALESCE(SUM(p.amount),0) as total 
    FROM payments p 
    JOIN students s ON p.student_id = s.student_id 
    $where
");
$total_filtered = $total_result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - College Fee Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="page-wrapper">
            <div class="content-card animate-in">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> All Payments</h3>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <span style="font-size:13px;color:var(--text-muted);">Total:</span>
                        <span style="font-size:16px;font-weight:700;color:var(--success);">₹<?php echo number_format($total_filtered); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Bar -->
                    <form method="GET" class="filter-bar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search by name, ID or receipt..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <select name="method">
                            <option value="">All Methods</option>
                            <option value="cash" <?php echo $filter_method=='cash'?'selected':''; ?>>Cash</option>
                            <option value="online" <?php echo $filter_method=='online'?'selected':''; ?>>Online</option>
                            <option value="cheque" <?php echo $filter_method=='cheque'?'selected':''; ?>>Cheque</option>
                        </select>
                        <input type="date" name="date_from" class="form-control" style="width:auto;" value="<?php echo $date_from; ?>" placeholder="From">
                        <input type="date" name="date_to" class="form-control" style="width:auto;" value="<?php echo $date_to; ?>" placeholder="To">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        <a href="payment_history.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
                    </form>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Receipt No</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Semester</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($payments->num_rows > 0): $i=0; ?>
                                    <?php while($row = $payments->fetch_assoc()): $i++; ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><span class="badge badge-primary"><?php echo $row['receipt_no']; ?></span></td>
                                        <td><?php echo $row['student_id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                                        <td>Sem <?php echo $row['semester']; ?></td>
                                        <td><strong style="color:var(--success);">₹<?php echo number_format($row['amount']); ?></strong></td>
                                        <td>
                                            <span class="badge <?php
                                                echo $row['payment_method']=='cash' ? 'badge-success' : 
                                                    ($row['payment_method']=='online' ? 'badge-info' : 'badge-warning'); 
                                            ?>">
                                                <?php echo ucfirst($row['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row['payment_date'])); ?></td>
                                        <td style="display:flex;gap:4px;">
                                            <a href="receipt.php?receipt=<?php echo $row['receipt_no']; ?>" class="btn btn-sm btn-primary" title="View Receipt">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="receipt_pdf.php?receipt=<?php echo $row['receipt_no']; ?>" target="_blank" class="btn btn-sm btn-success" title="PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10">
                                            <div class="empty-state">
                                                <i class="fas fa-receipt"></i>
                                                <h4>No Payments Found</h4>
                                                <p>Try adjusting your filters.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/confirm_popup.php'; ?>
</body>
</html>
