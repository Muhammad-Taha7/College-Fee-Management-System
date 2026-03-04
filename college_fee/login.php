<?php
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

// Admin Credentials
$ADMIN_USER = "User";
$ADMIN_PASS = "12345678";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - College Fee Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            justify-content: center;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e3a5f 100%);
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: rgba(37, 99, 235, 0.12);
            border-radius: 50%;
            top: -150px; right: -150px;
        }
        body::after {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            background: rgba(14, 165, 233, 0.08);
            border-radius: 50%;
            bottom: -100px; left: -100px;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        /* ===== SPLIT LAYOUT ===== */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 100%;
            padding: 0;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            border-radius: 0;
            box-shadow: none;
            overflow: hidden;
            display: flex;
            min-height: 100vh;
            animation: slideUp 0.5s ease forwards;
        }

        /* ===== LEFT SIDE - INFO PANEL ===== */
        .login-left {
            flex: 1.1;
            background: linear-gradient(160deg, #1e3a5f 0%, #1d4ed8 40%, #2563eb 100%);
            padding: 64px 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            width: 250px; height: 250px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            top: -60px; right: -60px;
        }
        .login-left::after {
            content: '';
            position: absolute;
            width: 180px; height: 180px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
            bottom: -40px; left: -40px;
        }
        .left-content {
            position: relative;
            z-index: 2;
        }
        .left-logo {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
            margin-bottom: 24px;
            border: 2px solid rgba(255,255,255,0.2);
        }
        .left-content h1 {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 8px;
        }
        .left-content .tagline {
            font-size: 14px;
            opacity: 0.85;
            font-weight: 300;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            font-size: 14px;
            font-weight: 400;
            opacity: 0.95;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list li .f-icon {
            width: 38px; height: 38px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .feature-list li span {
            line-height: 1.4;
        }
        .feature-list li small {
            display: block;
            font-size: 11px;
            opacity: 0.7;
            font-weight: 300;
            margin-top: 2px;
        }
        .left-footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            opacity: 0.7;
        }
        .left-footer i { font-size: 16px; }

        /* ===== RIGHT SIDE - FORM ===== */
        .login-right {
            flex: 0.9;
            padding: 64px 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-width: 500px;
            margin: 0 auto;
        }
        .form-header {
            margin-bottom: 32px;
            text-align: center;
        }
        .form-header .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(37,99,235,0.1);
            color: #2563eb;
            padding: 8px 16px;
            border-radius: 24px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
            border: 1px solid rgba(37,99,235,0.2);
        }
        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .form-header p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
        }
        .form-group {
            margin-bottom: 22px;
            position: relative;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i.field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #cbd5e1;
            font-size: 16px;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        .input-wrapper input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #1e293b;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        .input-wrapper input::placeholder {
            color: #cbd5e1;
        }
        .input-wrapper input:hover {
            border-color: #cbd5e1;
            background: #fff;
        }
        .input-wrapper input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
            background: #fff;
        }
        .input-wrapper input:focus ~ i.field-icon {
            color: #2563eb;
        }
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #cbd5e1;
            cursor: pointer;
            font-size: 16px;
            padding: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toggle-password:hover { 
            color: #2563eb;
            transform: translateY(-50%) scale(1.1);
        }
        .error-msg {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            padding: 14px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(239,68,68,0.15);
            animation: shake 0.4s ease;
            font-weight: 500;
        }
        .error-msg i {
            flex-shrink: 0;
            font-size: 16px;
        }
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.4px;
            margin-top: 8px;
            box-shadow: 0 4px 15px rgba(37,99,235,0.15);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 8px 30px rgba(37,99,235,0.35);
            transform: translateY(-2px);
        }
        .btn-login:active { 
            transform: translateY(0);
        }
        .login-footer-text {
            text-align: center;
            margin-top: 28px;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .login-footer-text span { 
            color: #2563eb; 
            font-weight: 600;
        }

        /* ===== RESPONSIVE ===== */
        /* Tablets & Mobile: hide left info, show only form */
        @media (max-width: 1024px) {
            .login-card {
                flex-direction: column;
                min-height: 100vh;
                border-radius: 0;
                margin: 0;
                box-shadow: none;
            }
            .login-left {
                display: none;
            }
            .login-right {
                padding: 48px 32px;
                flex: 1;
                width: 100%;
                max-width: 100%;
                justify-content: center;
            }
            .form-header {
                margin-bottom: 30px;
            }
            .form-header h2 {
                font-size: 26px;
            }
            .form-header p {
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                min-height: 100vh;
                border-radius: 0;
                margin: 0;
                box-shadow: none;
            }
            .login-left {
                display: none;
                padding: 32px 28px;
            }
            .left-content h1 {
                font-size: 22px;
            }
            .feature-list li {
                padding: 10px 0;
                font-size: 13px;
            }
            .feature-list li .f-icon {
                width: 34px; height: 34px;
                font-size: 14px;
            }
            .left-footer {
                margin-top: 20px;
            }
            .login-right {
                padding: 40px 28px;
                flex: 1;
            }
            .form-header {
                margin-bottom: 28px;
            }
            .form-header h2 {
                font-size: 24px;
            }
            .form-header p {
                font-size: 13px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .btn-login {
                padding: 14px;
                font-size: 14px;
            }
        }
        @media (max-width: 480px) {
            .login-wrapper {
                padding: 0;
            }
            .login-left {
                padding: 28px 22px;
            }
            .left-logo {
                width: 60px; height: 60px;
                font-size: 28px;
            }
            .left-content h1 {
                font-size: 19px;
            }
            .login-right {
                padding: 36px 22px;
                flex: 1;
            }
            .form-header {
                margin-bottom: 24px;
            }
            .form-header .welcome-badge {
                padding: 6px 12px;
                font-size: 11px;
            }
            .form-header h2 {
                font-size: 22px;
                margin-bottom: 6px;
            }
            .form-header p {
                font-size: 12px;
            }
            .form-group {
                margin-bottom: 18px;
            }
            .form-group label {
                font-size: 12px;
                margin-bottom: 8px;
            }
            .input-wrapper input {
                padding: 12px 12px 12px 40px;
                font-size: 14px;
            }
            .input-wrapper i.field-icon {
                font-size: 14px;
            }
            .btn-login {
                padding: 13px;
                font-size: 13px;
                gap: 6px;
            }
            .btn-login i {
                font-size: 14px;
            }
            .login-footer-text {
                margin-top: 20px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <!-- LEFT SIDE - Info Panel -->
            <div class="login-left">
                <div class="left-content">
                    <div class="left-logo">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h1>College Fee<br>Management System</h1>
                    <p class="tagline">A complete solution to manage student fee records, payments, receipts and generate reports efficiently.</p>

                    <ul class="feature-list">
                        <li>
                            <div class="f-icon"><i class="fas fa-users"></i></div>
                            <span>Student Management<small>Add, edit & manage all student records</small></span>
                        </li>
                        <li>
                            <div class="f-icon"><i class="fas fa-money-bill-wave"></i></div>
                            <span>Fee Collection<small>Process payments with multiple methods</small></span>
                        </li>
                        <li>
                            <div class="f-icon"><i class="fas fa-receipt"></i></div>
                            <span>Receipt Generation<small>Print & download PDF receipts instantly</small></span>
                        </li>
                        <li>
                            <div class="f-icon"><i class="fas fa-chart-bar"></i></div>
                            <span>Reports & Analytics<small>Visual charts for fee collection insights</small></span>
                        </li>
                    </ul>

                    <div class="left-footer">
                        <i class="fas fa-shield-alt"></i>
                        Secure Admin Portal &bull; &copy; <?php echo date('Y'); ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE - Login Form -->
            <div class="login-right">
                <div class="form-header">
                    <div class="welcome-badge"><i class="fas fa-hand-sparkles"></i> Welcome Back</div>
                    <h2>Admin Login</h2>
                    <p>Enter your credentials to access the dashboard</p>
                </div>

                <?php if($error): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label><i class="fas fa-user" style="margin-right:4px;font-size:12px;"></i> Username</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" placeholder="Enter your username" required autofocus
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            <i class="fas fa-user field-icon"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock" style="margin-right:4px;font-size:12px;"></i> Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" placeholder="Enter your password" required>
                            <i class="fas fa-lock field-icon"></i>
                            <button type="button" class="toggle-password" onclick="togglePass()">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                    </button>
                </form>

                <div class="login-footer-text">
                    &copy; <?php echo date('Y'); ?> College Fee Management | Powered by <span>Admin Panel</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePass() {
            const passInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
