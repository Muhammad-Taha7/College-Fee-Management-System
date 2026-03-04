<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Topbar Component -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
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
            <span class="admin-name"><?php echo isset($_SESSION['admin_user']) ? htmlspecialchars($_SESSION['admin_user']) : 'Admin'; ?></span>
        </div>
        <a href="javascript:void(0)" onclick="confirmLogout('logout.php')" class="logout-btn-topbar">
            <i class="fas fa-sign-out-alt"></i><span class="logout-text"> Logout</span>
        </a>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
    document.body.classList.toggle('sidebar-open');
}
// Close sidebar when clicking a link on mobile
document.querySelectorAll('.sidebar-menu a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
});
// Close sidebar on window resize to desktop
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
    }
});
</script>
