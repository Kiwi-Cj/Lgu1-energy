<section class="profile-delete-wrap">
    <p class="profile-delete-note">
        Once your account is deleted, all of its resources and data will be permanently deleted.
    </p>

    <button class="profile-delete-btn" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        Delete Account
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="profile-delete-modal-form">
            @csrf
            @method('delete')

            <h2>Are you sure you want to delete your account?</h2>
            <p>
                This action is permanent. Enter your password to confirm account deletion.
            </p>

            <div class="mt-4">
                <label for="password" class="sr-only">Password</label>
                <input id="password" name="password" type="password" placeholder="Password">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="profile-delete-actions">
                <button type="button" class="profile-cancel-btn" x-on:click="$dispatch('close')">Cancel</button>
                <button type="submit" class="profile-confirm-delete-btn">Delete Account</button>
            </div>
        </form>
    </x-modal>
</section>

<style>
.profile-delete-note {
    color: #475569;
    font-size: 0.9rem;
    margin-bottom: 12px;
}

.profile-delete-btn,
.profile-confirm-delete-btn {
    background: #dc2626;
    color: #ffffff;
    border: 0;
    border-radius: 10px;
    padding: 10px 14px;
    font-weight: 700;
    cursor: pointer;
}

.profile-cancel-btn {
    background: #e2e8f0;
    color: #0f172a;
    border: 0;
    border-radius: 10px;
    padding: 10px 14px;
    font-weight: 700;
    cursor: pointer;
}

.profile-delete-modal-form h2 {
    font-size: 1.08rem;
    margin: 0;
    color: #0f172a;
}

.profile-delete-modal-form p {
    margin: 8px 0 0;
    color: #475569;
}

.profile-delete-modal-form input[type="password"] {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    color: #0f172a;
    padding: 10px 12px;
}

.profile-delete-actions {
    margin-top: 14px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

body.dark-mode .profile-delete-note {
    color: #94a3b8;
}

body.dark-mode .profile-cancel-btn {
    background: #334155;
    color: #e2e8f0;
}

body.dark-mode .profile-delete-modal-form h2 {
    color: #e2e8f0;
}

body.dark-mode .profile-delete-modal-form p {
    color: #94a3b8;
}

body.dark-mode .profile-delete-modal-form input[type="password"] {
    background: #0b1220;
    color: #e2e8f0;
    border-color: #334155;
}
</style>
