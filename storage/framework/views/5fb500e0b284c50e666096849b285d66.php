<div id="otpModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div class="modal-content" style="background:#fff; padding:2rem; border-radius:8px; max-width:400px; width:90%; box-shadow:0 2px 16px rgba(0,0,0,0.2);">
        <h2 class="title">OTP Verification</h2>
        <p class="subtitle">Enter the OTP sent to your email to continue.</p>
        <form id="otpForm" method="POST" action="<?php echo e(route('verify.otp')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="user_id" id="otp_user_id" value="">
            <div class="input-box">
                <label>OTP Code</label>
                <input type="text" name="otp_code" maxlength="10" required autofocus>
            </div>
            <button class="btn-primary" type="submit">Verify OTP</button>
        </form>
        <button onclick="closeOtpModal()" style="margin-top:1rem; background:#eee; border:none; padding:0.5rem 1rem; border-radius:4px;">Cancel</button>
    </div>
</div>
<script>
function showOtpModal(userId) {
    document.getElementById('otp_user_id').value = userId;
    document.getElementById('otpModal').style.display = 'flex';
}
function closeOtpModal() {
    document.getElementById('otpModal').style.display = 'none';
}
</script>
<?php /**PATH C:\xampp\htdocs\energy-system\resources\views/auth/otp-modal.blade.php ENDPATH**/ ?>