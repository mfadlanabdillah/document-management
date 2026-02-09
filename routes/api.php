<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentVersionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::post('/auth/login', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token
    ]);
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {

    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::put('/documents/{document}', [DocumentController::class, 'update']);
    Route::patch('/documents/{document}', [DocumentController::class, 'updateStatus']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    Route::post('/documents/{document}/restore', [DocumentController::class, 'restore']);

    Route::get('/documents/{document}/versions', [DocumentVersionController::class, 'index']);
    Route::post('/documents/{document}/versions', [DocumentVersionController::class, 'store']);
    Route::get('/documents/{document}/versions/{version}/download', [DocumentVersionController::class, 'download']);
    Route::get('/documents/{document}/activities', [DocumentController::class, 'activities']);

});
