<?php $__env->startSection('content'); ?>
<?php
	// Ensure notifications and unreadNotifCount are available for the notification bell
	$user = auth()->user();
	$notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
	$unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
?>

<?php if(session('success')): ?>
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;font-size:1.3rem;"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
</div>
<?php endif; ?>
<?php if(session('error')): ?>
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-times-circle" style="color:#e11d48;font-size:1.3rem;"></i>
        <span><?php echo e(session('error')); ?></span>
    </div>
</div>
<?php endif; ?>
<script>
window.addEventListener('DOMContentLoaded', function() {
        var success = document.getElementById('successAlert');
        var error = document.getElementById('errorAlert');
        if (success) setTimeout(() => success.style.display = 'none', 3000);
        if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>

<div style="max-width: 800px; margin: 40px auto; padding: 0 20px; font-family: 'Inter', -apple-system, sans-serif;">
    
    
    <div style="margin-bottom: 24px;">
        <a href="/profile" style="color: #64748b; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Back to Profile view
        </a>
    </div>

    
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; margin-bottom: 24px; display: flex; align-items: center; gap: 28px; position: relative; overflow: hidden;">
        
        <div style="position: absolute; top: 0; right: 0; width: 150px; height: 100%; background: linear-gradient(225deg, #eff6ff 0%, transparent 100%); z-index: 0;"></div>

        <div style="position: relative; z-index: 1; display: flex; align-items: center; gap: 24px; width: 100%;">
            <div style="position: relative;">
                <img src="<?php echo e(auth()->user()->profile_photo_url ?? '/img/default-avatar.png'); ?>" 
                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                <div style="position: absolute; bottom: 5px; right: 5px; background: #22c55e; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff;"></div>
            </div>

            <div style="flex: 1;">
                <h1 style="margin: 0; font-size: 1.8rem; font-weight: 800; color: #1e293b; letter-spacing: -0.025em;">Settings</h1>
                <p style="margin: 4px 0 0; color: #64748b; font-size: 1rem;">
                    Manage your account details and preferences.
                </p>
                <div style="margin-top: 12px; display: flex; align-items: center; gap: 8px;">
                    <span style="background: #f1f5f9; color: #475569; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; border: 1px solid #e2e8f0;">
                        <?php echo e(ucfirst(auth()->user()->role)); ?>

                    </span>
                    <span style="color: #94a3b8; font-size: 0.85rem;">•</span>
                    <span style="color: #64748b; font-size: 0.85rem;"><?php echo e(auth()->user()->email); ?></span>
                </div>
            </div>
        </div>
    </div>

    
    <div style="display: grid; gap: 24px;">
        
        
        <section style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;">
            <div style="padding: 24px 32px; border-bottom: 1px solid #f1f5f9; background: #fafafa;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #334155;">Profile Information</h3>
                <p style="margin: 4px 0 0; font-size: 0.9rem; color: #64748b;">Update your account's profile information and email address.</p>
            </div>
            <div style="padding: 32px;">
                <?php echo $__env->make('profile.partials.update-profile-information-form', ['user' => auth()->user()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </section>

        
        <section style="background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;">
            <div style="padding: 24px 32px; border-bottom: 1px solid #f1f5f9; background: #fafafa;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #334155;">Security</h3>
                <p style="margin: 4px 0 0; font-size: 0.9rem; color: #64748b;">Ensure your account is using a long, random password to stay secure.</p>
            </div>
            <div style="padding: 32px;">
                <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </section>

        
        <section style="background: #fff; border: 1px solid #fee2e2; border-radius: 16px; overflow: hidden;">
            <div style="padding: 24px 32px; border-bottom: 1px solid #fee2e2; background: #fffafb;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #991b1b;">Danger Zone</h3>
                <p style="margin: 4px 0 0; font-size: 0.9rem; color: #b91c1c;">Irreversibly delete your account and all associated data.</p>
            </div>
            <div style="padding: 32px;">
                <?php echo $__env->make('profile.partials.delete-user-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </section>

    </div>
</div>

<style>
    /* Simpleng hover effect para sa mga buttons sa loob ng partials kung walang styling */
    input[type="text"], input[type="email"], input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        margin-top: 6px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/profile/edit.blade.php ENDPATH**/ ?>