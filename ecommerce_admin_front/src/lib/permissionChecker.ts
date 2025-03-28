import axios from './axios';

// Cache for permission checks to avoid multiple requests for the same permission
const permissionCache: Record<string, boolean> = {};
let isAdminCache: boolean | null = null;

/**
 * Check if the current user is an admin
 * @returns Promise resolving to a boolean indicating if the user is an admin
 */
export async function isAdmin(): Promise<boolean> {
  // Return from cache if available
  if (isAdminCache !== null) {
    return isAdminCache;
  }

  try {
    const response = await axios.get('/user-profile');
    
    // Check if the user has is_admin flag set to true
    const isAdmin = response.data?.data?.is_admin === true;
    
    // Cache the result
    isAdminCache = isAdmin;
    
    return isAdmin;
  } catch (error) {
    console.error('Error checking admin status:', error);
    return false;
  }
}

/**
 * Check if the current user has a specific permission
 * @param permission The permission name to check
 * @returns Promise resolving to a boolean indicating if the user has the permission
 */
export async function hasPermission(permission: string): Promise<boolean> {
  // Check if user is admin first (admins have all permissions)
  if (await isAdmin()) {
    return true;
  }
  
  // Return from cache if available
  if (permissionCache[permission] !== undefined) {
    return permissionCache[permission];
  }

  try {
    const response = await axios.post('/check-permission', {
      permission
    });

    const hasPermission = response.data?.data?.hasPermission || false;
    
    // Cache the result
    permissionCache[permission] = hasPermission;
    
    return hasPermission;
  } catch (error) {
    //console.error(`Error checking permission ${permission}:`, error);
    return false;
  }
}

/**
 * Clear the permission cache (useful when logging out or changing roles)
 */
export function clearPermissionCache(): void {
  // Clear permission cache
  Object.keys(permissionCache).forEach(key => {
    delete permissionCache[key];
  });
  
  // Reset admin status
  isAdminCache = null;
}

/**
 * Map of routes to required permissions
 * Add new entries as needed for new routes that require specific permissions
 */
export const routePermissionMap: Record<string, string> = {
  '/users': 'view users',
  '/roles': 'view roles',
  '/permissions': 'view permissions',
  '/user-roles': 'assign roles',
  '/products': 'view products',
  '/orders': 'view orders',
  '/customers': 'view customers',
};

/**
 * Check if the user has permission to access a specific route
 * @param path The route path
 * @returns Promise resolving to a boolean indicating if the user has access
 */
export async function canAccessRoute(path: string): Promise<boolean> {
  // Admin users can access all routes
  if (await isAdmin()) {
    return true;
  }
  
  // Routes that don't require permissions are accessible to all authenticated users
  if (!routePermissionMap[path]) {
    return true;
  }

  // Check the required permission for this route
  return await hasPermission(routePermissionMap[path]);
} 