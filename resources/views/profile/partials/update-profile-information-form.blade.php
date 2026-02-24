<div class="profile-form-wrap">
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-form-stack" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="profile-photo-row">
            <img src="{{ $user->profile_photo_url ?? asset('img/default-avatar.png') }}" alt="Profile Photo" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div class="profile-photo-fallback" style="display:none;">
                {{ strtoupper(substr(trim((string) ($user->full_name ?? $user->name ?? 'U')), 0, 1)) }}
            </div>
            <div class="profile-photo-input-wrap">
                <label for="profile_photo">Profile Photo</label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/*">
                <p class="profile-field-help">JPG, PNG, GIF, or SVG up to 2MB.</p>
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>
        </div>

        <div class="profile-readonly-grid">
            <div class="profile-readonly-card">
                <span class="profile-readonly-label">Full Name</span>
                <span class="profile-readonly-value">{{ $user->full_name ?? $user->name ?? '-' }}</span>
            </div>
            <div class="profile-readonly-card">
                <span class="profile-readonly-label">Email</span>
                <span class="profile-readonly-value">{{ $user->email ?? '-' }}</span>
            </div>
        </div>

        <div class="profile-form-actions">
            <button type="submit">Update Photo</button>
            @if (session('status') === 'profile-updated')
                <p class="profile-success-note">Saved.</p>
            @endif
        </div>
    </form>
</div>

<style>
.profile-form-stack {
    display: grid;
    gap: 14px;
}

.profile-photo-row {
    display: flex;
    gap: 14px;
    align-items: center;
}

.profile-photo-row img {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #dbeafe;
}

.profile-photo-fallback {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.4rem;
    color: #1e3a8a;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border: 2px solid #dbeafe;
}

.profile-photo-input-wrap {
    flex: 1;
}

.profile-field-help {
    margin: 8px 0 0;
    font-size: 0.84rem;
    color: #64748b;
}

.profile-readonly-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.profile-readonly-card {
    border: 1px solid #dbe2ee;
    border-radius: 12px;
    background: #f8fafc;
    padding: 12px 14px;
    display: grid;
    gap: 6px;
}

.profile-readonly-label {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #64748b;
}

.profile-readonly-value {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    word-break: break-word;
}

.profile-form-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-verify-note {
    margin-top: 8px;
    font-size: 0.86rem;
    color: #a16207;
}

.profile-inline-link {
    border: 0;
    background: transparent;
    color: #1d4ed8;
    text-decoration: underline;
    padding: 0;
    margin-left: 6px;
    font-weight: 700;
}

.profile-success-note {
    font-size: 0.84rem;
    font-weight: 700;
    color: #15803d;
}

body.dark-mode .profile-photo-row img {
    border-color: #1e3a8a;
}

body.dark-mode .profile-photo-fallback {
    border-color: #1e3a8a;
    background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
    color: #dbeafe;
}

body.dark-mode .profile-field-help {
    color: #94a3b8;
}

body.dark-mode .profile-readonly-card {
    background: #111827;
    border-color: #334155;
}

body.dark-mode .profile-readonly-label {
    color: #94a3b8;
}

body.dark-mode .profile-readonly-value {
    color: #e2e8f0;
}

body.dark-mode .profile-verify-note {
    color: #facc15;
}

body.dark-mode .profile-inline-link {
    color: #93c5fd;
}

body.dark-mode .profile-success-note {
    color: #86efac;
}

@media (max-width: 640px) {
    .profile-photo-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .profile-readonly-grid {
        grid-template-columns: 1fr;
    }
}
</style>
