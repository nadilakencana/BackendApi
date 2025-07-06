<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryChatController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/chat', [HistoryChatController::class, 'sendMessage']);
    Route::get('/sessions', [HistoryChatController::class, 'getSessions']);
    Route::get('/sessions/{sessionId}/history', [HistoryChatController::class, 'getHistory']);
});

require __DIR__.'/auth.php';