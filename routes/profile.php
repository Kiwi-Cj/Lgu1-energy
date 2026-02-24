<?php

use Illuminate\Support\Facades\Route;

$resolvePublicUploadRoot = function (): string {
    $configured = (string) env('PUBLIC_UPLOAD_ROOT', '');
    if ($configured !== '' && is_dir($configured)) {
        return rtrim($configured, DIRECTORY_SEPARATOR);
    }

    $cpanelPublicHtml = dirname(base_path()) . DIRECTORY_SEPARATOR . 'public_html';
    if (is_dir($cpanelPublicHtml)) {
        return rtrim($cpanelPublicHtml, DIRECTORY_SEPARATOR);
    }

    return public_path();
};

// --- User Profile Route ---
Route::get('/profile', function () {
    return view('profile.show');
})->name('profile.show');

// --- Edit Profile Route ---
Route::get('/profile/edit', function () {
    return view('profile.edit');
})->name('profile.edit');

// --- Update Profile Route (photo-only) ---
Route::patch('/profile', function (\Illuminate\Http\Request $request) use ($resolvePublicUploadRoot) {
    $user = auth()->user();

    $request->validate([
        'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    if ($request->hasFile('profile_photo')) {
        $file = $request->file('profile_photo');
        $directory = $resolvePublicUploadRoot() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile_photos';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid('profile_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        $user->profile_photo_path = 'uploads/profile_photos/' . $filename;
    }

    $user->save();

    return redirect('/profile/edit')->with('status', 'profile-updated');
})->name('profile.update');

// --- Delete Profile Route ---
Route::delete('/profile', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    $request->validate([
        'password' => 'required',
    ]);
    // Check password
    if (!\Hash::check($request->password, $user->password)) {
        return redirect('/profile')->withErrors(['password' => 'Incorrect password.'], 'userDeletion');
    }

    $userId = $user->id;
    auth()->logout();
    \App\Models\User::whereKey($userId)->delete();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/')->with('status', 'Your account has been deleted.');
})->name('profile.destroy');
