<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RtpmController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\HomeController;

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

Route::get('/', function () {
    return view('home');
});

Auth::routes(['verify' => true]);

Route::group(['prefix' => 'admin'], function(){
    Route::resource("rtmps", RtpmController::class);
    Route::get('/home', [RtpmController::class, 'home'])->name('admin.home');
    Route::get('/videos/{stream_key}', [RtpmController::class, 'videos'])->name('temple.videos');

    Route::group(['prefix' => 'video'], function(){
        Route::get('/shows/{id}', [RtpmController::class, 'shows'])->name('video.show');
        Route::delete('/delete/{id}', [RtpmController::class, 'delete'])->name('video.delete');
    });
});

Route::get('/home', [HomeController::class, 'index'])->name('home');
