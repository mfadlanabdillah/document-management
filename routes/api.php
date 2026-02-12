<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentVersionController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\MasterDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

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

Route::post('/auth/register', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $name = Str::before($validated['email'], '@');
    if ($name === '') {
        $name = 'user';
    }
    $firstUser = !User::query()->exists();

    $user = User::create([
        'name' => $name,
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => $firstUser ? 'admin' : 'user',
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
    ], 201);
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::get('/auth/me', function (Request $request) {
        return response()->json([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'role' => $request->user()->role,
        ]);
    });

    Route::get('/documents', [DocumentController::class, 'index']);
    Route::get('/documents/trash', [DocumentController::class, 'trash']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::put('/documents/{document}', [DocumentController::class, 'update']);
    Route::patch('/documents/{document}', [DocumentController::class, 'updateStatus']);
    Route::patch('/documents/{document}/status', [DocumentController::class, 'updateStatus']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    Route::post('/documents/{document}/restore', [DocumentController::class, 'restore'])->withTrashed();

    Route::get('/documents/{document}/versions', [DocumentVersionController::class, 'index']);
    Route::post('/documents/{document}/versions', [DocumentVersionController::class, 'store']);
    Route::get('/documents/{document}/versions/{version}/download', [DocumentVersionController::class, 'download']);
    Route::get('/documents/{document}/activities', [DocumentController::class, 'activities']);

    Route::get('/categories', function () {
        return \App\Models\Category::select('id', 'name')
            ->orderBy('name')
            ->get();
    });

    Route::get('/tags', function () {
        return \App\Models\Tag::select('id', 'name')
            ->orderBy('name')
            ->get();
    });

    Route::get('/document-statuses', [MasterDataController::class, 'statusIndex']);

    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::get('/users/audit-logs', [UserManagementController::class, 'auditLogs']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::get('/users/{user}', [UserManagementController::class, 'show']);
        Route::patch('/users/{user}', [UserManagementController::class, 'update']);
        Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole']);
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);
    });

    Route::middleware('can:manage-master-data')->group(function () {
        Route::get('/master/status-transitions', [MasterDataController::class, 'statusTransitionIndex']);
        Route::patch('/master/statuses/{status}/transitions', [MasterDataController::class, 'statusTransitionUpdate']);
        Route::post('/master/statuses', [MasterDataController::class, 'statusStore']);
        Route::patch('/master/statuses/{status}', [MasterDataController::class, 'statusUpdate']);
        Route::delete('/master/statuses/{status}', [MasterDataController::class, 'statusDestroy']);

        Route::get('/master/categories', [MasterDataController::class, 'categoryIndex']);
        Route::post('/master/categories', [MasterDataController::class, 'categoryStore']);
        Route::patch('/master/categories/{category}', [MasterDataController::class, 'categoryUpdate']);
        Route::delete('/master/categories/{category}', [MasterDataController::class, 'categoryDestroy']);

        Route::get('/master/tags', [MasterDataController::class, 'tagIndex']);
        Route::post('/master/tags', [MasterDataController::class, 'tagStore']);
        Route::patch('/master/tags/{tag}', [MasterDataController::class, 'tagUpdate']);
        Route::delete('/master/tags/{tag}', [MasterDataController::class, 'tagDestroy']);
    });


});
