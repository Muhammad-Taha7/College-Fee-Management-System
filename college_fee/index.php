<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Dashboard";
$page_subtitle = "Overview of College Fee Management System";

// Fetch statistics
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$active_students = $conn->query("SELECT COUNT(*) as count FROM students WHERE status='active'")->fetch_assoc()['count'];
$total_collected = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM payments")->fetch_assoc()['total'];
$today_collected = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE payment_date = CURDATE()")->fetch_assoc()['total'];
$total_payments = $conn->query("SELECT COUNT(*) as count FROM payments")->fetch_assoc()['count'];
$courses_result = $conn->query("SELECT COUNT(DISTINCT course) as count FROM students");
$total_courses = $courses_result->fetch_assoc()['count'];

// Recent payments
$recent_payments = $conn->query("
    SELECT p.*, s.name, s.course 
    FROM payments p 
    JOIN students s ON p.student_id = s.student_id 
    ORDER BY p.created_at DESC LIMIT 5
");

// Recent students
$recent_students = $conn->query("
    SELECT * FROM students ORDER BY created_at DESC LIMIT 5
");

// Monthly collection data for chart
$monthly_data = $conn->query("
    SELECT MONTH(payment_date) as month, SUM(amount) as total 
    FROM payments 
    WHERE YEAR(payment_date) = YEAR(CURDATE())
    GROUP BY MONTH(payment_date) 
    ORDER BY month
");
$months = [];
$monthly_amounts = [];
$month_names = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
while ($row = $monthly_data->fetch_assoc()) {
    $months[] = $month_names[$row['month'] - 1];
    $monthly_amounts[] = $row['total'];
}

// Course-wise student count
$course_data = $conn->query("
    SELECT course, COUNT(*) as count FROM students GROUP BY course
");
$course_labels = [];
$course_counts = [];
while ($row = $course_data->fetch_assoc()) {
    $course_labels[] = $row['course'];
    $course_counts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - College Fee Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="page-wrapper">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card blue animate-in">
                    <div class="icon-box"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="stat-card green animate-in">
                    <div class="icon-box"><i class="fas fa-user-check"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $active_students; ?></h3>
                        <p>Active Students</p>
                    </div>
                </div>
                <div class="stat-card purple animate-in">
                    <div class="icon-box"><i class="fas fa-rupee-sign"></i></div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($total_collected); ?></h3>
                        <p>Total Fee Collected</p>
                    </div>
                </div>
                <div class="stat-card orange animate-in">
                    <div class="icon-box"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($today_collected); ?></h3>
                        <p>Today's Collection</p>
                    </div>
                </div>
                <div class="stat-card cyan animate-in">
                    <div class="icon-box"><i class="fas fa-receipt"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_payments; ?></h3>
                        <p>Total Transactions</p>
                    </div>
                </div>
                <div class="stat-card red animate-in">
                    <div class="icon-box"><i class="fas fa-graduation-cap"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_courses; ?></h3>
                        <p>Courses Available</p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-area"></i> Monthly Fee Collection (<?php echo date('Y'); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> Students by Course</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="courseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Data Tables -->
            <div class="charts-grid">
                <!-- Recent Payments -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> Recent Payments</h3>
                        <a href="payment_history.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Receipt</th>
                                        <th>Student</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($recent_payments->num_rows > 0): ?>
                                        <?php while($row = $recent_payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="badge badge-primary"><?php echo $row['receipt_no']; ?></span></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><strong>₹<?php echo number_format($row['amount']); ?></strong></td>
                                            <td><?php echo date('d M Y', strtotime($row['payment_date'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" style="text-align:center;padding:20px;">No payments yet</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Students -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-graduate"></i> Recent Students</h3>
                        <a href="manage_students.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Course</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($recent_students->num_rows > 0): ?>
                                        <?php while($row = $recent_students->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="badge badge-info"><?php echo $row['student_id']; ?></span></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['course']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $row['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" style="text-align:center;padding:20px;">No students yet</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Monthly Collection Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Fee Collected (₹)',
                    data: <?php echo json_encode($monthly_amounts); ?>,
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: function(value) { return '₹' + value.toLocaleString(); }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Course Distribution Chart
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        new Chart(courseCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($course_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($course_counts); ?>,
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'],
                    borderWidth: 3,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, font: { size: 12 } }
                    }
                },
                cutout: '60%'
            }
        });
    </script>
    <?php include 'includes/confirm_popup.php'; ?>
</body>
</html>
