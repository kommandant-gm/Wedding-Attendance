<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login');

Route::post('/login', function () {
    // Simple login logic
    request()->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    if (Auth::attempt(request()->only('email', 'password'))) {
        request()->session()->regenerate();
        return redirect('/dashboard');
    }
    
    return back()->withErrors(['email' => 'Invalid credentials']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    });
    
    Route::get('/scan', function () {
        return Inertia::render('GuestScan');
    });

    Route::get('/attendance', function () {
        return Inertia::render('Attendance');
    });
    
    Route::get('/reports', function () {
        return Inertia::render('Reports');
    });
    
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    });
});

Route::get('/', function () {
    return redirect('/login');
});
