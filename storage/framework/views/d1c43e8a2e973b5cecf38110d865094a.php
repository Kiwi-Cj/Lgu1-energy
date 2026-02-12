<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>LGU | Energy Efficiency System</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>

    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary: #6366f1;
            --accent: #0ea5e9;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.4);
        }

        * {
            box-sizing: border-box;
            transition: all 0.2s ease-in-out;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-color: #0f172a; /* Fallback color */
        }

        /* Modern Blurred Background */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.6)), 
                        url('<?php echo e(asset("img/cityhall.jpeg")); ?>') center/cover no-repeat;
            filter: blur(8px);
            transform: scale(1.1); /* Prevents white edges on blur */
            z-index: -1;
        }

        /* Transparent Glass Navbar */
        .nav {
            height: 80px;
            padding: 0 5%;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: #ffffff;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
            text-decoration: none;
        }

        .nav-logo img {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* Center Content */
        .wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* Modern Glass Card */
        .card {
            width: 100%;
            max-width: 410px;
            padding: 38px 34px 32px 34px;
            border-radius: 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .icon-top {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            margin-bottom: 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .title {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
            letter-spacing: -1px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        /* Form Styling */
        .input-box {
            text-align: left;
            margin-bottom: 20px;
        }

        .input-box label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 8px;
            margin-left: 4px;
        }

        .input-box input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            font-size: 1rem;
            color: var(--text-main);
        }

        .input-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* Modern Gradient Button */
        .login-btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(100deg, var(--primary), var(--secondary), var(--accent));
            color: #fff;
            font-weight: 700;
            font-size: 1.08rem;
            margin-top: 12px;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.18);
            transition: background 0.18s, box-shadow 0.18s, filter 0.18s, transform 0.18s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            filter: grayscale(0.2) brightness(0.95);
        }

        .login-btn:hover:not(:disabled) {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.22);
            filter: brightness(1.08);
        }

        .login-btn:active:not(:disabled) {
            transform: translateY(0) scale(0.98);
        }

        .modern-spinner {
            animation: spinner-rotate 0.9s linear infinite;
            margin-right: 6px;
        }
        @keyframes spinner-rotate {
            100% { transform: rotate(360deg); }
        }

        /* Footer */
        .footer {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            padding: 24px;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-links {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #fff;
            text-decoration: none;
            margin: 0 12px;
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .footer-links a:hover {
            opacity: 1;
        }

        .footer-logo {
            font-size: 0.85rem;
        }

        /* Error Message */
        #loginError {
            background: #fff1f2;
            color: #e11d48;
            border: 1px solid #ffe4e6;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .card {
                padding: 32px 24px;
                border-radius: 20px;
            }
            .nav {
                padding: 0 20px;
            }
            .nav-text {
                display: none;
            }
        }
    </style>
</head>

<body>

<header class="nav">
    <a href="/" class="nav-logo">
        <img src="<?php echo e(asset('img/logocityhall.jpg')); ?>" alt="Logo">
        <span>Energy System Portal</span>
    </a>
    <div class="nav-links">
        <a href="/">Home</a>
    </div>
</header>

<div class="wrapper">
    <div class="card">
        <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-bottom: 8px;">
            <img src="<?php echo e(asset('img/logocityhall.jpg')); ?>" class="icon-top" alt="LGU Logo" style="box-shadow:0 4px 16px rgba(37,99,235,0.10);">
        </div>
      
        <div class="subtitle">Sign in to your LGU Energy Efficiency System account</div>
        <div id="loginError" style="display:none;margin-bottom:20px;padding:12px;border-radius:12px;font-weight:500;"></div>

        <form id="loginForm" method="POST" action="<?php echo e(url('/login')); ?>" autocomplete="off" style="margin-top: 10px;">
            <?php echo csrf_field(); ?>
            <input type="text" name="fake_user" style="display:none" tabindex="-1" autocomplete="off">
            <input type="password" name="fake_pass" style="display:none" tabindex="-1" autocomplete="new-password">

            <div class="input-box">
                <label for="loginEmail">Email Address</label>
                <input type="email" name="email" id="loginEmail" placeholder="name@lgu.infra.ph" required autocomplete="off" autocapitalize="off" spellcheck="false" style="box-shadow:0 2px 8px rgba(37,99,235,0.04);">
            </div>

            <div class="input-box">
                <label for="loginPassword">Password</label>
                <input type="password" name="password" id="loginPassword" placeholder="••••••••" required autocomplete="off" style="box-shadow:0 2px 8px rgba(37,99,235,0.04);">
            </div>

            <button type="submit" class="login-btn d-flex align-items-center justify-content-center gap-2" id="loginBtn" style="position:relative;overflow:hidden;">
                <span id="loginBtnText" style="display:inline-block;">Sign In to Dashboard</span>
                <span id="loginBtnLoading" style="display:none;align-items:center;gap:8px;">
                    <svg class="modern-spinner" width="22" height="22" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;"><circle cx="22" cy="22" r="18" stroke="#fff" stroke-width="4" opacity="0.2"/><circle cx="22" cy="22" r="18" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-dasharray="90 60" stroke-dashoffset="0"><animateTransform attributeName="transform" type="rotate" from="0 22 22" to="360 22 22" dur="0.9s" repeatCount="indefinite"/></circle></svg>
                    Authenticating...
                </span>
            </button>
        </form>
        <div style="margin-top:18px;font-size:0.97rem;color:var(--text-muted);">
            <span style="opacity:0.7;">Forgot your password?</span> <a href="#" style="color:var(--primary);font-weight:600;text-decoration:none;">Contact admin</a>
        </div>
    </div>
</div>

<?php echo $__env->make('auth.partials.otp-modal-auto', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<footer class="footer">
    <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">About System</a>
        <a href="#">Technical Support</a>
    </div>
    <div class="footer-logo">
        © 2026 Energy Efficiency System · Local Government Unit
    </div>
</footer>

<script src="<?php echo e(asset('js/scripts.js')); ?>"></script>
<script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const btn = document.getElementById('loginBtn');
        const btnText = document.getElementById('loginBtnText');
        const btnLoading = document.getElementById('loginBtnLoading');
        const errorDiv = document.getElementById('loginError');

        // Loading State
        btn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-block';
        errorDiv.style.display = 'none';

        const csrfToken = document.querySelector('input[name="_token"]').value;

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password }),
                credentials: 'same-origin'
            });

            const data = await response.json();

            if (response.ok) {
                if (data.show_otp_modal) {
                    openOtpModalAuto(email);
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            } else {
                errorDiv.textContent = data.message || 'The credentials you entered are incorrect.';
                errorDiv.style.display = 'block';
                btn.disabled = false;
                btnText.style.display = '';
                btnLoading.style.display = 'none';
            }
        } catch (err) {
            errorDiv.textContent = 'Connection error. Please check your internet.';
            errorDiv.style.display = 'block';
            btn.disabled = false;
            btnText.style.display = '';
            btnLoading.style.display = 'none';
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
        const otpModal = document.getElementById('otpModalAuto');
        if (otpModal) otpModal.style.display = 'none';

        // Patch: Reset login button if OTP modal is closed (user clicks back/close)
        window.closeOtpModalAuto = function() {
            document.getElementById('otpModalAuto').style.display = 'none';
            if (window.otpInterval) clearInterval(window.otpInterval);
            // Reset login button state
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('loginBtnText');
            const btnLoading = document.getElementById('loginBtnLoading');
            if (btn && btnText && btnLoading) {
                btn.disabled = false;
                btnText.style.display = '';
                btnLoading.style.display = 'none';
            }
        }
    });
</script>
</body>
</html><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/auth/login.blade.php ENDPATH**/ ?>