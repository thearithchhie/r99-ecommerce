<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;

class AuthController extends Controller
{
    /**
     * Handle API login request
     * 
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            $request->authenticate();
            
            /** @var \App\Models\User&\Laravel\Sanctum\HasApiTokens $user */
            $user = Auth::user();
            
            // First check if tokens method exists before trying to use it
            if (method_exists($user, 'tokens')) {
                // Revoke old tokens
                $user->tokens()->delete();
                
                // Create new token
                $token = $user->createToken('api-token')->plainTextToken;
                
                return ApiResponse::ok([
                    // 'user' => $user,
                    'token' => $token
                ], 'Login successful', [], StatusCode::STATUS_LOGIN_SUCCESS);
            } else {
                // Fallback for when Sanctum is not fully configured
                return ApiResponse::ok([
                    'user' => $user
                ], 'Login successful (token creation unavailable)', [], StatusCode::STATUS_LOGIN_SUCCESS_NO_TOKEN);
            }
            
        } catch (ValidationException $e) {
            return ApiResponse::validationError('The provided credentials are incorrect.', $e->errors(), StatusCode::STATUS_LOGIN_INVALID_CREDENTIALS);
        }
    }
    
    /**
     * Handle API logout request
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Only attempt to delete tokens if the method exists
        if (method_exists($request->user(), 'tokens')) {
            $request->user()->tokens()->delete();
        }
        
        return ApiResponse::ok(null, 'Logged out successfully', [], StatusCode::STATUS_LOGOUT_SUCCESS);
    }
} 