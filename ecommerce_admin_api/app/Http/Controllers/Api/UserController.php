<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

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
        
        // Get paginated users, explicitly excluding soft deleted users
        $users = User::whereNull('deleted_at')->paginate($perPage, ['*'], 'page', $page);
        
        // Create response with just the user items
        $response = ApiResponse::ok([
            'users' => $users->items()
        ], 'Users retrieved successfully', [], StatusCode::OK);
        
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
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'username' => $validated['username'],
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
        $user = User::whereNull('deleted_at')->find($id);
        
        if (!$user) {
            return ApiResponse::notFound('User not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }

        return ApiResponse::ok($user, 'User retrieved successfully', [], StatusCode::OK);
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
        // Check if the email has changed before running the unique check
        $rules = [
            "username" => "string|min:3",
            "password" => "nullable",
        ];

        // Custom error messages
        $messages = [
            'email.unique' => 'this email is already already',
        ];

        $user = User::whereNull('deleted_at')->find($id);
        if (!$user) {
            return ApiResponse::notFound('User not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }

        // Only validate unique email if it's different from the current user's email
        if ($request->email !== $user->email) {
            $rules['email'] = [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ];
        }

        // Validate in coming request
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }

        // Get validated data
        $validated = $validator->validated();
        
       

        // i want validate email if it is unique
        if (isset($validated['email'])) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ]);

            if ($validator->fails()) {
                return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
            }
        }

        if (isset($validated['username'])) {
            $user->username = $validated['username'];
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
     * Remove the specified resource from storage (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::whereNull('deleted_at')->find($id);
        
        if (!$user) {
            return ApiResponse::notFound('User not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }

        // This will now perform a soft delete since the SoftDeletes trait is used in the User model
        $user->delete();

        return ApiResponse::ok(null, 'User deleted successfully', [], StatusCode::STATUS_DELETE_USER_SUCCESSFULLY);
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        // Use withTrashed to find the user even if they're soft-deleted
        $user = User::withTrashed()->find($id);
        
        if (!$user) {
            return ApiResponse::notFound('User not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }

        if (!$user->trashed()) {
            return ApiResponse::badRequest('User is not deleted', null, StatusCode::STATUS_BAD_REQUEST_400);
        }

        $user->restore();

        return ApiResponse::ok($user, 'User restored successfully', [], StatusCode::OK);
    }

    /**
     * List all soft-deleted users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trashed()
    {
        $users = User::onlyTrashed()->get();
        
        return ApiResponse::ok(['users' => $users], 'Trashed users retrieved successfully', [], StatusCode::OK);
    }

    public function userProfile(Request $request)
    {
        //! built-in user() method
        $user = $request->user(); //This retrieves the currently authenticated user.
        return ApiResponse::ok($user, 'User profile retrieved successfully', [], StatusCode::OK);
    }


} 