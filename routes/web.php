<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::controller(\App\Http\Controllers\AuthController::class)->group(function () {
	Route::get('/login', 'getLogin')->name('auth.login')->middleware('guest');
	Route::get('/logout', 'getLogout')->name('auth.logout');
	Route::get('/auth/redirect', 'getRedirect')->name('auth.redirect');
	Route::get('/auth/callback', 'getCallback')->name('auth.callback');
	Route::post('/auth/quickcode', 'postQuickcode')->name('auth.quickcode.post');
});

Route::get('/', function () {
	return view('layouts.master');
})->middleware('auth');
