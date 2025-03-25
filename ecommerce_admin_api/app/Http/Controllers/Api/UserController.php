<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get pagination parameters from the request
        $perPage = (int) $request->input('per_page', 10);
        $page = (int) $request->input('page', 1);
        
        // Apply limits to prevent excessive requests
        $perPage = min(max($perPage, $perPage), 100); // Between 5 and 100 items per page
        
        // Get paginated users
        $users = User::paginate($perPage, ['*'], 'page', $page);
        
        // Create response with just the user items
        $response = ApiResponse::ok([
            'users' => $users->items()
        ], 'Users retrieved successfully', [], StatusCode::OK_200);
        
        // Add pagination metadata
        return $response->withPagination($users);
    }

    /**
     * Store a newly created user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return ApiResponse::created($user, 'User created successfully', [], StatusCode::STATUS_CREATED_USER_SUCCESSFULLY);
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ApiResponse::notFound('User not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }

        return ApiResponse::ok($user, 'User retrieved successfully', [], StatusCode::OK_200);
    }

    /**
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ApiResponse::notFound('User not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|string|min:8',
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return ApiResponse::ok($user, 'User updated successfully', [], StatusCode::STATUS_UPDATE_USER_SUCCESSFULLY);
    }

    /**
     * Remove the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ApiResponse::notFound('User not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }

        $user->delete();

        return ApiResponse::ok(null, 'User deleted successfully', [], StatusCode::STATUS_DELETE_USER_SUCCESSFULLY);
    }
} 