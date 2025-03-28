<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $permissions = Permission::all();
        
        return ApiResponse::ok(['permissions' => $permissions], 'Permissions retrieved successfully', [], StatusCode::OK);
    }

    /**
     * Store a newly created permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }

        $validated = $validator->validated();
        
        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);
        
        return ApiResponse::created($permission, 'Permission created successfully', [], StatusCode::OK);
    }

    /**
     * Display the specified permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $permission = Permission::find($id);
        
        if (!$permission) {
            return ApiResponse::notFound('Permission not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }
        
        return ApiResponse::ok($permission, 'Permission retrieved successfully', [], StatusCode::OK);
    }

    /**
     * Update the specified permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);
        
        if (!$permission) {
            return ApiResponse::notFound('Permission not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }

        $validated = $validator->validated();
        
        $permission->name = $validated['name'];
        $permission->save();
        
        return ApiResponse::ok($permission, 'Permission updated successfully', [], StatusCode::OK);
    }

    /**
     * Remove the specified permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $permission = Permission::find($id);
        
        if (!$permission) {
            return ApiResponse::notFound('Permission not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }
        
        // Check if this permission is assigned to any roles before deletion
        if ($permission->roles()->count() > 0) {
            return ApiResponse::badRequest('This permission is assigned to one or more roles and cannot be deleted', null, StatusCode::STATUS_BAD_REQUEST_400);
        }
        
        $permission->delete();
        
        return ApiResponse::ok(null, 'Permission deleted successfully', [], StatusCode::OK);
    }
    
    /**
     * Get roles with this permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function roles($id)
    {
        $permission = Permission::with('roles')->find($id);
        
        if (!$permission) {
            return ApiResponse::notFound('Permission not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }
        
        return ApiResponse::ok(['roles' => $permission->roles], 'Roles with this permission retrieved successfully', [], StatusCode::OK);
    }
}
