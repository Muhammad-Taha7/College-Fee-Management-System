<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Fee Structure";
$page_subtitle = "View fee structure for all degree programs";

$message = '';
$msg_type = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_fee'])) {
    $id = intval($_POST['fee_id']);
    $tuition = floatval($_POST['tuition_fee']);
    $exam = floatval($_POST['exam_fee']);
    $library = floatval($_POST['library_fee']);
    $lab = floatval($_POST['lab_fee']);
    $other = floatval($_POST['other_fee']);
    $total = $tuition + $exam + $library + $lab + $other;

    $stmt = $conn->prepare("UPDATE fee_structure SET tuition_fee=?, exam_fee=?, library_fee=?, lab_fee=?, other_fee=?, total_fee=? WHERE id=?");
    $stmt->bind_param("ddddddi", $tuition, $exam, $library, $lab, $other, $total, $id);
    if ($stmt->execute()) {
        $message = "Fee structure updated successfully!";
        $msg_type = "success";
    } else {
        $message = "Error updating fee structure.";
        $msg_type = "danger";
    }
    $stmt->close();
}

// Filters
$filter_course = isset($_GET['course']) ? sanitize($conn, $_GET['course']) : '';

$where = "WHERE 1=1";
if ($filter_course) $where .= " AND course = '$filter_course'";

$fees = $conn->query("SELECT * FROM fee_structure $where ORDER BY course, semester");
$courses = $conn->query("SELECT DISTINCT course FROM fee_structure ORDER BY course");

// For edit
$edit_fee = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM fee_structure WHERE id = $edit_id");
    if ($result->num_rows > 0) {
        $edit_fee = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Structure - College Fee Management</title>
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

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-list-alt"></i> Fee Structure - All Degree Programs</h3>
                </div>
                <div class="card-body">
                    <!-- Filter Bar -->
                    <form method="GET" class="filter-bar">
                        <select name="course" class="form-control" style="min-width:200px;">
                            <option value="">All Courses</option>
                            <?php while($c = $courses->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($c['course']); ?>" <?php echo $filter_course==$c['course']?'selected':''; ?>>
                                <?php echo htmlspecialchars($c['course']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        <a href="fee_structure.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
                    </form>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Course</th>
                                    <th>Semester</th>
                                    <th>Tuition Fee</th>
                                    <th>Exam Fee</th>
                                    <th>Library Fee</th>
                                    <th>Lab Fee</th>
                                    <th>Other Fee</th>
                                    <th>Total Fee</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($fees->num_rows > 0): $i=0; $prev_course=''; ?>
                                    <?php while($row = $fees->fetch_assoc()): $i++; ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['course']); ?></strong></td>
                                        <td><span class="badge badge-primary">Sem <?php echo $row['semester']; ?></span></td>
                                        <td>&#8377;<?php echo number_format($row['tuition_fee']); ?></td>
                                        <td>&#8377;<?php echo number_format($row['exam_fee']); ?></td>
                                        <td>&#8377;<?php echo number_format($row['library_fee']); ?></td>
                                        <td>&#8377;<?php echo number_format($row['lab_fee']); ?></td>
                                        <td>&#8377;<?php echo number_format($row['other_fee']); ?></td>
                                        <td><strong style="color:var(--success);">&#8377;<?php echo number_format($row['total_fee']); ?></strong></td>
                                        <td>
                                            <a href="?edit=<?php echo $row['id']; ?><?php echo $filter_course ? '&course='.$filter_course : ''; ?>" class="btn btn-info btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10">
                                            <div class="empty-state">
                                                <i class="fas fa-list-alt"></i>
                                                <h4>No Fee Structure Found</h4>
                                                <p>Run the setup to create fee structures.</p>
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

    <!-- Edit Modal -->
    <?php if($edit_fee): ?>
    <div class="modal-overlay active" id="editModal">
        <div class="modal-box" style="max-width:550px;">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i>&nbsp; Edit Fee - <?php echo $edit_fee['course']; ?> (Sem <?php echo $edit_fee['semester']; ?>)</h3>
                <a href="fee_structure.php<?php echo $filter_course ? '?course='.$filter_course : ''; ?>" class="close-btn">&times;</a>
            </div>
            <form method="POST" action="fee_structure.php<?php echo $filter_course ? '?course='.$filter_course : ''; ?>">
                <div class="modal-body">
                    <input type="hidden" name="fee_id" value="<?php echo $edit_fee['id']; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tuition Fee (&#8377;)</label>
                            <input type="number" name="tuition_fee" class="form-control" value="<?php echo $edit_fee['tuition_fee']; ?>" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Exam Fee (&#8377;)</label>
                            <input type="number" name="exam_fee" class="form-control" value="<?php echo $edit_fee['exam_fee']; ?>" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Library Fee (&#8377;)</label>
                            <input type="number" name="library_fee" class="form-control" value="<?php echo $edit_fee['library_fee']; ?>" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Lab Fee (&#8377;)</label>
                            <input type="number" name="lab_fee" class="form-control" value="<?php echo $edit_fee['lab_fee']; ?>" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Other Fee (&#8377;)</label>
                        <input type="number" name="other_fee" class="form-control" value="<?php echo $edit_fee['other_fee']; ?>" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="fee_structure.php<?php echo $filter_course ? '?course='.$filter_course : ''; ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" name="update_fee" class="btn btn-primary"><i class="fas fa-save"></i> Update Fee</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php include 'includes/confirm_popup.php'; ?>
</body>
</html>
