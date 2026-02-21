<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show change password form
     */
    public function changePassword()
    {
        return view('profile.change-password');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) {
                    /** @var \App\Models\User $user */
                    $user = auth()->user();
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
            'new_password' => [
                'required',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                'confirmed',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value === $request->get('current_password')) {
                        $fail('The new password must be different from the current password.');
                    }
                },
            ],
            'new_password_confirmation' => 'required',
        ], [
            'new_password.regex' => 'Password must contain uppercase, lowercase, numbers, and special characters (@$!%*?&).',
        ]);

        // Update password
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->route('dashboard')
                       ->with('success', '✅ Password changed successfully!');
    }
}
