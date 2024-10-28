<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RtpmController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'stream'], function () {
    Route::any('start', [ApiController::class, 'streamStart']);
    Route::any('stop', [ApiController::class, 'streamStop']);
    Route::any('record', [ApiController::class, 'streamRecord']);
    Route::any('blocked', [ApiController::class, 'streamBlocked']);

    Route::get('live/{id}', [ApiController::class, 'shareLive'])->name('live.share');
    Route::get('record/{id}', [ApiController::class, 'shareRecord'])->name('record.share');
    Route::get('filter/s3', [ApiController::class, 'streamS3Bucket']);
});
