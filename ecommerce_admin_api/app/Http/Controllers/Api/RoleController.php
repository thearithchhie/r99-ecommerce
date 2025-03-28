<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Constants\StatusCode;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        
        return ApiResponse::ok(['roles' => $roles], 'Roles retrieved successfully', [], StatusCode::OK);
    }

    /**
     * Store a newly created role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }

        $validated = $validator->validated();
        
        // Create role
        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        
        // Assign permissions to the role
        if (isset($validated['permissions']) && !empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }
        
        return ApiResponse::created($role->load('permissions'), 'Role created successfully', [], StatusCode::OK);
    }

    /**
     * Display the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $role = Role::with('permissions')->find($id);
        
        if (!$role) {
            return ApiResponse::notFound('Role not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }
        
        return ApiResponse::ok($role, 'Role retrieved successfully', [], StatusCode::OK);
    }

    /**
     * Update the specified role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return ApiResponse::notFound('Role not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }

        $validated = $validator->validated();
        
        // Update role name if provided
        if (isset($validated['name'])) {
            $role->name = $validated['name'];
            $role->save();
        }
        
        // Sync permissions if provided
        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }
        
        return ApiResponse::ok($role->load('permissions'), 'Role updated successfully', [], StatusCode::OK);
    }

    /**
     * Remove the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return ApiResponse::notFound('Role not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }
        
        // Check if it's a system role (e.g., 'Super Admin')
        if ($role->name === 'Super Admin') {
            return ApiResponse::badRequest('Cannot delete the Super Admin role', null, StatusCode::STATUS_BAD_REQUEST_400);
        }
        
        $role->delete();
        
        return ApiResponse::ok(null, 'Role deleted successfully', [], StatusCode::OK);
    }
    
    /**
     * Get all available permissions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissions()
    {
        $permissions = Permission::all();
        
        return ApiResponse::ok(['permissions' => $permissions], 'Permissions retrieved successfully', [], StatusCode::OK);
    }
    
    /**
     * Assign permissions to a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return ApiResponse::notFound('Role not found', null, StatusCode::STATUS_GET_USER_NOT_FOUND);
        }
        
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), StatusCode::STATUS_VALIDATION_ERROR_422);
        }

        $validated = $validator->validated();
        
        $permissions = Permission::whereIn('id', $validated['permissions'])->get();
        $role->syncPermissions($permissions);
        
        return ApiResponse::ok($role->load('permissions'), 'Permissions assigned successfully', [], StatusCode::OK);
    }
}
