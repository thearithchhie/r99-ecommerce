<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // User permissions
        $userPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'restore users',
        ];
        
        // Product permissions
        $productPermissions = [
            'view products',
            'create products',
            'edit products',
            'delete products',
            'restore products',
        ];
        
        // Order permissions
        $orderPermissions = [
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'process orders',
            'cancel orders',
        ];
        
        // Customer permissions
        $customerPermissions = [
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
        ];
        
        // Role permissions
        $rolePermissions = [
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign permissions',
        ];
        
        // Permission permissions
        $permissionPermissions = [
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
        ];
        
        // System permissions
        $systemPermissions = [
            'view system settings',
            'edit system settings',
            'view logs',
            'run maintenance',
        ];
        
        // Create permissions
        $allPermissions = array_merge(
            $userPermissions,
            $productPermissions,
            $orderPermissions,
            $customerPermissions,
            $rolePermissions,
            $permissionPermissions,
            $systemPermissions
        );
        
        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Create roles and assign permissions
        
        // Super Admin role - has all permissions
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());
        
        // Admin role - has all permissions except some system ones
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(array_diff($allPermissions, [
            'run maintenance',
            'delete permissions',
            'delete roles',
        ]));
        
        // Manager role - can manage products, orders, customers
        $managerRole = Role::create(['name' => 'Manager']);
        $managerRole->givePermissionTo(array_merge(
            $productPermissions,
            $orderPermissions,
            $customerPermissions,
            ['view users']
        ));
        
        // Sales role - can view products, manage orders and customers
        $salesRole = Role::create(['name' => 'Sales']);
        $salesRole->givePermissionTo(array_merge(
            ['view products'],
            $orderPermissions,
            $customerPermissions
        ));
        
        // Support role - can view and process orders, view customers
        $supportRole = Role::create(['name' => 'Support']);
        $supportRole->givePermissionTo([
            'view orders',
            'edit orders',
            'process orders',
            'view customers',
        ]);
        
        $this->command->info('Roles and permissions created successfully!');
    }
}
