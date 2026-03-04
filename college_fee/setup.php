<?php
// Database Setup Script - Run once to create database and tables
$host = 'localhost';
$user = 'root';
$pass = '';

$setup_done = false;
$setup_log = [];
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_setup'])) {
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        $error_msg = "Connection failed: " . $conn->connect_error;
    } else {
        // Create database
        $conn->query("CREATE DATABASE IF NOT EXISTS college_fee_db");
        $setup_log[] = "Database 'college_fee_db' created";
        $conn->select_db('college_fee_db');

        // Drop existing tables (fresh setup)
        $conn->query("DROP TABLE IF EXISTS payments");
        $conn->query("DROP TABLE IF EXISTS fee_structure");
        $conn->query("DROP TABLE IF EXISTS students");
        $setup_log[] = "Old tables dropped";

        // Students table
        $conn->query("
            CREATE TABLE students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id VARCHAR(20) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                father_name VARCHAR(100) NOT NULL,
                email VARCHAR(100),
                phone VARCHAR(20) NOT NULL,
                course VARCHAR(100) NOT NULL,
                semester INT NOT NULL DEFAULT 1,
                admission_date DATE NOT NULL,
                address TEXT,
                status ENUM('active','inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        $setup_log[] = "Table 'students' created";

        // Fee structure table
        $conn->query("
            CREATE TABLE fee_structure (
                id INT AUTO_INCREMENT PRIMARY KEY,
                course VARCHAR(100) NOT NULL,
                semester INT NOT NULL,
                tuition_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
                exam_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
                library_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
                lab_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
                other_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
                total_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $setup_log[] = "Table 'fee_structure' created";

        // Payments table
        $conn->query("
            CREATE TABLE payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id VARCHAR(20) NOT NULL,
                receipt_no VARCHAR(30) UNIQUE NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                payment_date DATE NOT NULL,
                payment_method ENUM('cash','online','cheque') DEFAULT 'cash',
                semester INT NOT NULL,
                remarks TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
            )
        ");
        $setup_log[] = "Table 'payments' created";

        // Insert fee structures for 12 degree programs
        $fees = [
            // BCA - 6 semesters
            ['BCA',1,15000,2000,1000,2000,500,20500],
            ['BCA',2,15000,2000,1000,2000,500,20500],
            ['BCA',3,16000,2000,1000,2500,500,22000],
            ['BCA',4,16000,2000,1000,2500,500,22000],
            ['BCA',5,17000,2500,1000,3000,500,24000],
            ['BCA',6,17000,2500,1000,3000,500,24000],
            // MCA - 4 semesters
            ['MCA',1,25000,3000,1500,3000,1000,33500],
            ['MCA',2,25000,3000,1500,3000,1000,33500],
            ['MCA',3,26000,3000,1500,3500,1000,35000],
            ['MCA',4,26000,3000,1500,3500,1000,35000],
            // BSc IT - 6 semesters
            ['BSc IT',1,12000,1500,800,1500,500,16300],
            ['BSc IT',2,12000,1500,800,1500,500,16300],
            ['BSc IT',3,13000,1500,800,2000,500,17800],
            ['BSc IT',4,13000,1500,800,2000,500,17800],
            ['BSc IT',5,14000,2000,1000,2000,500,19500],
            ['BSc IT',6,14000,2000,1000,2000,500,19500],
            // MBA - 4 semesters
            ['MBA',1,30000,3000,1500,1000,1500,37000],
            ['MBA',2,30000,3000,1500,1000,1500,37000],
            ['MBA',3,32000,3500,1500,1000,1500,39500],
            ['MBA',4,32000,3500,1500,1000,1500,39500],
            // B.Tech CS - 8 semesters
            ['B.Tech CS',1,35000,3000,1500,4000,1000,44500],
            ['B.Tech CS',2,35000,3000,1500,4000,1000,44500],
            ['B.Tech CS',3,36000,3000,1500,4500,1000,46000],
            ['B.Tech CS',4,36000,3000,1500,4500,1000,46000],
            ['B.Tech CS',5,37000,3500,1500,5000,1000,48000],
            ['B.Tech CS',6,37000,3500,1500,5000,1000,48000],
            ['B.Tech CS',7,38000,3500,2000,5000,1500,50000],
            ['B.Tech CS',8,38000,3500,2000,5000,1500,50000],
            // BBA - 6 semesters
            ['BBA',1,14000,1500,800,500,500,17300],
            ['BBA',2,14000,1500,800,500,500,17300],
            ['BBA',3,15000,1500,800,500,500,18300],
            ['BBA',4,15000,1500,800,500,500,18300],
            ['BBA',5,16000,2000,1000,500,500,20000],
            ['BBA',6,16000,2000,1000,500,500,20000],
            // B.Com - 6 semesters
            ['B.Com',1,10000,1500,800,500,500,13300],
            ['B.Com',2,10000,1500,800,500,500,13300],
            ['B.Com',3,11000,1500,800,500,500,14300],
            ['B.Com',4,11000,1500,800,500,500,14300],
            ['B.Com',5,12000,2000,1000,500,500,16000],
            ['B.Com',6,12000,2000,1000,500,500,16000],
            // BA English - 6 semesters
            ['BA English',1,8000,1200,800,0,500,10500],
            ['BA English',2,8000,1200,800,0,500,10500],
            ['BA English',3,9000,1200,800,0,500,11500],
            ['BA English',4,9000,1200,800,0,500,11500],
            ['BA English',5,10000,1500,1000,0,500,13000],
            ['BA English',6,10000,1500,1000,0,500,13000],
            // BSc Physics - 6 semesters
            ['BSc Physics',1,12000,1500,800,2000,500,16800],
            ['BSc Physics',2,12000,1500,800,2000,500,16800],
            ['BSc Physics',3,13000,1500,800,2500,500,18300],
            ['BSc Physics',4,13000,1500,800,2500,500,18300],
            ['BSc Physics',5,14000,2000,1000,3000,500,20500],
            ['BSc Physics',6,14000,2000,1000,3000,500,20500],
            // M.Tech CS - 4 semesters
            ['M.Tech CS',1,40000,3500,2000,5000,1500,52000],
            ['M.Tech CS',2,40000,3500,2000,5000,1500,52000],
            ['M.Tech CS',3,42000,3500,2000,5500,1500,54500],
            ['M.Tech CS',4,42000,3500,2000,5500,1500,54500],
            // MSc IT - 4 semesters
            ['MSc IT',1,20000,2500,1200,2500,800,27000],
            ['MSc IT',2,20000,2500,1200,2500,800,27000],
            ['MSc IT',3,22000,2500,1200,3000,800,29500],
            ['MSc IT',4,22000,2500,1200,3000,800,29500],
            // B.Pharm - 8 semesters
            ['B.Pharm',1,28000,2500,1000,4000,1000,36500],
            ['B.Pharm',2,28000,2500,1000,4000,1000,36500],
            ['B.Pharm',3,30000,2500,1000,4500,1000,39000],
            ['B.Pharm',4,30000,2500,1000,4500,1000,39000],
            ['B.Pharm',5,32000,3000,1200,5000,1000,42200],
            ['B.Pharm',6,32000,3000,1200,5000,1000,42200],
            ['B.Pharm',7,33000,3000,1200,5000,1500,43700],
            ['B.Pharm',8,33000,3000,1200,5000,1500,43700],
        ];

        $stmt = $conn->prepare("INSERT INTO fee_structure (course,semester,tuition_fee,exam_fee,library_fee,lab_fee,other_fee,total_fee) VALUES (?,?,?,?,?,?,?,?)");
        foreach ($fees as $f) {
            $stmt->bind_param("sidddddd", $f[0],$f[1],$f[2],$f[3],$f[4],$f[5],$f[6],$f[7]);
            $stmt->execute();
        }
        $setup_log[] = "Fee structures inserted (" . count($fees) . " records for 12 degree programs)";

        // Insert sample students across different courses
        $students = [
            ['STU2026001','Rahul Sharma','Rajesh Sharma','rahul@email.com','9876543210','BCA',3,'2025-07-01','Delhi, India'],
            ['STU2026002','Priya Verma','Suresh Verma','priya@email.com','9876543211','MCA',1,'2026-01-15','Mumbai, India'],
            ['STU2026003','Amit Kumar','Ramesh Kumar','amit@email.com','9876543212','BSc IT',2,'2025-07-10','Pune, India'],
            ['STU2026004','Sneha Patel','Dinesh Patel','sneha@email.com','9876543213','MBA',1,'2026-01-20','Ahmedabad, India'],
            ['STU2026005','Vikram Singh','Harpal Singh','vikram@email.com','9876543214','BCA',5,'2024-07-05','Jaipur, India'],
            ['STU2026006','Ananya Gupta','Rakesh Gupta','ananya@email.com','9876543215','B.Tech CS',3,'2025-07-15','Lucknow, India'],
            ['STU2026007','Rohit Jain','Manoj Jain','rohit@email.com','9876543216','BBA',2,'2025-08-01','Kolkata, India'],
            ['STU2026008','Meera Nair','Sunil Nair','meera@email.com','9876543217','B.Com',4,'2024-07-20','Chennai, India'],
            ['STU2026009','Karan Malhotra','Vijay Malhotra','karan@email.com','9876543218','BA English',1,'2026-01-10','Chandigarh, India'],
            ['STU2026010','Pooja Reddy','Srinivas Reddy','pooja@email.com','9876543219','BSc Physics',3,'2025-07-05','Hyderabad, India'],
            ['STU2026011','Arjun Mehta','Deepak Mehta','arjun@email.com','9876543220','M.Tech CS',1,'2026-01-18','Bangalore, India'],
            ['STU2026012','Nisha Yadav','Ramesh Yadav','nisha@email.com','9876543221','B.Pharm',4,'2024-08-10','Patna, India'],
        ];

        $stmt = $conn->prepare("INSERT INTO students (student_id,name,father_name,email,phone,course,semester,admission_date,address) VALUES (?,?,?,?,?,?,?,?,?)");
        foreach ($students as $s) {
            $stmt->bind_param("ssssssiss", $s[0],$s[1],$s[2],$s[3],$s[4],$s[5],$s[6],$s[7],$s[8]);
            $stmt->execute();
        }
        $setup_log[] = "Sample students inserted (" . count($students) . " records)";

        // Insert sample payments
        $payments = [
            ['STU2026001','RCP2026001',22000,'2026-01-15','cash',3,'Semester 3 full payment'],
            ['STU2026002','RCP2026002',33500,'2026-01-20','online',1,'Semester 1 full payment'],
            ['STU2026003','RCP2026003',10000,'2026-02-01','cash',2,'Partial payment'],
            ['STU2026004','RCP2026004',37000,'2026-02-10','cheque',1,'Full payment'],
            ['STU2026005','RCP2026005',24000,'2026-02-15','online',5,'Full payment'],
            ['STU2026001','RCP2026006',20500,'2025-07-20','cash',1,'Semester 1 full payment'],
            ['STU2026001','RCP2026007',20500,'2025-12-10','online',2,'Semester 2 full payment'],
            ['STU2026003','RCP2026008',6300,'2026-02-20','cash',2,'Remaining payment'],
            ['STU2026006','RCP2026009',46000,'2026-02-05','online',3,'Semester 3 full payment'],
            ['STU2026007','RCP2026010',17300,'2026-02-12','cash',2,'Semester 2 full payment'],
            ['STU2026008','RCP2026011',14300,'2026-01-25','cheque',4,'Semester 4 full payment'],
            ['STU2026010','RCP2026012',18300,'2026-02-18','online',3,'Semester 3 full payment'],
            ['STU2026012','RCP2026013',39000,'2026-01-30','cash',4,'Semester 4 full payment'],
        ];

        $stmt = $conn->prepare("INSERT INTO payments (student_id,receipt_no,amount,payment_date,payment_method,semester,remarks) VALUES (?,?,?,?,?,?,?)");
        foreach ($payments as $p) {
            $stmt->bind_param("ssdssis", $p[0],$p[1],$p[2],$p[3],$p[4],$p[5],$p[6]);
            $stmt->execute();
        }
        $setup_log[] = "Sample payments inserted (" . count($payments) . " records)";

        $setup_done = true;
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - College Fee Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .setup-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); max-width: 600px; width: 100%; overflow: hidden; }
        .setup-header { background: #1e293b; color: #fff; padding: 32px; text-align: center; }
        .setup-header i { font-size: 40px; margin-bottom: 12px; color: #3b82f6; }
        .setup-header h1 { font-size: 22px; font-weight: 600; }
        .setup-header p { font-size: 13px; opacity: 0.7; margin-top: 4px; }
        .setup-body { padding: 32px; }
        .warning-box { background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: 16px; margin-bottom: 24px; font-size: 13px; color: #92400e; display: flex; gap: 10px; align-items: flex-start; }
        .warning-box i { font-size: 18px; flex-shrink: 0; margin-top: 2px; }
        .info-list { list-style: none; margin-bottom: 24px; }
        .info-list li { padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 13px; display: flex; align-items: center; gap: 10px; }
        .info-list li:last-child { border-bottom: none; }
        .info-list li i { color: #2563eb; width: 20px; text-align: center; }
        .btn-setup { width: 100%; padding: 14px; background: #2563eb; color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.3s; }
        .btn-setup:hover { background: #1d4ed8; }
        .btn-setup.danger { background: #ef4444; }
        .btn-setup.danger:hover { background: #dc2626; }
        .log-list { list-style: none; margin-bottom: 24px; }
        .log-list li { padding: 8px 12px; font-size: 13px; display: flex; align-items: center; gap: 8px; background: #f0fdf4; border-radius: 6px; margin-bottom: 6px; color: #166534; }
        .log-list li i { color: #16a34a; }
        .success-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 24px; }
        .success-box i { font-size: 40px; color: #16a34a; margin-bottom: 8px; }
        .success-box h3 { font-size: 18px; color: #166534; margin-bottom: 4px; }
        .success-box p { font-size: 13px; color: #15803d; }
        .error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 16px; margin-bottom: 24px; color: #991b1b; font-size: 13px; display: flex; gap: 10px; align-items: center; }
        .btn-go { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: #2563eb; color: #fff; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 500; transition: background 0.3s; }
        .btn-go:hover { background: #1d4ed8; }
        @media (max-width: 480px) { .setup-header { padding: 24px 16px; } .setup-body { padding: 20px 16px; } .setup-header h1 { font-size: 18px; } }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="setup-header">
            <i class="fas fa-database"></i>
            <h1>Database Setup</h1>
            <p>College Fee Management System</p>
        </div>
        <div class="setup-body">
            <?php if($error_msg): ?>
                <div class="error-box"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <?php if($setup_done): ?>
                <div class="success-box">
                    <i class="fas fa-check-circle"></i>
                    <h3>Setup Complete!</h3>
                    <p>Database has been created with 12 degree programs and sample data.</p>
                </div>
                <ul class="log-list">
                    <?php foreach($setup_log as $log): ?>
                    <li><i class="fas fa-check"></i> <?php echo $log; ?></li>
                    <?php endforeach; ?>
                </ul>
                <div style="text-align:center;">
                    <a href="login.php" class="btn-go"><i class="fas fa-arrow-right"></i> Go to Login</a>
                </div>
            <?php else: ?>
                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div><strong>Warning:</strong> This will DROP all existing tables and recreate them with fresh data. All existing records will be lost.</div>
                </div>
                <ul class="info-list">
                    <li><i class="fas fa-server"></i> Creates database <strong>college_fee_db</strong></li>
                    <li><i class="fas fa-table"></i> Creates 3 tables: students, fee_structure, payments</li>
                    <li><i class="fas fa-graduation-cap"></i> Inserts fee structures for <strong>12 degree programs</strong></li>
                    <li><i class="fas fa-users"></i> Adds 12 sample students</li>
                    <li><i class="fas fa-receipt"></i> Adds 13 sample payment records</li>
                </ul>
                <form method="POST">
                    <button type="submit" name="run_setup" class="btn-setup danger">
                        <i class="fas fa-play-circle"></i> Run Database Setup
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
