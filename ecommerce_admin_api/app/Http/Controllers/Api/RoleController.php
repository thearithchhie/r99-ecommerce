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
        
        return ApiResponse::ok(['roles' => $roles], 'Roles retrieved successfully', [], 200);
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
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();
        
        // Create role
        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        
        // Assign permissions to the role
        if (isset($validated['permissions']) && !empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }
        
        return ApiResponse::created($role->load('permissions'), 'Role created successfully', [], 200);
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
            return ApiResponse::notFound('Role not found', null, 404);
        }
        
        return ApiResponse::ok($role, 'Role retrieved successfully', [], 200);
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
            return ApiResponse::notFound('Role not found', null, 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), 422);
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
        
        return ApiResponse::ok($role->load('permissions'), 'Role updated successfully', [], 200);
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
            return ApiResponse::notFound('Role not found', null, 404);
        }
        
        // Check if it's a system role (e.g., 'Super Admin')
        if ($role->name === 'Super Admin') {
            return ApiResponse::badRequest('Cannot delete the Super Admin role', null, 400);
        }
        
        $role->delete();
        
        return ApiResponse::ok(null, 'Role deleted successfully', [], 200);
    }
    
    /**
     * Get all available permissions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissions()
    {
        $permissions = Permission::all();
        
        return ApiResponse::ok(['permissions' => $permissions], 'Permissions retrieved successfully', [], 200);
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
            return ApiResponse::notFound('Role not found', null, 404);
        }
        
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError('Validation failed', $validator->errors()->toArray(), 422);
        }

        $validated = $validator->validated();
        
        $permissions = Permission::whereIn('id', $validated['permissions'])->get();
        $role->syncPermissions($permissions);
        
        return ApiResponse::ok($role->load('permissions'), 'Permissions assigned successfully', [], 200);
    }
}
