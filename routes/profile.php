<?php

use Illuminate\Support\Facades\Route;

// --- User Profile Route ---
Route::get('/profile', function () {
    return view('profile.show');
})->name('profile.show');

// --- Edit Profile Route ---
Route::get('/profile/edit', function () {
    return view('profile.edit');
})->name('profile.edit');

// --- Update Profile Route ---
Route::patch('/profile', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    $validated = $request->validate([
        'name' => 'nullable|string|max:255',
        'full_name' => 'nullable|string|max:255',
        'email' => 'required|email|max:255',
        'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);
    $resolvedName = trim((string) ($validated['full_name'] ?? $validated['name'] ?? $user->full_name ?? $user->name ?? ''));
    if ($resolvedName === '') {
        return redirect('/profile')->withErrors(['name' => 'Name is required.']);
    }

    $emailChanged = $user->email !== $validated['email'];

    $user->full_name = $resolvedName;
    $user->name = $resolvedName;
    $user->email = $validated['email'];
    if ($emailChanged) {
        $user->email_verified_at = null;
    }
    if ($request->hasFile('profile_photo')) {
        $file = $request->file('profile_photo');
        $path = $file->store('profile_photos', 'public');
        $user->profile_photo_path = $path;
    }
    $user->save();
    return redirect('/profile')->with('status', 'profile-updated');
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
