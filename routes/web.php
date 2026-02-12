<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('spa');
})->name('home');

Route::get('/login', function () {
    return redirect('/app/login');
})->name('login');

Route::get('/register', function () {
    return redirect('/app/register');
})->name('register');

Route::get('dashboard', function () {
    return redirect('/app');
})->name('dashboard');

Route::get('app/{any?}', function () {
    return view('spa');
})->where('any', '.*')->name('spa');

require __DIR__.'/settings.php';
