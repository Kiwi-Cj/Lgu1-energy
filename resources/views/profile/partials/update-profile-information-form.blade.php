<div class="profile-form-wrap">
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-form-stack" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="profile-photo-row">
            <img src="{{ $user->profile_photo_url ?? asset('img/default-avatar.png') }}" alt="Profile Photo">
            <div class="profile-photo-input-wrap">
                <label for="profile_photo">Profile Photo</label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/*">
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>
        </div>

        <div>
            <label for="full_name">Full Name</label>
            <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $user->full_name) }}" required autocomplete="name">
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>

        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="profile-verify-note">
                    Your email address is unverified.
                    <button form="send-verification" type="submit" class="profile-inline-link">Re-send verification email</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="profile-success-note">A new verification link has been sent to your email address.</p>
                @endif
            @endif
        </div>

        <div class="profile-form-actions">
            <button type="submit">Save Changes</button>
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

.profile-photo-input-wrap {
    flex: 1;
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
}
</style>
