<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('v1')->group(function (): void {
    Route::post('users', [\App\Http\Controllers\API\UserController::class, 'store'])
        ->name('api.v1.users.store');

    Route::middleware(['web', 'permission:users.api.manage'])
        ->apiResource('users', \App\Http\Controllers\API\UserController::class)
        ->except(['store']);
});
