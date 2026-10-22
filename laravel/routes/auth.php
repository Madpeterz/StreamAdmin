<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'auth.login')
        ->name('login');
    Volt::route('forgot-password', 'auth.forgot-password')
        ->name('password.request');
    Volt::route('reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');
});

Route::post('logout', Logout::class)
    ->name('logout');
