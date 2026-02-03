<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

    $data = $request->validated();

    // capture current avatar so we can delete it after storing new one
    $oldAvatar = $user->avatar_url ?? null;

        // Handle avatar upload if present
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Double-check mime type server-side
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (! in_array($file->getMimeType(), $allowed, true)) {
                // invalid mime, ignore the upload and add an error
                return Redirect::back()->withErrors(['avatar' => __('profile.avatar.error_invalid_type')]);
            }

            try {
                // store the file on the public disk, in avatar/ directory; store() generates a safe unique name
                $path = $file->store('avatar', 'public');
                // set the new path so it will be saved on the user (public disk relative path)
                $data['avatar_url'] = $path;
            } catch (\Throwable $e) {
                // If storage fails, log and continue without blocking the profile update
                report($e);
            }
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // If we stored a new avatar successfully, delete the old file now (non-blocking)
        if (isset($path) && !empty($path) && $oldAvatar) {
            try {
                $user->removeAvatarFile($oldAvatar);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
