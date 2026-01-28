<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LGU | Login</title>
<link rel="stylesheet" href="<?php echo e(asset('css/style - Copy.css')); ?>">
<style>

body {
    height: 100vh;
    display: flex;
    flex-direction: column;

    /* NEW — background image + blur */
    background: url("<?php echo e(asset('img/cityhall.jpeg')); ?>") center/cover no-repeat fixed;
    position: relative;
    overflow: hidden;
}

/* NEW — Blur overlay */
body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    backdrop-filter: blur(6px); /* actual blur */
    background: rgba(0, 0, 0, 0.35); /* dark overlay */
    z-index: 0; /* keeps blur behind content */
}

/* Make content appear ABOVE blur */
.nav, .wrapper {
    position: relative;
    z-index: 1;
}

/* Make content appear ABOVE blur */
.footer, .wrapper {
    position: relative;
    z-index: 1;
}
</style>
</head>

<body>

<header class="nav">
    <div class="nav-logo">🏛️ Local Government Unit Portal</div>
    <div class="nav-links">
        <a href="">Home</a>
    </div>
</header>

<div class="wrapper">
    <div class="card">  

        <img src="<?php echo e(asset('img/logocityhall.png')); ?>" class="icon-top">

        <h2 class="title">LGU Login</h2>
        <p class="subtitle">Secure access to community maintenance services.</p>

        <div id="loginError" style="display:none;margin-bottom:16px;padding:10px 16px;border-radius:8px;background:#fee2e2;color:#b91c1c;font-weight:500;text-align:center;"></div>
        <form id="loginForm" method="POST" action="<?php echo e(url('/login')); ?>" autocomplete="off">
            <?php echo csrf_field(); ?>
            <div class="input-box">
                <label>Email Address</label>
                <input type="email" name="email" id="loginEmail" placeholder="name@lgu.gov.ph" required autocomplete="off" autocapitalize="off" spellcheck="false">
                <span class="icon">📧</span>
            </div>

            <div class="input-box">
                <label>Password</label>
                <input type="password" name="password" id="loginPassword" placeholder="••••••••" required autocomplete="new-password" autocapitalize="off" spellcheck="false">
                <span class="icon">🔒</span>
            </div>

            <button class="btn-primary" type="submit" id="loginBtn">Sign In</button>

            <!-- Registration link removed -->
        </form>
    </div>
</div>
<?php echo $__env->make('auth.partials.otp-modal-auto', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>

<footer class="footer">

    <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">About</a>
        <a href="#">Help</a>
    </div>

    <div class="footer-logo">
        © 2025 LGU Citizen Portal · All Rights Reserved
    </div>

</footer>

<script src="<?php echo e(asset('js/scripts.js')); ?>"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const btn = document.getElementById('loginBtn');
    const errorDiv = document.getElementById('loginError');
    btn.disabled = true;
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';

    // DEBUG: Log CSRF token value
    const csrfToken = document.querySelector('input[name="_token"]').value;
    console.log('CSRF Token:', csrfToken);

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
            // If OTP modal should show, open it
            if (data.show_otp_modal) {
                openOtpModalAuto(email);
            } else if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
        } else {
            // Show error message
            errorDiv.textContent = data.message || 'Invalid credentials.';
            errorDiv.style.display = 'block';
        }
    } catch (err) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
    }
    btn.disabled = false;
});

// Always hide OTP modal on page load
window.addEventListener('DOMContentLoaded', function() {
    var otpModal = document.getElementById('otpModalAuto');
    if (otpModal) {
        otpModal.style.display = 'none';
    }
});
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\energy-system\resources\views/auth/login.blade.php ENDPATH**/ ?>