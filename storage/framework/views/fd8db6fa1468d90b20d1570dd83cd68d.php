<!-- resources/views/auth/partials/otp-modal-auto.blade.php -->

<div id="otpModalAuto">
    <div class="otp-modal-content pro">
        <button class="otp-close" onclick="closeOtpModalAuto()" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <img src="<?php echo e(asset('img/logocityhall.png')); ?>" class="otp-logo" alt="LGU Logo">
        <h2 class="otp-title">Verify Your Identity</h2>
        <div id="otpTimer" class="otp-timer">05:00</div>
        <p class="otp-desc">Enter the One-Time Password (OTP) sent to your email.</p>
        <form id="otpModalAutoForm" method="POST" action="<?php echo e(route('otp.verify.submit')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="email" id="otpModalAutoEmail">
            <input type="text" name="otp" maxlength="6" required autocomplete="one-time-code" placeholder="Enter 6-digit code" class="otp-input pro">
            <button type="submit" id="otpVerifyBtn" class="otp-btn pro">Verify OTP</button>
        </form>
        <button type="button" id="otpResendBtn" class="otp-btn pro" style="margin-top:8px;background:#e0e7ef;color:#2563eb;" onclick="resendOtpAuto()">Resend OTP</button>
        <div id="otpResendMsg" class="otp-success" style="display:none;margin-top:10px;"></div>
        </form>
        <div id="otpExpiredMsg" class="otp-error" style="display:none">OTP expired. Please request a new code.</div>
        <?php if($errors->any()): ?>
            <div class="otp-error"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>
        <?php if(session('success')): ?>
            <div class="otp-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
    </div>
</div>

<style>
/* OVERLAY */
#otpModalAuto {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(30,41,59,0.18);
    z-index: 10050;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', Arial, sans-serif;
}

/* MODAL BOX */
.otp-modal-content.pro {
    width: 100%;
    max-width: 410px;
    background: #f8fafc;
    border-radius: 20px;
    padding: 38px 34px 32px 34px;
    position: relative;
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 8px 32px rgba(37,99,235,0.13), 0 1.5px 8px rgba(30,41,59,0.07);
    border: 1.5px solid #e0e7ef;
    animation: pop 0.25s cubic-bezier(.4,2,.6,1);
    transition: box-shadow 0.18s;
}

@keyframes pop {
    from { transform: scale(0.95); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

.otp-close {
    position: absolute;
    top: 14px;
    right: 18px;
    background: none;
    border: none;
    font-size: 1.7rem;
    color: #64748b;
    cursor: pointer;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.18s, color 0.18s;
}
.otp-close:hover {
    background: #e0e7ef;
    color: #e11d48;
}


.otp-logo {
    width: 56px;
    margin: 0 auto 8px auto;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(37,99,235,0.08);
    background: #fff;
}

.otp-title {
    color: #2563eb;
    font-weight: 700;
    font-size: 1.28rem;
    margin-bottom: 2px;
    letter-spacing: 0.01em;
}

h2 {
    color: #2563eb;
    font-weight: 700;
}


.otp-timer {
    font-weight: 700;
    color: #2563eb;
    letter-spacing: 2px;
    font-size: 1.08rem;
    margin-bottom: 2px;
}


.otp-desc {
    color: #64748b;
    font-size: 1.01rem;
    margin-bottom: 8px;
}


.otp-input.pro {
    width: 100%;
    padding: 12px;
    font-size: 1.18rem;
    text-align: center;
    letter-spacing: 0.32em;
    border-radius: 9px;
    border: 1.5px solid #c3cbe5;
    background: #f8fafc;
    transition: border 0.18s, background 0.18s;
    margin-bottom: 2px;
}
.otp-input.pro:focus {
    border-color: #2563eb;
    background: #f0f6ff;
    outline: none;
}


.otp-btn.pro {
    padding: 12px;
    border-radius: 9px;
    border: none;
    background: linear-gradient(90deg,#2563eb,#6366f1);
    color: #fff;
    font-weight: 700;
    font-size: 1.08rem;
    margin-top: 8px;
    box-shadow: 0 2px 8px rgba(55,98,200,0.10);
    cursor: pointer;
    transition: background 0.18s, box-shadow 0.18s;
}
.otp-btn.pro:hover:not(:disabled) {
    background: linear-gradient(90deg,#1d4ed8,#6366f1);
    box-shadow: 0 4px 16px rgba(37,99,235,0.13);
}
.otp-btn.pro:disabled {
    background: #cbd5e1;
    color: #fff;
    cursor: not-allowed;
}


.otp-error {
    color: #e11d48;
    font-weight: 600;
    margin-top: 10px;
    font-size: 1.01rem;
}
.otp-success {
    color: #22c55e;
    font-weight: 600;
    margin-top: 10px;
    font-size: 1.01rem;
}
</style>


<script>

let resendCooldown = false;
let resendTimeout;
let resendInterval;
const RESEND_COOLDOWN = 60; // seconds


function resendOtpAuto() {
    if (resendCooldown) return;
    const btn = document.getElementById('otpResendBtn');
    const email = document.getElementById('otpModalAutoEmail').value;
    const msgDiv = document.getElementById('otpResendMsg');
    btn.disabled = true;
    btn.textContent = 'Sending...';
    msgDiv.style.display = 'none';
    fetch('/otp/resend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ email }),
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        resendCooldown = true;
        let secondsLeft = RESEND_COOLDOWN;
        btn.disabled = true;
        btn.style.background = '#cbd5e1';
        btn.style.color = '#64748b';
        // Reset OTP timer and localStorage
        setOtpTimerStart();
        startOtpTimer();
        // Start resend cooldown with countdown
        btn.textContent = `Resend OTP (${secondsLeft}s)`;
        resendInterval = setInterval(() => {
            secondsLeft--;
            btn.textContent = `Resend OTP (${secondsLeft}s)`;
            if (secondsLeft <= 0) {
                clearInterval(resendInterval);
                btn.disabled = false;
                resendCooldown = false;
                btn.textContent = 'Resend OTP';
                btn.style.background = '#e0e7ef';
                btn.style.color = '#2563eb';
            }
        }, 1000);
        // Show a modal-like message below the button
        msgDiv.textContent = data.message || 'OTP resent!';
        msgDiv.style.display = 'block';
        setTimeout(() => { msgDiv.style.display = 'none'; }, 4000);
    })
    .catch(() => {
        btn.textContent = 'Resend OTP';
        btn.disabled = false;
        resendCooldown = false;
        btn.style.background = '#e0e7ef';
        btn.style.color = '#2563eb';
        msgDiv.textContent = 'Failed to resend OTP.';
        msgDiv.style.display = 'block';
        setTimeout(() => { msgDiv.style.display = 'none'; }, 4000);
    });
}

let otpInterval;
const OTP_DURATION = 300; // 5 minutes in seconds
const OTP_TIMER_KEY = 'otp_timer_start';
function getOtpSecondsLeft() {
    const start = parseInt(localStorage.getItem(OTP_TIMER_KEY), 10);
    if (!start) return OTP_DURATION;
    const elapsed = Math.floor(Date.now() / 1000) - start;
    return Math.max(0, OTP_DURATION - elapsed);
}
function setOtpTimerStart() {
    localStorage.setItem(OTP_TIMER_KEY, Math.floor(Date.now() / 1000));
}
function clearOtpTimerStart() {
    localStorage.removeItem(OTP_TIMER_KEY);
}


function openOtpModalAuto(email) {
    document.getElementById('otpModalAuto').style.display = 'flex';
    document.getElementById('otpModalAutoEmail').value = email || '';
    // Only set timer start if not already set (new OTP)
    if (!localStorage.getItem(OTP_TIMER_KEY)) {
        setOtpTimerStart();
    }
    startOtpTimer();
}


function closeOtpModalAuto() {
    document.getElementById('otpModalAuto').style.display = 'none';
    clearInterval(otpInterval);
}


function startOtpTimer() {
    const timer = document.getElementById('otpTimer');
    const btn = document.getElementById('otpVerifyBtn');
    const expired = document.getElementById('otpExpiredMsg');

    clearInterval(otpInterval);
    btn.disabled = false;
    expired.style.display = 'none';
    timer.style.color = '#2563eb';

    function updateTimer() {
        let secondsLeft = getOtpSecondsLeft();
        let m = Math.floor(secondsLeft / 60);
        let s = secondsLeft % 60;
        timer.textContent = `${m}:${s.toString().padStart(2,'0')}`;
        if (secondsLeft <= 0) {
            clearInterval(otpInterval);
            timer.textContent = 'OTP expired';
            timer.style.color = '#e11d48';
            btn.disabled = true;
            expired.style.display = 'block';
            clearOtpTimerStart();
        }
    }
    updateTimer();
    otpInterval = setInterval(updateTimer, 1000);
}

// AJAX OTP verification
document.getElementById('otpModalAutoForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('otpModalAutoEmail').value;
    const otp = this.otp.value;
    const btn = document.getElementById('otpVerifyBtn');
    const errorDiv = document.querySelector('#otpModalAuto .otp-error');
    const successDiv = document.querySelector('#otpModalAuto .otp-success');
    btn.disabled = true;
    if (errorDiv) errorDiv.style.display = 'none';
    if (successDiv) successDiv.style.display = 'none';

    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ email, otp }),
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (response.ok) {
            // Success: redirect to dashboard
            clearOtpTimerStart();
            window.location.href = data.redirect || '/modules/dashboard/index';
        } else {
            // Show error in modal
            if (errorDiv) {
                errorDiv.textContent = data.message || 'Invalid or expired OTP.';
                errorDiv.style.display = 'block';
            }
        }
    } catch (err) {
        if (errorDiv) {
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.style.display = 'block';
        }
    }
    btn.disabled = false;
});

</script>
</script>
<?php /**PATH C:\xampp\htdocs\energy-system\resources\views/auth/partials/otp-modal-auto.blade.php ENDPATH**/ ?>