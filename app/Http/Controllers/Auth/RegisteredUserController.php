<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Services\Notifications\PlatformNotificationService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['nullable', 'in:student,teacher'],
        ]);

        $userType = $request->input('user_type', User::ROLE_STUDENT);

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $userType,
            'is_active' => $userType !== User::ROLE_TEACHER,
            'configuration_compt_eleve' => false,
        ]);

        // Assign role based on user type
        try {
            if ($userType === User::ROLE_STUDENT) {
                $newUser->assignRole(User::ROLE_STUDENT);
            } elseif ($userType === User::ROLE_TEACHER) {
                $newUser->assignRole(User::ROLE_TEACHER);
            }
        } catch (\Throwable $e) {}

        event(new Registered($newUser));

        app(PlatformNotificationService::class)->notifyUserRegistered($newUser);

        if ($userType === User::ROLE_TEACHER) {
            return redirect()->route('login')
                ->with('status', 'Votre compte enseignant est en attente de validation par un administrateur.');
        }

        Auth::login($newUser);

        // update last login timestamp for the freshly registered user
        $newUser->update(['last_login_at' => now()]);

        return redirect()->route('student-profile.show');
    }
}
