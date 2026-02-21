<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    })->name('login.post');

    Route::get('register', function () {
        $offices = \App\Models\RegistrationOffice::query()
            ->where('status', 'active')
            ->orderBy('region')
            ->orderBy('office_name')
            ->get(['id', 'office_name', 'region']);

        return view('auth.register', compact('offices'));
    })->name('register');

    Route::post('register', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'registration_office_id' => ['required', 'exists:registration_offices,id'],
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'registration_office_id' => $request->registration_office_id,
            'role' => 'citizen',
            'status' => 'active',
            'is_approved' => false,
            'is_system_admin' => false,
            'email_verified_at' => now(),
        ]);

        Auth::login($user);
        return redirect()->intended('dashboard');
    })->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function (\Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});

