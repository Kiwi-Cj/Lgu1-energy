
<?php $__env->startSection('title', 'Create Energy Action'); ?>
<?php $__env->startSection('content'); ?>
<style>
    .ea-form-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px; }
    .ea-label { font-weight: 600; margin-bottom: 2px; }
    .ea-input, .ea-select, .ea-textarea {
        width: 100%;
        padding: 10px 13px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 1rem;
        background: #f9fafb;
        transition: border 0.18s, box-shadow 0.18s;
    }
    .ea-input:focus, .ea-select:focus, .ea-textarea:focus {
        border: 1.5px solid #2563eb;
        outline: none;
        background: #fff;
        box-shadow: 0 0 0 2px #2563eb22;
    }
    .ea-input[readonly] {
        background: #f3f4f6;
        color: #888;
        cursor: not-allowed;
    }
    .ea-textarea { min-height: 60px; resize: vertical; }
    .ea-btn {
        background: linear-gradient(90deg,#2563eb,#6366f1);
        color: #fff;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        padding: 12px 0;
        font-size: 1.1rem;
        margin-top: 8px;
        transition: background 0.18s, box-shadow 0.18s;
        width: 100%;
        box-shadow: 0 2px 8px rgba(55,98,200,0.08);
    }
    .ea-btn:hover, .ea-btn:focus {
        background: linear-gradient(90deg,#1d4ed8,#6366f1);
        box-shadow: 0 4px 16px rgba(55,98,200,0.13);
    }
</style>
<div class="modal-content" style="max-width:540px;margin:40px auto;background:#fff;border-radius:18px;box-shadow:0 8px 32px rgba(49,46,129,0.13);padding:32px 28px 24px 28px;">
    <h2 style="font-size:1.6rem;font-weight:800;color:#312e81;margin-bottom:18px;">Create Energy Action</h2>
    <form method="POST" action="<?php echo e(url('/energy-actions/store')); ?>">
        <?php echo csrf_field(); ?>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Facility</label>
                <input type="text" class="ea-input" value="<?php echo e($facility->name); ?>" readonly>
                <input type="hidden" name="facility_id" value="<?php echo e($facility->id); ?>">
            </div>
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Current Month kWh</label>
                <input type="text" class="ea-input" value="<?php echo e($currentKwh ?? '-'); ?>" readonly>
            </div>
        </div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Baseline kWh (3-mo avg)</label>
                <input type="text" class="ea-input" value="<?php echo e($baseline ?? '-'); ?>" readonly>
            </div>
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Deviation %</label>
                <input type="text" class="ea-input" value="<?php echo e($deviation !== null ? $deviation.'%' : '-'); ?>" readonly>
            </div>
        </div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Alert Level</label>
                <input type="text" class="ea-input" value="<?php echo e($alertLevel); ?>" readonly>
            </div>
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Risk Score</label>
                <input type="text" class="ea-input" value="<?php echo e($riskScore ?? '-'); ?>" readonly>
            </div>
        </div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Trigger Reason</label>
                <input type="text" class="ea-input" value="<?php echo e($triggerReason); ?>" readonly>
            </div>
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Action Type</label>
                <select name="action_type" class="ea-select" required>
                    <option value="">Select Action</option>
                    <option value="Inspection">Equipment inspection</option>
                    <option value="Maintenance">Schedule maintenance</option>
                    <option value="Behavioral">Behavioral change</option>
                    <option value="Retrofit">Retrofit / upgrade</option>
                </select>
            </div>
        </div>
        <div class="ea-form-group">
            <label class="ea-label">Description</label>
            <textarea name="description" class="ea-textarea" required></textarea>
        </div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Priority</label>
                <select name="priority" class="ea-select" required>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div class="ea-form-group" style="flex:1 1 220px;min-width:0;">
                <label class="ea-label">Target Completion Date</label>
                <input type="date" name="target_date" class="ea-input" required>
            </div>
        </div>
        <button type="submit" class="ea-btn">Save Action</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-monitoring/energy-actions/create.blade.php ENDPATH**/ ?>