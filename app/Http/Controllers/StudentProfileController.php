<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->isStudent()) {
            return redirect()->route('home');
        }

        $profile = $user->studentProfile ?? new StudentProfile(['user_id' => $user->id]);

        return view('profile.student-config', [
            'profile' => $profile,
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->isStudent()) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'education_level' => 'required|string',
            'bac_type' => 'nullable|in:marocain,mission',
            'bac_field' => 'nullable|string',
            'school_name' => 'required|string|max:255',
            'school_type' => 'required|in:public,private',
            'preferred_school_types' => 'required|array|min:1',
            'interested_services' => 'nullable|array',
            'birth_date' => 'required|date',
            'gender' => 'required|in:masculine,feminine',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'consent_contact' => 'required|boolean',
        ]);

        $phone = $validated['phone'] ?? null;
        unset($validated['phone']);

        $profile = $user->studentProfile ?? new StudentProfile(['user_id' => $user->id]);
        $profile->fill($validated);
        $profile->is_complete = true;
        $profile->save();

        $user->update([
            'configuration_compt_eleve' => true,
            'phone' => $phone ?: $user->phone,
            'city' => $validated['city'],
        ]);

        return redirect()
            ->route('filament.admin.pages.dashboard')
            ->with('success', 'Votre profil eleve a ete configure avec succes.');
    }

    public static function redirectAfterRegister($user)
    {
        if ($user->isStudent() && ! $user->configuration_compt_eleve) {
            return redirect()
                ->route('student-profile.show')
                ->with('alert', 'Veuillez completer votre profil eleve pour acceder a la plateforme.');
        }

        return redirect()->route('filament.admin.pages.dashboard');
    }
}
