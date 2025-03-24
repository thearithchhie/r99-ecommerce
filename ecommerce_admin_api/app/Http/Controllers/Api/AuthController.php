<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

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
                
                return response()->json([
                    'user' => $user,
                    'token' => $token,
                    'message' => 'Login successful'
                ]);
            } else {
                // Fallback for when Sanctum is not fully configured
                return response()->json([
                    'user' => $user,
                    'message' => 'Login successful (token creation unavailable)'
                ]);
            }
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => $e->errors(),
            ], 422);
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
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
} 