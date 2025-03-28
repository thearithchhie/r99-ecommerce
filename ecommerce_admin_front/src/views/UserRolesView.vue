<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">User Role Management</h1>
    </div>

    <!-- Loading state -->
    <div v-if="isLoading" class="flex justify-center my-12">
      <RefreshCw class="h-8 w-8 animate-spin text-primary" />
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="p-4 bg-red-50 text-red-700 rounded-md mb-6">
      <p>{{ error }}</p>
      <Button variant="outline" class="mt-2" @click="fetchUsers">Try Again</Button>
    </div>

    <!-- Users List Table -->
    <div v-else-if="users.length" class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">{{ user.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ user.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span 
                  v-for="role in user.roles" 
                  :key="role.id"
                  class="px-2 py-1 text-xs rounded bg-green-100 text-green-800"
                >
                  {{ role.name }}
                </span>
                <span v-if="!user.roles?.length" class="text-gray-400 text-sm italic">None</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <Button variant="outline" size="sm" @click="handleManageRoles(user)">
                <UserCog class="h-4 w-4 mr-2" />
                Manage Roles
              </Button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state -->
    <div v-else class="p-6 text-center bg-white rounded-lg shadow-md">
      <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
      <p class="text-gray-500 mb-4">You need to create users first before managing their roles.</p>
    </div>

    <!-- User Roles Modal -->
    <Dialog v-if="showRolesModal" :open="showRolesModal" @update:open="showRolesModal = $event">
      <DialogContent class="sm:max-w-[425px]" @close="showRolesModal = false">
        <DialogHeader>
          <DialogTitle>Manage Roles for {{ selectedUser?.name }}</DialogTitle>
          <DialogDescription>
            Select the roles you want to assign to this user.
          </DialogDescription>
        </DialogHeader>
        <div v-if="isLoadingRoles" class="flex justify-center my-4">
          <RefreshCw class="h-6 w-6 animate-spin text-primary" />
        </div>
        <div v-else class="py-4">
          <div class="space-y-4 max-h-[300px] overflow-y-auto">
            <div v-for="role in allRoles" :key="role.id" class="flex items-center space-x-2">
              <Checkbox 
                :id="`role-${role.id}`" 
                :checked="isRoleSelected(role.id)" 
                @update:checked="toggleRole(role.id)"
              />
              <Label :for="`role-${role.id}`" class="cursor-pointer flex flex-col">
                <span class="font-medium">{{ role.name }}</span>
                <span class="text-sm text-gray-500" v-if="role.permissions?.length">
                  {{ role.permissions.length }} permissions
                </span>
              </Label>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="closeRolesModal">
            Cancel
          </Button>
          <Button @click="updateUserRoles" :disabled="isSubmitting">
            <RefreshCw v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
            Save Roles
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from '@/lib/axios';
import { useToast } from '@/components/ui/toast';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { RefreshCw, UserCog } from 'lucide-vue-next';

// Interfaces
interface Role {
  id: number;
  name: string;
  permissions?: { id: number; name: string }[];
}

interface User {
  id: number;
  name: string;
  email: string;
  roles?: Role[];
}

// State
const toast = useToast();
const users = ref<User[]>([]);
const isLoading = ref<boolean>(true);
const error = ref<string | null>(null);

// Roles Modal State
const showRolesModal = ref<boolean>(false);
const selectedUser = ref<User | null>(null);
const allRoles = ref<Role[]>([]);
const selectedRoles = ref<number[]>([]);
const isLoadingRoles = ref<boolean>(false);
const isSubmitting = ref<boolean>(false);

// Fetch all users with their roles
const fetchUsers = async () => {
  isLoading.value = true;
  error.value = null;
  
  try {
    const response = await axios.get('/users');
    users.value = response.data.data.users;
  } catch (err: any) {
    console.error('Error fetching users:', err);
    error.value = err.response?.data?.message || 'Failed to fetch users';
    toast.toast({
      title: 'Error',
      description: error.value,
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

// Fetch all roles
const fetchRoles = async () => {
  isLoadingRoles.value = true;
  
  try {
    const response = await axios.get('/roles');
    allRoles.value = response.data.data.roles;
  } catch (err: any) {
    console.error('Error fetching roles:', err);
    toast.toast({
      title: 'Error',
      description: err.response?.data?.message || 'Failed to fetch roles',
      variant: 'destructive',
    });
  } finally {
    isLoadingRoles.value = false;
  }
};

// Handle manage roles
const handleManageRoles = async (user: User) => {
  // Reset state first
  selectedUser.value = null;
  selectedRoles.value = [];
  
  // Then set the selected user
  selectedUser.value = { ...user };
  
  // Load all roles if not already loaded
  if (!allRoles.value.length) {
    await fetchRoles();
  }
  
  // Set selected roles (make a copy to avoid reference issues)
  selectedRoles.value = (user.roles || []).map(r => r.id);
  
  // Open the modal last, after everything is set up
  showRolesModal.value = true;
};

// Update user roles
const updateUserRoles = async () => {
  if (!selectedUser.value) {
    toast.toast({
      title: 'Error',
      description: 'No user selected',
      variant: 'destructive',
    });
    return;
  }
  
  if (selectedRoles.value.length === 0) {
    toast.toast({
      title: 'Error',
      description: 'Please select at least one role',
      variant: 'destructive',
    });
    return;
  }
  
  isSubmitting.value = true;
  
  // Debug logging
  console.log('Selected role IDs:', selectedRoles.value);
  console.log('All available roles:', allRoles.value);
  
  try {
    // Get the first selected role ID
    const roleId = selectedRoles.value[0];
    
    // Find the role object with this ID to get its name
    const selectedRole = allRoles.value.find(role => role.id === roleId);
    
    if (!selectedRole) {
      throw new Error(`Could not find role with ID ${roleId}`);
    }
    
    console.log('Selected role object:', selectedRole);
    
    // Try sending the role name instead of ID
    const response = await axios.post(`/users/${selectedUser.value.id}/roles`, {
      role: selectedRole.name // Use the role name instead of ID
    });
    
    console.log('API response:', response.data);
    
    // Update local data
    if (response.data?.data) {
      const index = users.value.findIndex(u => u.id === selectedUser.value!.id);
      if (index !== -1) {
        // Update the user's roles with the response data
        users.value[index].roles = response.data.data.roles || [];
      }
    }
    
    toast.toast({
      title: 'Success',
      description: 'User role updated successfully',
    });
    
    // Close the modal properly
    closeRolesModal();
  } catch (err: any) {
    console.error('Error updating user roles:', err);
    console.log('Error response data:', err.response?.data);
    
    // Try to provide more specific error feedback
    if (err.message && err.message.includes('Could not find role')) {
      toast.toast({
        title: 'Role Error',
        description: err.message,
        variant: 'destructive',
      });
    } else if (err.response?.data?.errors) {
      const errors = err.response.data.errors;
      let errorMessage = 'Validation failed:';
      
      // Format error messages for each field
      Object.keys(errors).forEach(field => {
        errorMessage += ` ${field}: ${errors[field].join(', ')}`;
      });
      
      toast.toast({
        title: 'Validation Error',
        description: errorMessage,
        variant: 'destructive',
      });
    } else {
      // General error handling
      toast.toast({
        title: 'Error',
        description: err.response?.data?.message || 'Failed to update user roles',
        variant: 'destructive',
      });
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Helper methods for role selection
const isRoleSelected = (roleId: number) => {
  return selectedRoles.value.includes(roleId);
};

const toggleRole = (roleId: number) => {
  if (isRoleSelected(roleId)) {
    // Remove role if already selected
    selectedRoles.value = selectedRoles.value.filter(id => id !== roleId);
  } else {
    // Add role if not selected
    selectedRoles.value.push(roleId);
  }
};

// Close modal and reset state
const closeRolesModal = () => {
  showRolesModal.value = false;
  selectedUser.value = null;
  selectedRoles.value = [];
};

// Initialize
onMounted(() => {
  fetchUsers();
});
</script> 