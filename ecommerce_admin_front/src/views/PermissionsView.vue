<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Permission Management</h1>
      <Button @click="openCreateModal">Create New Permission</Button>
    </div>

    <!-- Loading state -->
    <div v-if="isLoading" class="flex justify-center my-12">
      <RefreshCw class="h-8 w-8 animate-spin text-primary" />
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="p-4 bg-red-50 text-red-700 rounded-md mb-6">
      <p>{{ error }}</p>
      <Button variant="outline" class="mt-2" @click="fetchPermissions">Try Again</Button>
    </div>

    <!-- Permissions List Table -->
    <div v-else-if="permissions.length" class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="permission in permissions" :key="permission.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">{{ permission.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ permission.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex space-x-2">
                <Button variant="outline" size="sm" @click="handleEdit(permission)">
                  <Pencil class="h-4 w-4" />
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  @click="handleDelete(permission)"
                  class="text-red-600 hover:bg-red-50"
                >
                  <Trash class="h-4 w-4" />
                </Button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination controls could be added here if needed -->
    </div>

    <!-- Empty state -->
    <div v-else class="p-6 text-center bg-white rounded-lg shadow-md">
      <h3 class="text-lg font-medium text-gray-900 mb-2">No permissions found</h3>
      <p class="text-gray-500 mb-4">Get started by creating a new permission.</p>
      <Button @click="openCreateModal">Create New Permission</Button>
    </div>

    <!-- Create/Edit Permission Modal -->
    <Dialog v-if="showModal" :open="showModal" @update:open="showModal = $event">
      <DialogContent class="sm:max-w-[425px]" @close="closeModal()">
        <DialogHeader>
          <DialogTitle>{{ editing ? 'Edit Permission' : 'Create New Permission' }}</DialogTitle>
          <DialogDescription>
            {{ editing ? 'Update permission details below.' : 'Enter details for the new permission.' }}
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitForm">
          <div class="grid gap-4 py-4">
            <div class="grid grid-cols-4 items-center gap-4">
              <Label for="name" class="text-right">Name</Label>
              <Input
                id="name"
                v-model="form.name"
                class="col-span-3"
                :class="{ 'border-red-500': formErrors.name }"
                placeholder="e.g., view users"
              />
            </div>
            <div v-if="formErrors.name" class="text-red-500 text-sm ml-[33%]">
              {{ formErrors.name }}
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeModal()">
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

    <!-- Delete Confirmation Modal -->
    <Dialog v-if="showDeleteModal" :open="showDeleteModal" @update:open="showDeleteModal = $event">
      <DialogContent class="sm:max-w-[425px]" @close="closeDeleteModal()">
        <DialogHeader>
          <DialogTitle>Delete Permission</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete the permission "{{ selectedPermission?.name }}"? This may affect roles that use this permission.
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
import { ref, onMounted } from 'vue';
import axios from '@/lib/axios';
import { useToast } from '@/components/ui/toast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { RefreshCw, Pencil, Trash } from 'lucide-vue-next';

// Interfaces
interface Permission {
  id: number;
  name: string;
}

// State
const toast = useToast();
const permissions = ref<Permission[]>([]);
const isLoading = ref<boolean>(true);
const error = ref<string | null>(null);

// Modal State
const showModal = ref<boolean>(false);
const editing = ref<boolean>(false);
const form = ref({ name: '' });
const formErrors = ref({ name: '' });
const isSubmitting = ref<boolean>(false);
const selectedPermission = ref<Permission | null>(null);

// Delete Modal State
const showDeleteModal = ref<boolean>(false);
const isDeleting = ref<boolean>(false);

// Fetch all permissions
const fetchPermissions = async () => {
  isLoading.value = true;
  error.value = null;
  
  try {
    const response = await axios.get('/permissions');
    permissions.value = response.data.data.permissions;
  } catch (err: any) {
    console.error('Error fetching permissions:', err);
    error.value = err.response?.data?.message || 'Failed to fetch permissions';
    toast.toast({
      title: 'Error',
      description: error.value,
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};

// Close modal and reset form
const closeModal = () => {
  showModal.value = false;
  formErrors.value = { name: '' };
  if (!editing.value) {
    form.value = { name: '' };
  }
};

// Close delete modal
const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedPermission.value = null;
};

// Open create permission modal
const openCreateModal = () => {
  editing.value = false;
  form.value = { name: '' };
  formErrors.value = { name: '' };
  showModal.value = true;
};

// Handle edit permission
const handleEdit = (permission: Permission) => {
  editing.value = true;
  selectedPermission.value = { ...permission };
  form.value = { name: permission.name };
  formErrors.value = { name: '' };
  showModal.value = true;
};

// Handle delete permission
const handleDelete = (permission: Permission) => {
  selectedPermission.value = { ...permission };
  showDeleteModal.value = true;
};

// Submit form (create or update)
const submitForm = async () => {
  formErrors.value = { name: '' };
  
  // Validate
  if (!form.value.name.trim()) {
    formErrors.value.name = 'Permission name is required';
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    if (editing.value && selectedPermission.value) {
      // Update existing permission
      const response = await axios.put(`/permissions/${selectedPermission.value.id}`, form.value);
      
      // Update local data
      const index = permissions.value.findIndex(p => p.id === selectedPermission.value!.id);
      if (index !== -1) {
        permissions.value[index] = response.data.data;
      }
      
      toast.toast({
        title: 'Success',
        description: 'Permission updated successfully',
      });
    } else {
      // Create new permission
      const response = await axios.post('/permissions', form.value);
      permissions.value.push(response.data.data);
      
      toast.toast({
        title: 'Success',
        description: 'Permission created successfully',
      });
    }
    
    closeModal();
  } catch (err: any) {
    console.error('Error submitting permission:', err);
    
    if (err.response?.data?.errors?.name) {
      formErrors.value.name = err.response.data.errors.name[0];
    } else {
      toast.toast({
        title: 'Error',
        description: err.response?.data?.message || 'Failed to save permission',
        variant: 'destructive',
      });
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Confirm delete permission
const confirmDelete = async () => {
  if (!selectedPermission.value) return;
  
  isDeleting.value = true;
  
  try {
    await axios.delete(`/permissions/${selectedPermission.value.id}`);
    
    // Update local data
    permissions.value = permissions.value.filter(p => p.id !== selectedPermission.value!.id);
    
    toast.toast({
      title: 'Success',
      description: 'Permission deleted successfully',
    });
    
    closeDeleteModal();
  } catch (err: any) {
    console.error('Error deleting permission:', err);
    toast.toast({
      title: 'Error',
      description: err.response?.data?.message || 'Failed to delete permission',
      variant: 'destructive',
    });
  } finally {
    isDeleting.value = false;
  }
};

// Initialize
onMounted(() => {
  fetchPermissions();
});
</script> 