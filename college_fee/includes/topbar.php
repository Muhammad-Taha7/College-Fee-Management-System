<!-- Topbar Component -->
<div class="topbar">
    <div>
        <div class="page-title">
            <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
            <small><?php echo isset($page_subtitle) ? $page_subtitle : 'Welcome to College Fee Management System'; ?></small>
        </div>
    </div>
    <div class="topbar-right">
        <div class="date-display">
            <i class="fas fa-calendar-alt"></i>&nbsp;
            <?php echo date('D, d M Y'); ?>
        </div>
        <div class="admin-badge">
            <div class="avatar"><i class="fas fa-user"></i></div>
            <?php echo isset($_SESSION['admin_user']) ? htmlspecialchars($_SESSION['admin_user']) : 'Admin'; ?>
        </div>
        <a href="logout.php" onclick="return confirm('Logout?');" style="background:#ef4444;color:#fff;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.3s;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
