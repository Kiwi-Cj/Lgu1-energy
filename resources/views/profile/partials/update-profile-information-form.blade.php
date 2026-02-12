<section class="max-w-xl mx-auto bg-white dark:bg-gray-900 rounded-xl shadow-lg p-8 border border-gray-200 dark:border-gray-700">
    <header class="mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex items-center gap-6 mb-4">
            <div>
                <img src="{{ $user->profile_photo_url ?? asset('img/default-avatar.png') }}" alt="Profile Photo" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e8f0; box-shadow: 0 2px 8px #0001; background: #fff;">
            </div>
            <div class="flex flex-col gap-2">
                <label for="profile_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Change Photo</label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>
        </div>
        <div>
            <x-input-label for="full_name" :value="__('Full Name')" class="font-semibold text-gray-800 dark:text-gray-200" />
            <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100" :value="old('full_name', $user->full_name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="font-semibold text-gray-800 dark:text-gray-200" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-yellow-700 dark:text-yellow-400 flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" /></svg>
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="ml-2 underline text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 mt-8">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-150">Save</button>
            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 dark:text-green-400 font-medium"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
