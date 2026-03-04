<!-- Custom Confirm Popup -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon" id="confirmIcon">
            <i class="fas fa-question-circle"></i>
        </div>
        <h3 class="confirm-title" id="confirmTitle">Do you want to proceed?</h3>
        <p class="confirm-message" id="confirmMessage">Are you sure you want to perform this action?</p>
        <div class="confirm-buttons">
            <button class="confirm-btn confirm-cancel" id="confirmCancel" onclick="closeConfirm()">
                <i class="fas fa-times"></i> No, Cancel
            </button>
            <button class="confirm-btn confirm-yes" id="confirmYes">
                <i class="fas fa-check"></i> Yes, Continue
            </button>
        </div>
    </div>
</div>

<style>
.confirm-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    animation: confirmFadeIn 0.2s ease;
}
.confirm-overlay.active {
    display: flex;
}
@keyframes confirmFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes confirmSlideIn {
    from { opacity: 0; transform: scale(0.85) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.confirm-box {
    background: #fff;
    border-radius: 16px;
    padding: 32px 28px 24px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: confirmSlideIn 0.3s ease forwards;
}
.confirm-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 30px;
}
.confirm-icon.icon-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
.confirm-icon.icon-warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}
.confirm-icon.icon-info {
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
}
.confirm-icon.icon-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}
.confirm-icon.icon-logout {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
.confirm-title {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
    font-family: 'Poppins', sans-serif;
}
.confirm-message {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 24px;
    line-height: 1.5;
    font-family: 'Poppins', sans-serif;
}
.confirm-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
}
.confirm-btn {
    padding: 10px 24px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
}
.confirm-cancel {
    background: #f1f5f9;
    color: #64748b;
}
.confirm-cancel:hover {
    background: #e2e8f0;
    color: #475569;
}
.confirm-yes {
    background: #2563eb;
    color: #fff;
}
.confirm-yes:hover {
    background: #1d4ed8;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}
.confirm-yes.btn-red {
    background: #ef4444;
}
.confirm-yes.btn-red:hover {
    background: #dc2626;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}
.confirm-yes.btn-green {
    background: #10b981;
}
.confirm-yes.btn-green:hover {
    background: #059669;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}
.confirm-yes.btn-orange {
    background: #f59e0b;
}
.confirm-yes.btn-orange:hover {
    background: #d97706;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

@media (max-width: 480px) {
    .confirm-box {
        padding: 24px 20px 20px;
    }
    .confirm-title {
        font-size: 16px;
    }
    .confirm-message {
        font-size: 13px;
    }
    .confirm-buttons {
        flex-direction: column;
    }
    .confirm-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
let confirmCallback = null;

function showConfirm(options = {}) {
    const overlay = document.getElementById('confirmOverlay');
    const icon = document.getElementById('confirmIcon');
    const title = document.getElementById('confirmTitle');
    const message = document.getElementById('confirmMessage');
    const yesBtn = document.getElementById('confirmYes');

    // Set content
    title.textContent = options.title || 'Do you want to proceed?';
    message.textContent = options.message || 'Are you sure you want to perform this action?';

    // Set icon
    const iconType = options.icon || 'info';
    icon.className = 'confirm-icon icon-' + iconType;
    const icons = {
        danger: 'fa-exclamation-triangle',
        warning: 'fa-exclamation-circle',
        info: 'fa-question-circle',
        success: 'fa-check-circle',
        logout: 'fa-sign-out-alt'
    };
    icon.innerHTML = '<i class="fas ' + (icons[iconType] || 'fa-question-circle') + '"></i>';

    // Set button color
    yesBtn.className = 'confirm-btn confirm-yes';
    if (options.btnColor) yesBtn.classList.add('btn-' + options.btnColor);
    yesBtn.innerHTML = '<i class="fas fa-check"></i> ' + (options.yesText || 'Yes, Continue');

    // Set cancel text
    document.getElementById('confirmCancel').innerHTML = '<i class="fas fa-times"></i> ' + (options.noText || 'No, Cancel');

    // Set callback
    confirmCallback = options.onConfirm || null;

    // Show
    overlay.classList.add('active');

    // Yes button handler
    yesBtn.onclick = function() {
        overlay.classList.remove('active');
        if (confirmCallback) confirmCallback();
        confirmCallback = null;
    };
}

function closeConfirm() {
    document.getElementById('confirmOverlay').classList.remove('active');
    confirmCallback = null;
}

// Close on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeConfirm();
});

// Close on overlay click
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});

// ===== Helper functions for common actions =====

function confirmDelete(url, name) {
    showConfirm({
        title: 'Do you want to delete?',
        message: 'Are you sure you want to delete ' + (name || 'this record') + '? This action cannot be undone.',
        icon: 'danger',
        btnColor: 'red',
        yesText: 'Yes, Delete',
        noText: 'No, Keep it',
        onConfirm: function() { window.location.href = url; }
    });
}

function confirmToggle(url, name) {
    showConfirm({
        title: 'Do you want to change status?',
        message: 'Are you sure you want to toggle the status of ' + (name || 'this student') + '?',
        icon: 'warning',
        btnColor: 'orange',
        yesText: 'Yes, Change Status',
        onConfirm: function() { window.location.href = url; }
    });
}

function confirmLogout(url) {
    showConfirm({
        title: 'Do you want to logout?',
        message: 'You will be logged out from the system and redirected to the login page.',
        icon: 'logout',
        btnColor: 'red',
        yesText: 'Yes, Logout',
        noText: 'No, Stay',
        onConfirm: function() { window.location.href = url; }
    });
}

function confirmFormSubmit(formElement, options) {
    showConfirm({
        title: options.title || 'Do you want to submit?',
        message: options.message || 'Are you sure you want to submit this form?',
        icon: options.icon || 'info',
        btnColor: options.btnColor || '',
        yesText: options.yesText || 'Yes, Submit',
        onConfirm: function() {
            // temporarily remove onsubmit to avoid re-trigger
            formElement.removeAttribute('onsubmit');
            formElement.submit();
        }
    });
}
</script>
