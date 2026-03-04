<!-- Sidebar Component -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-close-btn" onclick="toggleSidebar()" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>
        <span class="logo-icon">
            <img src="https://static.vecteezy.com/system/resources/previews/014/909/769/non_2x/abstract-initial-letter-t-or-tt-logo-in-blue-color-isolated-in-white-background-applied-for-software-engineering-logo-also-suitable-for-the-brands-or-companies-have-initial-name-tt-or-t-vector.jpg" 
                 alt="College Logo" class="sidebar-logo">
        </span>
        <h2>College Fee System</h2>
        <small>Management Portal</small>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-label">Main</li>
        <li>
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>

        <li class="menu-label">Students</li>
        <li>
            <a href="add_student.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_student.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-plus"></i> Add Student
            </a>
        </li>
        <li>
            <a href="manage_students.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_students.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Manage Students
            </a>
        </li>

        <li class="menu-label">Fees</li>
        <li>
            <a href="fee_payment.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'fee_payment.php' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i> Fee Payment
            </a>
        </li>
        <li>
            <a href="fee_structure.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'fee_structure.php' ? 'active' : ''; ?>">
                <i class="fas fa-list-alt"></i> Fee Structure
            </a>
        </li>
        <li>
            <a href="payment_history.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payment_history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> Payment History
            </a>
        </li>

        <li class="menu-label">Reports</li>
        <li>
            <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Graph / Charts
            </a>
        </li>
        <li>
            <a href="receipt.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'receipt.php' ? 'active' : ''; ?>">
                <i class="fas fa-receipt"></i> Print Receipt
            </a>
        </li>

        <li class="menu-label">Account</li>
        <li>
            <a href="javascript:void(0)" style="color:#ef4444;" onclick="confirmLogout('logout.php')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>