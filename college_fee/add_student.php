<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Add Student";
$page_subtitle = "Register a new student in the system";

$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = generateStudentId($conn);
    $name = sanitize($conn, $_POST['name']);
    $father_name = sanitize($conn, $_POST['father_name']);
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);
    $course = sanitize($conn, $_POST['course']);
    $semester = intval($_POST['semester']);
    $admission_date = sanitize($conn, $_POST['admission_date']);
    $address = sanitize($conn, $_POST['address']);

    if (empty($name) || empty($father_name) || empty($phone) || empty($course) || empty($admission_date)) {
        $message = "Please fill all required fields!";
        $msg_type = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (student_id, name, father_name, email, phone, course, semester, admission_date, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssisd", $student_id, $name, $father_name, $email, $phone, $course, $semester, $admission_date, $address);
        
        if ($stmt->execute()) {
            $message = "Student registered successfully! Student ID: <strong>$student_id</strong>";
            $msg_type = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $msg_type = "danger";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - College Fee Management</title>
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
                    <h3><i class="fas fa-user-plus"></i> Student Registration Form</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter student name" required>
                            </div>
                            <div class="form-group">
                                <label>Father's Name <span class="required">*</span></label>
                                <input type="text" name="father_name" class="form-control" placeholder="Enter father's name" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter email address">
                            </div>
                            <div class="form-group">
                                <label>Phone Number <span class="required">*</span></label>
                                <input type="text" name="phone" class="form-control" placeholder="Enter phone number" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Course <span class="required">*</span></label>
                                <select name="course" class="form-control" required>
                                    <option value="">-- Select Course --</option>
                                    <option value="BCA">BCA</option>
                                    <option value="MCA">MCA</option>
                                    <option value="BSc IT">BSc IT</option>
                                    <option value="MBA">MBA</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Semester <span class="required">*</span></label>
                                <select name="semester" class="form-control" required>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                    <option value="3">Semester 3</option>
                                    <option value="4">Semester 4</option>
                                    <option value="5">Semester 5</option>
                                    <option value="6">Semester 6</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Admission Date <span class="required">*</span></label>
                                <input type="date" name="admission_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control" placeholder="Enter address">
                            </div>
                        </div>

                        <div style="margin-top: 20px; display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Register Student
                            </button>
                            <button type="reset" class="btn btn-outline">
                                <i class="fas fa-redo"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
