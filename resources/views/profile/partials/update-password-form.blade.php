<form method="post" action="{{ route('password.update') }}" class="profile-form-stack">
    @csrf
    @method('put')

    <p class="profile-password-hint">
        Strong password required: at least 12 characters with uppercase, lowercase, number, and symbol.
    </p>

    <div>
        <label for="update_password_current_password">Current Password</label>
        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password">
        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
    </div>

    <div>
        <label for="update_password_password">New Password</label>
        <input id="update_password_password" name="password" type="password" autocomplete="new-password">
        <div class="profile-password-strength" id="profilePasswordStrength">
            <div class="strength-head">
                <span>Password strength</span>
                <strong id="strengthLabel" class="level-empty">Not yet evaluated</strong>
            </div>
            <div class="strength-bar-track">
                <div id="strengthBar" class="strength-bar-fill"></div>
            </div>
            <ul class="strength-rules">
                <li id="ruleLength">At least 12 characters</li>
                <li id="ruleUpper">At least 1 uppercase letter</li>
                <li id="ruleLower">At least 1 lowercase letter</li>
                <li id="ruleNumber">At least 1 number</li>
                <li id="ruleSymbol">At least 1 symbol</li>
            </ul>
        </div>
        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
    </div>

    <div>
        <label for="update_password_password_confirmation">Confirm Password</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
        <p id="passwordMatchHint" class="profile-password-match-hint">Waiting for confirmation...</p>
        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
    </div>

    <div class="profile-form-actions">
        <button type="submit">Update Password</button>
        @if (session('status') === 'password-updated')
            <p class="profile-success-note">Saved.</p>
        @endif
    </div>
</form>

<style>
.profile-password-hint {
    font-size: 0.86rem;
    color: #64748b;
    margin: 0;
}

body.dark-mode .profile-password-hint {
    color: #94a3b8;
}

.profile-password-strength {
    margin-top: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    background: #f8fafc;
}

.strength-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 0.84rem;
    color: #475569;
}

.strength-head strong {
    font-size: 0.82rem;
    border-radius: 999px;
    padding: 4px 10px;
    border: 1px solid transparent;
}

.strength-head .level-empty {
    color: #64748b;
    background: #e2e8f0;
}

.strength-head .level-weak {
    color: #991b1b;
    background: #fee2e2;
    border-color: #fecaca;
}

.strength-head .level-medium {
    color: #92400e;
    background: #fef3c7;
    border-color: #fde68a;
}

.strength-head .level-strong {
    color: #166534;
    background: #dcfce7;
    border-color: #86efac;
}

.strength-bar-track {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 10px;
}

.strength-bar-fill {
    width: 0%;
    height: 100%;
    background: #94a3b8;
    transition: width 0.2s ease, background 0.2s ease;
}

.strength-rules {
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 4px;
}

.strength-rules li {
    font-size: 0.82rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.strength-rules li::before {
    content: 'x';
    font-weight: 700;
    color: #dc2626;
}

.strength-rules li.is-pass {
    color: #166534;
}

.strength-rules li.is-pass::before {
    content: '✓';
    color: #16a34a;
}

.profile-password-match-hint {
    margin-top: 8px;
    font-size: 0.82rem;
    color: #64748b;
}

.profile-password-match-hint.is-match {
    color: #15803d;
}

.profile-password-match-hint.is-mismatch {
    color: #b91c1c;
}

body.dark-mode .profile-password-strength {
    background: #111827;
    border-color: #334155;
}

body.dark-mode .strength-head,
body.dark-mode .strength-rules li,
body.dark-mode .profile-password-match-hint {
    color: #94a3b8;
}

body.dark-mode .strength-head .level-empty {
    color: #cbd5e1;
    background: #334155;
    border-color: #475569;
}

body.dark-mode .strength-head .level-weak {
    color: #fecaca;
    background: #7f1d1d;
    border-color: #991b1b;
}

body.dark-mode .strength-head .level-medium {
    color: #fde68a;
    background: #78350f;
    border-color: #92400e;
}

body.dark-mode .strength-head .level-strong {
    color: #86efac;
    background: #14532d;
    border-color: #166534;
}

body.dark-mode .strength-bar-track {
    background: #1f2937;
}

body.dark-mode .strength-rules li.is-pass,
body.dark-mode .profile-password-match-hint.is-match {
    color: #86efac;
}

body.dark-mode .profile-password-match-hint.is-mismatch {
    color: #fca5a5;
}
</style>

<script>
window.addEventListener('DOMContentLoaded', function () {
    var passwordInput = document.getElementById('update_password_password');
    var confirmInput = document.getElementById('update_password_password_confirmation');
    var strengthLabel = document.getElementById('strengthLabel');
    var strengthBar = document.getElementById('strengthBar');
    var matchHint = document.getElementById('passwordMatchHint');

    if (!passwordInput || !confirmInput || !strengthLabel || !strengthBar || !matchHint) {
        return;
    }

    var rules = {
        length: document.getElementById('ruleLength'),
        upper: document.getElementById('ruleUpper'),
        lower: document.getElementById('ruleLower'),
        number: document.getElementById('ruleNumber'),
        symbol: document.getElementById('ruleSymbol')
    };

    function markRule(element, isPass) {
        if (!element) return;
        element.classList.toggle('is-pass', isPass);
    }

    function updateStrength() {
        var value = passwordInput.value || '';
        var checks = {
            length: value.length >= 12,
            upper: /[A-Z]/.test(value),
            lower: /[a-z]/.test(value),
            number: /\d/.test(value),
            symbol: /[^A-Za-z0-9]/.test(value)
        };

        markRule(rules.length, checks.length);
        markRule(rules.upper, checks.upper);
        markRule(rules.lower, checks.lower);
        markRule(rules.number, checks.number);
        markRule(rules.symbol, checks.symbol);

        var score = 0;
        Object.keys(checks).forEach(function (key) {
            if (checks[key]) score += 1;
        });

        var width = (score / 5) * 100;
        strengthBar.style.width = width + '%';

        strengthLabel.classList.remove('level-empty', 'level-weak', 'level-medium', 'level-strong');

        if (value.length === 0) {
            strengthLabel.textContent = 'Not yet evaluated';
            strengthLabel.classList.add('level-empty');
            strengthBar.style.background = '#94a3b8';
        } else if (score <= 2) {
            strengthLabel.textContent = 'Weak';
            strengthLabel.classList.add('level-weak');
            strengthBar.style.background = '#dc2626';
        } else if (score <= 4) {
            strengthLabel.textContent = 'Medium';
            strengthLabel.classList.add('level-medium');
            strengthBar.style.background = '#f59e0b';
        } else {
            strengthLabel.textContent = 'Strong';
            strengthLabel.classList.add('level-strong');
            strengthBar.style.background = '#16a34a';
        }
    }

    function updateMatchHint() {
        var password = passwordInput.value || '';
        var confirm = confirmInput.value || '';

        matchHint.classList.remove('is-match', 'is-mismatch');
        if (confirm.length === 0) {
            matchHint.textContent = 'Waiting for confirmation...';
            return;
        }

        if (password === confirm) {
            matchHint.textContent = 'Passwords match.';
            matchHint.classList.add('is-match');
        } else {
            matchHint.textContent = 'Passwords do not match.';
            matchHint.classList.add('is-mismatch');
        }
    }

    passwordInput.addEventListener('input', function () {
        updateStrength();
        updateMatchHint();
    });

    confirmInput.addEventListener('input', updateMatchHint);

    updateStrength();
    updateMatchHint();
});
</script>
