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
        unset($data['avatar']);

        $oldAvatar = $user->avatar_url ?? null;
        $newAvatarPath = null;

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (! $file->isValid() || ! in_array($file->getMimeType(), $allowed, true)) {
                return Redirect::back()
                    ->withInput($request->except('avatar'))
                    ->withErrors(['avatar' => __('profile.avatar.error_invalid_type')]);
            }

            try {
                $newAvatarPath = $file->store('avatar', 'public');
            } catch (\Throwable $e) {
                report($e);

                return Redirect::back()
                    ->withInput($request->except('avatar'))
                    ->withErrors(['avatar' => __('profile.avatar.error_upload_failed')]);
            }

            if (! $newAvatarPath) {
                return Redirect::back()
                    ->withInput($request->except('avatar'))
                    ->withErrors(['avatar' => __('profile.avatar.error_upload_failed')]);
            }

            $data['avatar_url'] = $newAvatarPath;
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($newAvatarPath && $oldAvatar) {
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
