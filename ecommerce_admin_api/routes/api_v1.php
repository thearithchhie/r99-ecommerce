<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use Illuminate\Support\Facades\Log;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Permission check endpoint for route guards
    Route::post('/check-permission', [UserController::class, 'checkPermission']);

    // Current user permissions
    Route::get('/my-permissions', [UserController::class, 'myPermissions']);

    // User Modules
    //!Noted: When we are apply permission for specification role
    // Route::get('/users', [UserController::class, 'index'])
    //     ->middleware('role_or_permission:Support|Admin|view users');

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:view users');

    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create users');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:view users');
    Route::patch('/users/{id}', [UserController::class, 'update'])->middleware('permission:update users');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:delete users');
    Route::get('/user-profile', [UserController::class, 'userProfile']);
    
    // User Role Management
    Route::get('/users/{id}/roles', [UserController::class, 'roles'])->middleware('permission:view roles');
    Route::get('/users/{id}/permissions', [UserController::class, 'permissions'])->middleware('permission:view permissions');
    Route::post('/users/{id}/roles', [UserController::class, 'assignRole'])->middleware('permission:assign roles');
    Route::delete('/users/{id}/roles', [UserController::class, 'removeRole'])->middleware('permission:assign roles');
    Route::put('/users/{id}/roles', [UserController::class, 'syncRoles'])->middleware('permission:assign roles');
    Route::post('/users/{id}/check-role', [UserController::class, 'hasRole']);
    Route::post('/users/{id}/check-permission', [UserController::class, 'hasPermission']);
    
    // Soft delete related routes
    Route::get('/users/trashed', [UserController::class, 'trashed'])->middleware('permission:view users');
    Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->middleware('permission:restore users');
    
    // Roles Management
    Route::apiResource('roles', RoleController::class)->middleware('permission:view roles');
    Route::get('/roles/{id}/permissions', [RoleController::class, 'permissions'])->middleware('permission:view roles');
    Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions'])->middleware('permission:assign permissions');
    
    // Permissions Management
    Route::apiResource('permissions', PermissionController::class)->middleware('permission:view permissions');
    Route::get('/permissions/{id}/roles', [PermissionController::class, 'roles'])->middleware('permission:view permissions');
}); 