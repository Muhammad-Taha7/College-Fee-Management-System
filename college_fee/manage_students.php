<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Manage Students";
$page_subtitle = "View, edit and manage all students";

$message = '';
$msg_type = '';

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = sanitize($conn, $_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $del_id);
    if ($stmt->execute()) {
        $message = "Student deleted successfully!";
        $msg_type = "success";
    } else {
        $message = "Error deleting student.";
        $msg_type = "danger";
    }
    $stmt->close();
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $tog_id = sanitize($conn, $_GET['toggle']);
    $conn->query("UPDATE students SET status = IF(status='active','inactive','active') WHERE student_id='$tog_id'");
    $message = "Student status updated!";
    $msg_type = "success";
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $sid = sanitize($conn, $_POST['student_id']);
    $name = sanitize($conn, $_POST['name']);
    $father_name = sanitize($conn, $_POST['father_name']);
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);
    $course = sanitize($conn, $_POST['course']);
    $semester = intval($_POST['semester']);
    $address = sanitize($conn, $_POST['address']);

    $stmt = $conn->prepare("UPDATE students SET name=?, father_name=?, email=?, phone=?, course=?, semester=?, address=? WHERE student_id=?");
    $stmt->bind_param("sssssiss", $name, $father_name, $email, $phone, $course, $semester, $address, $sid);
    if ($stmt->execute()) {
        $message = "Student updated successfully!";
        $msg_type = "success";
    } else {
        $message = "Error updating student.";
        $msg_type = "danger";
    }
    $stmt->close();
}

// Search & Filter
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$filter_course = isset($_GET['course']) ? sanitize($conn, $_GET['course']) : '';
$filter_status = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';

$where = "WHERE 1=1";
if ($search) $where .= " AND (name LIKE '%$search%' OR student_id LIKE '%$search%' OR phone LIKE '%$search%')";
if ($filter_course) $where .= " AND course = '$filter_course'";
if ($filter_status) $where .= " AND status = '$filter_status'";

$students = $conn->query("SELECT * FROM students $where ORDER BY created_at DESC");

// For edit modal
$edit_student = null;
if (isset($_GET['edit'])) {
    $edit_id = sanitize($conn, $_GET['edit']);
    $result = $conn->query("SELECT * FROM students WHERE student_id = '$edit_id'");
    if ($result->num_rows > 0) {
        $edit_student = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - College Fee Management</title>
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

            <div class="content-card animate-in">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> All Students</h3>
                    <a href="add_student.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter Bar -->
                    <form method="GET" class="filter-bar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search by name, ID or phone..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <select name="course">
                            <option value="">All Courses</option>
                            <option value="BCA" <?php echo $filter_course == 'BCA' ? 'selected' : ''; ?>>BCA</option>
                            <option value="MCA" <?php echo $filter_course == 'MCA' ? 'selected' : ''; ?>>MCA</option>
                            <option value="BSc IT" <?php echo $filter_course == 'BSc IT' ? 'selected' : ''; ?>>BSc IT</option>
                            <option value="MBA" <?php echo $filter_course == 'MBA' ? 'selected' : ''; ?>>MBA</option>
                        </select>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $filter_status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                        <a href="manage_students.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
                    </form>

                    <!-- Students Table -->
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Father's Name</th>
                                    <th>Phone</th>
                                    <th>Course</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($students->num_rows > 0): $i=0; ?>
                                    <?php while($row = $students->fetch_assoc()): $i++; ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><span class="badge badge-info"><?php echo $row['student_id']; ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['father_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                                        <td>Sem <?php echo $row['semester']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $row['student_id']; ?>" class="btn btn-info btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmToggle('?toggle=<?php echo $row['student_id']; ?>', '<?php echo htmlspecialchars(addslashes($row['name'])); ?>')" class="btn btn-warning btn-sm" title="Toggle Status">
                                                <i class="fas fa-sync"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="confirmDelete('?delete=<?php echo $row['student_id']; ?>', '<?php echo htmlspecialchars(addslashes($row['name'])); ?>')" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="fee_payment.php?sid=<?php echo $row['student_id']; ?>" class="btn btn-success btn-sm" title="Pay Fee">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <i class="fas fa-users"></i>
                                                <h4>No Students Found</h4>
                                                <p>Try adjusting your filters or add a new student.</p>
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
    <?php if($edit_student): ?>
    <div class="modal-overlay active" id="editModal">
        <div class="modal-box" style="max-width:650px;">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i>&nbsp; Edit Student - <?php echo $edit_student['student_id']; ?></h3>
                <a href="manage_students.php" class="close-btn">&times;</a>
            </div>
            <form method="POST" action="manage_students.php">
                <div class="modal-body">
                    <input type="hidden" name="student_id" value="<?php echo $edit_student['student_id']; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_student['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Father's Name</label>
                            <input type="text" name="father_name" class="form-control" value="<?php echo htmlspecialchars($edit_student['father_name']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_student['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($edit_student['phone']); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Course</label>
                            <select name="course" class="form-control" required>
                                <option value="BCA" <?php echo $edit_student['course']=='BCA'?'selected':''; ?>>BCA</option>
                                <option value="MCA" <?php echo $edit_student['course']=='MCA'?'selected':''; ?>>MCA</option>
                                <option value="BSc IT" <?php echo $edit_student['course']=='BSc IT'?'selected':''; ?>>BSc IT</option>
                                <option value="MBA" <?php echo $edit_student['course']=='MBA'?'selected':''; ?>>MBA</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Semester</label>
                            <select name="semester" class="form-control" required>
                                <?php for($s=1; $s<=6; $s++): ?>
                                <option value="<?php echo $s; ?>" <?php echo $edit_student['semester']==$s?'selected':''; ?>>Semester <?php echo $s; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?php echo htmlspecialchars($edit_student['address']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="manage_students.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Update Student</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php include 'includes/confirm_popup.php'; ?>
    <script>
    // Confirm before updating student
    const editForm = document.querySelector('#editModal form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            showConfirm({
                title: 'Do you want to update?',
                message: 'Are you sure you want to save changes to this student record?',
                icon: 'info',
                yesText: 'Yes, Update',
                btnColor: 'green',
                onConfirm: function() {
                    form.removeEventListener('submit', arguments.callee);
                    form.submit();
                }
            });
        });
    }
    </script>
</body>
</html>
