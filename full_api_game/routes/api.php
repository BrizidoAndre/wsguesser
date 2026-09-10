<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/games', [\App\Http\Controllers\GameController::class, 'game']);
Route::get('/games/{game}', [\App\Http\Controllers\GameController::class, 'show'])->missing(function () {
    throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
        'message' => 'The specified game does not exist.'
    ], 404));
});

Route::post('/games/{game}/guesses', [\App\Http\Controllers\GameController::class, 'guess']);
