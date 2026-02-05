<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LGU | Login</title>
<link rel="stylesheet" href="{{ asset('css/style - Copy.css') }}">
<style>

body {
    height: 100vh;
    display: flex;
    flex-direction: column;

    /* NEW — background image + blur */
    background: url("{{ asset('img/cityhall.jpeg') }}") center/cover no-repeat fixed;
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

        <img src="{{ asset('img/logocityhall.jpg') }}" class="icon-top" style="border-radius:50%;box-shadow:0 2px 8px rgba(49,46,129,0.10);width:110px;height:110px;object-fit:cover;">

        <h2 class="title">Energy Efficiency</h2>
        <p class="subtitle">Secure access to community maintenance services.</p>

        <div id="loginError" style="display:none;margin-bottom:16px;padding:10px 16px;border-radius:8px;background:#fee2e2;color:#b91c1c;font-weight:500;text-align:center;"></div>
        <form id="loginForm" method="POST" action="{{ url('/login') }}" autocomplete="off" autocapitalize="off" spellcheck="false">
                <!-- Hidden fake fields to prevent browser autofill/saving real credentials (must be first in form) -->
                <input type="text" name="fakeusernameremembered" style="position:absolute;top:-9999px;left:-9999px;opacity:0;pointer-events:none;" tabindex="-1" autocomplete="username">
                <input type="password" name="fakepasswordremembered" style="position:absolute;top:-9999px;left:-9999px;opacity:0;pointer-events:none;" tabindex="-1" autocomplete="new-password">
            @csrf
            <div class="input-box">
                <label>Email Address</label>
                <input type="email" name="email" id="loginEmail" placeholder="name@lgu.infra.ph" required autocomplete="username-no-autofill" autocapitalize="off" spellcheck="false">
                                <!-- More anti-autofill: random autocomplete value -->

            </div>

            <div class="input-box">
                <label>Password</label>
                <input type="password" name="password" id="loginPassword" placeholder="••••••••" required autocomplete="new-password" autocapitalize="off" spellcheck="false">
                                <!-- More anti-autofill: random autocomplete value -->

            </div>

            <button class="btn-primary" type="submit" id="loginBtn">
                <span id="loginBtnText">Sign In</span>
                <span id="loginBtnLoading" style="display:none;margin-left:8px;">
                    <svg width="18" height="18" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" stroke="#fff">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="2">
                                <circle stroke-opacity=".3" cx="18" cy="18" r="18"/>
                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/>
                                </path>
                            </g>
                        </g>
                    </svg>
                </span>
            </button>

            <!-- Registration link removed -->
        </form>
    </div>
</div>
@include('auth.partials.otp-modal-auto')
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

<script src="{{ asset('js/scripts.js') }}"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const btn = document.getElementById('loginBtn');
    const btnText = document.getElementById('loginBtnText');
    const btnLoading = document.getElementById('loginBtnLoading');
    const errorDiv = document.getElementById('loginError');
    btn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-block';
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
    btnText.style.display = '';
    btnLoading.style.display = 'none';
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
