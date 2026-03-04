<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$page_title = "Reports & Charts";
$page_subtitle = "Visual analytics and fee reports";

// Monthly collection (current year)
$monthly = $conn->query("
    SELECT MONTH(payment_date) as month, SUM(amount) as total, COUNT(*) as count
    FROM payments 
    WHERE YEAR(payment_date) = YEAR(CURDATE())
    GROUP BY MONTH(payment_date) 
    ORDER BY month
");
$month_labels = [];
$month_amounts = [];
$month_counts = [];
$month_names = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
while ($row = $monthly->fetch_assoc()) {
    $month_labels[] = $month_names[$row['month'] - 1];
    $month_amounts[] = (float)$row['total'];
    $month_counts[] = (int)$row['count'];
}

// Course-wise collection
$course_collection = $conn->query("
    SELECT s.course, COALESCE(SUM(p.amount),0) as total 
    FROM students s 
    LEFT JOIN payments p ON s.student_id = p.student_id 
    GROUP BY s.course
");
$cc_labels = [];
$cc_amounts = [];
while ($row = $course_collection->fetch_assoc()) {
    $cc_labels[] = $row['course'];
    $cc_amounts[] = (float)$row['total'];
}

// Payment method distribution
$methods = $conn->query("
    SELECT payment_method, COUNT(*) as count, SUM(amount) as total 
    FROM payments 
    GROUP BY payment_method
");
$method_labels = [];
$method_counts = [];
$method_amounts = [];
while ($row = $methods->fetch_assoc()) {
    $method_labels[] = ucfirst($row['payment_method']);
    $method_counts[] = (int)$row['count'];
    $method_amounts[] = (float)$row['total'];
}

// Course-wise student count
$course_students = $conn->query("SELECT course, COUNT(*) as count FROM students GROUP BY course");
$cs_labels = [];
$cs_counts = [];
while ($row = $course_students->fetch_assoc()) {
    $cs_labels[] = $row['course'];
    $cs_counts[] = (int)$row['count'];
}

// Semester wise fee collection
$sem_data = $conn->query("
    SELECT semester, SUM(amount) as total, COUNT(*) as count 
    FROM payments 
    GROUP BY semester 
    ORDER BY semester
");
$sem_labels = [];
$sem_amounts = [];
while ($row = $sem_data->fetch_assoc()) {
    $sem_labels[] = 'Sem ' . $row['semester'];
    $sem_amounts[] = (float)$row['total'];
}

// Summary stats
$total_collected = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM payments")->fetch_assoc()['t'];
$avg_payment = $conn->query("SELECT COALESCE(AVG(amount),0) as t FROM payments")->fetch_assoc()['t'];
$max_payment = $conn->query("SELECT COALESCE(MAX(amount),0) as t FROM payments")->fetch_assoc()['t'];
$this_month = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM payments WHERE MONTH(payment_date)=MONTH(CURDATE()) AND YEAR(payment_date)=YEAR(CURDATE())")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - College Fee Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="page-wrapper">
            <!-- Summary Cards -->
            <div class="stats-grid">
                <div class="stat-card green animate-in">
                    <div class="icon-box"><i class="fas fa-rupee-sign"></i></div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($total_collected); ?></h3>
                        <p>Total Collection</p>
                    </div>
                </div>
                <div class="stat-card blue animate-in">
                    <div class="icon-box"><i class="fas fa-calculator"></i></div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($avg_payment); ?></h3>
                        <p>Average Payment</p>
                    </div>
                </div>
                <div class="stat-card purple animate-in">
                    <div class="icon-box"><i class="fas fa-arrow-up"></i></div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($max_payment); ?></h3>
                        <p>Highest Payment</p>
                    </div>
                </div>
                <div class="stat-card orange animate-in">
                    <div class="icon-box"><i class="fas fa-calendar"></i></div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($this_month); ?></h3>
                        <p>This Month</p>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="charts-grid">
                <div class="content-card animate-in">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Monthly Fee Collection (<?php echo date('Y'); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyLineChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="content-card animate-in">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Course-wise Collection</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="courseBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="charts-grid">
                <div class="content-card animate-in">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> Payment Method Distribution</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="methodPieChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="content-card animate-in">
                    <div class="card-header">
                        <h3><i class="fas fa-users"></i> Students per Course</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="studentsDoughnut"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 3 -->
            <div class="charts-grid">
                <div class="content-card animate-in">
                    <div class="card-header">
                        <h3><i class="fas fa-layer-group"></i> Semester-wise Collection</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="semBarChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="content-card animate-in">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-area"></i> Monthly Transactions Count</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="txnAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const colors = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];
    const bgColors = ['rgba(37,99,235,0.7)','rgba(16,185,129,0.7)','rgba(245,158,11,0.7)','rgba(239,68,68,0.7)','rgba(139,92,246,0.7)','rgba(236,72,153,0.7)','rgba(6,182,212,0.7)'];
    
    // Monthly Line Chart
    new Chart(document.getElementById('monthlyLineChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($month_labels); ?>,
            datasets: [{
                label: 'Collection (₹)',
                data: <?php echo json_encode($month_amounts); ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#2563eb',
                pointBorderWidth: 3,
                pointRadius: 5,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false },
                tooltip: { callbacks: { label: ctx => '₹' + ctx.parsed.y.toLocaleString() }}
            },
            scales: {
                y: { beginAtZero: true, grid: {color:'rgba(0,0,0,0.05)'}, ticks: { callback: v => '₹'+v.toLocaleString() }},
                x: { grid: {display: false} }
            }
        }
    });

    // Course Bar Chart
    new Chart(document.getElementById('courseBarChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($cc_labels); ?>,
            datasets: [{
                label: 'Collection (₹)',
                data: <?php echo json_encode($cc_amounts); ?>,
                backgroundColor: bgColors,
                borderColor: colors,
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false },
                tooltip: { callbacks: { label: ctx => '₹' + ctx.parsed.y.toLocaleString() }}
            },
            scales: {
                y: { beginAtZero: true, grid: {color:'rgba(0,0,0,0.05)'}, ticks: { callback: v => '₹'+v.toLocaleString() }},
                x: { grid: {display: false} }
            }
        }
    });

    // Payment Method Pie
    new Chart(document.getElementById('methodPieChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($method_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($method_amounts); ?>,
                backgroundColor: ['#10b981','#2563eb','#f59e0b'],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 }}},
                tooltip: { callbacks: { label: ctx => ctx.label + ': ₹' + ctx.parsed.toLocaleString() }}
            }
        }
    });

    // Students Doughnut
    new Chart(document.getElementById('studentsDoughnut'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($cs_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($cs_counts); ?>,
                backgroundColor: bgColors,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 }}}
            }
        }
    });

    // Semester Bar Chart
    new Chart(document.getElementById('semBarChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($sem_labels); ?>,
            datasets: [{
                label: 'Collection (₹)',
                data: <?php echo json_encode($sem_amounts); ?>,
                backgroundColor: 'rgba(139,92,246,0.7)',
                borderColor: '#8b5cf6',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false },
                tooltip: { callbacks: { label: ctx => '₹' + ctx.parsed.y.toLocaleString() }}
            },
            scales: {
                y: { beginAtZero: true, grid: {color:'rgba(0,0,0,0.05)'}, ticks: { callback: v => '₹'+v.toLocaleString() }},
                x: { grid: {display: false} }
            }
        }
    });

    // Transactions Area Chart
    new Chart(document.getElementById('txnAreaChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($month_labels); ?>,
            datasets: [{
                label: 'Transactions',
                data: <?php echo json_encode($month_counts); ?>,
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6,182,212,0.15)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#06b6d4',
                pointBorderWidth: 3,
                pointRadius: 5,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }},
            scales: {
                y: { beginAtZero: true, grid: {color:'rgba(0,0,0,0.05)'}, ticks: { stepSize: 1 }},
                x: { grid: {display: false} }
            }
        }
    });
    </script>
</body>
</html>
