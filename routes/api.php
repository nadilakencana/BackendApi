<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryChatController;
use App\Models\HistoryChat;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(HistoryChat::class)->group(function(){
    Route::post('chat', 'sendMessage');
    Route::get('session', 'getSession');
    ROute::get('session/{sessionId}/history', 'getHistory');
});
