<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Role Management</h1>
      <Button @click="openCreateModal">Create New Role</Button>
    </div>

    <!-- Loading state -->
    <div v-if="isLoading" class="flex justify-center my-12">
      <RefreshCw class="h-8 w-8 animate-spin text-primary" />
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="p-4 bg-red-50 text-red-700 rounded-md mb-6">
      <p>{{ error }}</p>
      <Button variant="outline" class="mt-2" @click="fetchRoles">Try Again</Button>
    </div>

    <!-- Role List Table -->
    <div v-else-if="roles.length" class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">{{ role.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ role.name }}</td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span 
                  v-for="permission in role.permissions" 
                  :key="permission.id"
                  class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800"
                >
                  {{ permission.name }}
                </span>
                <span v-if="!role.permissions.length" class="text-gray-400 text-sm italic">None</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex space-x-2">
                <Button variant="outline" size="sm" @click="handleEdit(role)">
                  <Pencil class="h-4 w-4" />
                </Button>
                <Button variant="outline" size="sm" @click="handlePermissions(role)">
                  <Key class="h-4 w-4" />
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  :disabled="role.name === 'Super Admin'"
                  @click="handleDelete(role)"
                  class="text-red-600 hover:bg-red-50"
                >
                  <Trash class="h-4 w-4" />
                </Button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state -->
    <div v-else class="p-6 text-center bg-white rounded-lg shadow-md">
      <h3 class="text-lg font-medium text-gray-900 mb-2">No roles found</h3>
      <p class="text-gray-500 mb-4">Get started by creating a new role.</p>
      <Button @click="openCreateModal">Create New Role</Button>
    </div>

    <!-- Create/Edit Role Modal -->
    <Dialog v-if="showRoleModal" :open="showRoleModal" @update:open="showRoleModal = $event">
      <DialogContent class="sm:max-w-[425px]" @close="closeRoleModal()">
        <DialogHeader>
          <DialogTitle>{{ editing ? 'Edit Role' : 'Create New Role' }}</DialogTitle>
          <DialogDescription>
            {{ editing ? 'Update role details below.' : 'Enter details for the new role.' }}
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitRoleForm">
          <div class="grid gap-4 py-4">
            <div class="grid grid-cols-4 items-center gap-4">
              <Label for="name" class="text-right">Name</Label>
              <Input
                id="name"
                v-model="roleForm.name"
                class="col-span-3"
                :class="{ 'border-red-500': formErrors.name }"
              />
            </div>
            <div v-if="formErrors.name" class="text-red-500 text-sm ml-[33%]">
              {{ formErrors.name }}
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeRoleModal()">
              Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting">
              <RefreshCw v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
              {{ editing ? 'Update' : 'Create' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Permissions Modal -->
    <Dialog v-if="showPermissionsModal" :open="showPermissionsModal" @update:open="showPermissionsModal = $event">
      <DialogContent class="sm:max-w-[600px]" @close="closePermissionsModal()">
        <DialogHeader>
          <DialogTitle>Manage Permissions for {{ selectedRole?.name }}</DialogTitle>
          <DialogDescription>
            Select the permissions you want to assign to this role.
          </DialogDescription>
        </DialogHeader>
        <div v-if="isLoadingPermissions" class="flex justify-center my-4">
          <RefreshCw class="h-6 w-6 animate-spin text-primary" />
        </div>
        <div v-else class="py-4">
          <div class="grid grid-cols-2 gap-4 max-h-[400px] overflow-y-auto">
            <div v-for="(group, category) in groupedPermissions" :key="category" class="space-y-2">
              <h3 class="font-medium text-sm uppercase text-gray-500">{{ category }}</h3>
              <div v-for="permission in group" :key="permission.id" class="flex items-center space-x-2">
                <Checkbox 
                  :id="`permission-${permission.id}`" 
                  :checked="isPermissionSelected(permission.id)" 
                  @update:checked="togglePermission(permission.id)"
                />
                <Label :for="`permission-${permission.id}`" class="cursor-pointer">
                  {{ permission.name }}
                </Label>
              </div>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="closePermissionsModal()">
            Cancel
          </Button>
          <Button @click="updatePermissions" :disabled="isSubmittingPermissions">
            <RefreshCw v-if="isSubmittingPermissions" class="mr-2 h-4 w-4 animate-spin" />
            Save Permissions
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Modal -->
    <Dialog v-if="showDeleteModal" :open="showDeleteModal" @update:open="showDeleteModal = $event">
      <DialogContent class="sm:max-w-[425px]" @close="closeDeleteModal()">
        <DialogHeader>
          <DialogTitle>Delete Role</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete the role "{{ selectedRole?.name }}"? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button type="button" variant="outline" @click="closeDeleteModal()">
            Cancel
          </Button>
          <Button 
            variant="destructive" 
            @click="confirmDelete" 
            :disabled="isDeleting"
          >
            <RefreshCw v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from '@/lib/axios';
import { useToast } from '@/components/ui/toast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { RefreshCw, Pencil, Key, Trash } from 'lucide-vue-next';

// Interfaces
interface Role {
  id: number;
  name: string;
  permissions: Permission[];
}

interface Permission {
  id: number;
  name: string;
}

// State
const toast = useToast();
const roles = ref<Role[]>([]);
const isLoading = ref<boolean>(true);
const error = ref<string | null>(null);

// Role Modal State
const showRoleModal = ref<boolean>(false);
const editing = ref<boolean>(false);
const roleForm = ref({ name: '' });
const formErrors = ref({ name: '' });
const isSubmitting = ref<boolean>(false);
const selectedRole = ref<Role | null>(null);

// Permissions Modal State
const showPermissionsModal = ref<boolean>(false);
const allPermissions = ref<Permission[]>([]);
const selectedPermissions = ref<number[]>([]);
const isLoadingPermissions = ref<boolean>(false);
const isSubmittingPermissions = ref<boolean>(false);

// Delete Modal State
const showDeleteModal = ref<boolean>(false);
const isDeleting = ref<boolean>(false);

// Computed property to group permissions by category
const groupedPermissions = computed(() => {
  const grouped: Record<string, Permission[]> = {};
  
  allPermissions.value.forEach(permission => {
    // Extract the category from permission name (e.g., 'view users' -> 'users')
    const parts = permission.name.split(' ');
    const category = parts.length > 1 ? parts[1] : 'other';
    
    if (!grouped[category]) {
      grouped[category] = [];
    }
    
    grouped[category].push(permission);
  });
  
  return grouped;
});

// Fetch all roles
const fetchRoles = async () => {
  isLoading.value = true;
  error.value = null;
  
  try {
    const response = await axios.get('/roles');
    roles.value = response.data.data.roles;
  } catch (err: any) {
    console.error('Error fetching roles:', err);
    error.value = err.response?.data?.message || 'Failed to fetch roles';
    toast.toast({
      title: 'Error',
      description: error.value,
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

// Fetch all permissions
const fetchPermissions = async () => {
  isLoadingPermissions.value = true;
  
  try {
    const response = await axios.get('/permissions');
    allPermissions.value = response.data.data.permissions;
  } catch (err: any) {
    console.error('Error fetching permissions:', err);
    toast.toast({
      title: 'Error',
      description: err.response?.data?.message || 'Failed to fetch permissions',
      variant: 'destructive',
    });
  } finally {
    isLoadingPermissions.value = false;
  }
};

// Open create role modal
const openCreateModal = () => {
  editing.value = false;
  roleForm.value = { name: '' };
  formErrors.value = { name: '' };
  showRoleModal.value = true;
};

// Handle edit role
const handleEdit = (role: Role) => {
  editing.value = true;
  selectedRole.value = { ...role };
  roleForm.value = { name: role.name };
  formErrors.value = { name: '' };
  showRoleModal.value = true;
};

// Handle role permissions
const handlePermissions = async (role: Role) => {
  selectedRole.value = { ...role };
  
  // Load all permissions if not already loaded
  if (!allPermissions.value.length) {
    await fetchPermissions();
  }
  
  // Set selected permissions
  selectedPermissions.value = role.permissions.map(p => p.id);
  showPermissionsModal.value = true;
};

// Handle delete role
const handleDelete = (role: Role) => {
  if (role.name === 'Super Admin') {
    toast.toast({
      title: 'Cannot Delete',
      description: 'The Super Admin role cannot be deleted',
      variant: 'destructive',
    });
    return;
  }
  
  selectedRole.value = { ...role };
  showDeleteModal.value = true;
};

// Submit role form (create or update)
const submitRoleForm = async () => {
  formErrors.value = { name: '' };
  
  // Validate
  if (!roleForm.value.name.trim()) {
    formErrors.value.name = 'Role name is required';
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    if (editing.value && selectedRole.value) {
      // Update existing role
      const response = await axios.put(`/roles/${selectedRole.value.id}`, roleForm.value);
      
      // Update local data
      const index = roles.value.findIndex(r => r.id === selectedRole.value!.id);
      if (index !== -1) {
        roles.value[index] = response.data.data;
      }
      
      toast.toast({
        title: 'Success',
        description: 'Role updated successfully',
      });
    } else {
      // Create new role
      const response = await axios.post('/roles', roleForm.value);
      roles.value.push(response.data.data);
      
      toast.toast({
        title: 'Success',
        description: 'Role created successfully',
      });
    }
    
    closeRoleModal();
  } catch (err: any) {
    console.error('Error submitting role:', err);
    
    if (err.response?.data?.errors?.name) {
      formErrors.value.name = err.response.data.errors.name[0];
    } else {
      toast.toast({
        title: 'Error',
        description: err.response?.data?.message || 'Failed to save role',
        variant: 'destructive',
      });
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Update permissions for a role
const updatePermissions = async () => {
  if (!selectedRole.value) return;
  
  isSubmittingPermissions.value = true;
  
  try {
    const response = await axios.post(`/roles/${selectedRole.value.id}/permissions`, {
      permissions: selectedPermissions.value
    });
    
    // Update local data
    const index = roles.value.findIndex(r => r.id === selectedRole.value!.id);
    if (index !== -1) {
      roles.value[index] = response.data.data;
    }
    
    toast.toast({
      title: 'Success',
      description: 'Permissions updated successfully',
    });
    
    closePermissionsModal();
  } catch (err: any) {
    console.error('Error updating permissions:', err);
    toast.toast({
      title: 'Error',
      description: err.response?.data?.message || 'Failed to update permissions',
      variant: 'destructive',
    });
  } finally {
    isSubmittingPermissions.value = false;
  }
};

// Confirm delete role
const confirmDelete = async () => {
  if (!selectedRole.value) return;
  
  isDeleting.value = true;
  
  try {
    await axios.delete(`/roles/${selectedRole.value.id}`);
    
    // Update local data
    roles.value = roles.value.filter(r => r.id !== selectedRole.value!.id);
    
    toast.toast({
      title: 'Success',
      description: 'Role deleted successfully',
    });
    
    closeDeleteModal();
  } catch (err: any) {
    console.error('Error deleting role:', err);
    toast.toast({
      title: 'Error',
      description: err.response?.data?.message || 'Failed to delete role',
      variant: 'destructive',
    });
  } finally {
    isDeleting.value = false;
  }
};

// Close role modal
const closeRoleModal = () => {
  showRoleModal.value = false;
  formErrors.value = { name: '' };
  if (!editing.value) {
    roleForm.value = { name: '' };
  }
};

// Close permissions modal
const closePermissionsModal = () => {
  showPermissionsModal.value = false;
};

// Close delete modal
const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedRole.value = null;
};

// Helper methods for permission selection
const isPermissionSelected = (permissionId: number) => {
  return selectedPermissions.value.includes(permissionId);
};

const togglePermission = (permissionId: number) => {
  if (isPermissionSelected(permissionId)) {
    // Remove permission if already selected
    selectedPermissions.value = selectedPermissions.value.filter(id => id !== permissionId);
  } else {
    // Add permission if not selected
    selectedPermissions.value.push(permissionId);
  }
};

// Initialize
onMounted(() => {
  fetchRoles();
});
</script> 