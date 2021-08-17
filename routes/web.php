<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', function () {
    return view('home');
});
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/sendMessage', [App\Http\Controllers\HomeController::class, 'sendMessage']);

Route::get('/wsMonitor', [App\Http\Controllers\wsMonitor::class, 'index']);
Route::post('/wsMonitor/sendNotification', [App\Http\Controllers\wsMonitor::class, 'sendNotification']);
Route::post('/wsMonitor/getLog', [App\Http\Controllers\wsMonitor::class, 'getLog']);