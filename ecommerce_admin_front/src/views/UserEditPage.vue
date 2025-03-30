<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Button } from '@/components/ui/button';
import { RefreshCw, ArrowLeft } from 'lucide-vue-next';
import axios from '@/lib/axios';
import { useToast } from '@/components/ui/toast';

interface User {
  id: number;
  name: string;
  email: string;
  role?: string;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  [key: string]: any;
}

const router = useRouter();
const route = useRoute();
const toast = useToast();

const userId = ref<number>(parseInt(route.params.id as string));
const isLoading = ref<boolean>(false);
const isSaving = ref<boolean>(false);
const updateSuccess = ref<boolean>(false);
// Store the original page number from query params - logging to debug
console.log('Route query params:', route.query);
const originalPage = ref<string | null>(route.query.from_page ? route.query.from_page.toString() : null);
console.log('Original page stored:', originalPage.value);

// Form data
const formData = ref({
  username: '',
  email: '',
  password: '' // Optional - will only be sent if not empty
});

// Form errors
const formErrors = ref({
  username: '',
  email: '',
  password: ''
});

// Track if the form has been modified
const formModified = ref(false);

// Watch for changes in the form data
watch(() => formData.value, () => {
  formModified.value = true;
}, { deep: true });

// Fetch user data
const fetchUser = async () => {
  isLoading.value = true;
  
  try {
    // Using the same endpoint pattern as the update function
    const response = await axios.get(`/users/${userId.value}`);
    console.log('User data response:', response.data);
    
    // Safely check for valid response format
    const responseData = response.data;
    
    // Extract user data based on response structure
    let userData = null;
    
    if (responseData.data) {
      // Format: { data: user }
      userData = responseData.data;
    } else if (responseData && !responseData.data) {
      // Format: direct user object
      userData = responseData;
    }
    
    if (userData) {
      console.log('User data structure:', userData);
      
      // Populate form data with fallbacks for missing values
      formData.value = {
        username: userData.username || userData.name || '',  // Handle both field names
        email: userData.email || '',
        password: '' // Start with empty password
      };
      
      console.log('User data loaded successfully');
    } else {
      throw new Error('Invalid user data structure in response');
    }
  } catch (error: any) {
    console.error('Error fetching user:', error);
    toast.toast({
      title: 'Error',
      description: error.response?.data?.message || error.message || 'Failed to fetch user',
      variant: 'destructive'
    });
    // Navigate back to users list if there's an error
    setTimeout(() => {
      router.push('/users');
    }, 1500);
  } finally {
    isLoading.value = false;
  }
};

// Function to clear form errors
const resetFormErrors = () => {
  formErrors.value = {
    username: '',
    email: '',
    password: ''
  };
};

// Cancel edit and go back
const handleCancel = () => {
  // Get the exact page number from the query params
  const pageToReturn = route.query.from_page;
  console.log('Canceling and returning to page:', pageToReturn);
  
  // Navigate back to the users page with correct page number
  if (pageToReturn) {
    router.push({
      path: '/users',
      query: { page: pageToReturn.toString() }
    });
  } else {
    router.push('/users');
  }
};

// Handle form submission
const handleSubmit = async () => {
  resetFormErrors();
  
  // Simple validation
  let hasErrors = false;
  
  if (!formData.value.username.trim()) {
    formErrors.value.username = 'Username is required';
    hasErrors = true;
  }
  
  if (!formData.value.email.trim()) {
    formErrors.value.email = 'Email is required';
    hasErrors = true;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
    formErrors.value.email = 'Invalid email format';
    hasErrors = true;
  }
  
  if (formData.value.password && formData.value.password.length < 8) {
    formErrors.value.password = 'Password must be at least 8 characters';
    hasErrors = true;
  }
  
  if (hasErrors) return;
  
  // Proceed with API update
  try {
    isSaving.value = true;
    updateSuccess.value = false;
    
    // Prepare data - only include password if it's not empty
    const updateData: Record<string, string> = {
      username: formData.value.username,  // Make sure this field name matches exactly what the backend expects
      email: formData.value.email
    };
    
    if (formData.value.password) {
      updateData.password = formData.value.password;
    }
    
    console.log('Submitting update for user ID:', userId.value);
    console.log('Update data being sent:', updateData); // Log the data being sent
    const response = await axios.patch(`/users/${userId.value}`, updateData);
    
    // Safely check for successful response
    const responseData = response.data;
    
    if (responseData && (responseData.success === true || response.status === 200)) {
      // Show success message
      toast.toast({
        title: 'Success',
        description: 'User updated successfully'
      });
      
      // Set the success state for in-form message
      updateSuccess.value = true;
      
      // Reset form modified flag since we've saved successfully
      formModified.value = false;
    } else {
      // Check if there's an error message in the response
      const errorMessage = responseData?.message || 'Failed to update user';
      throw new Error(errorMessage);
    }
  } catch (error: any) {
    console.error('Error updating user:', error);
    
    // Handle validation errors from API
    if (error.response?.data?.errors) {
      const apiErrors = error.response.data.errors;
      if (apiErrors.username) formErrors.value.username = apiErrors.username[0];
      if (apiErrors.email) formErrors.value.email = apiErrors.email[0];
      if (apiErrors.password) formErrors.value.password = apiErrors.password[0];
    } else {
      toast.toast({
        title: 'Error',
        description: error.response?.data?.message || error.message || 'Failed to update user',
        variant: 'destructive'
      });
    }
  } finally {
    isSaving.value = false;
  }
};

// Navigate back to user list after successful update
const navigateToUserList = () => {
  // Get the exact page number from the from_page param
  const pageToReturn = route.query.from_page;
  console.log('Navigating back to users list, page:', pageToReturn);
  
  // Navigate back to the users page with highlight and page parameters
  router.push({
    path: '/users',
    query: { 
      highlight_user: userId.value.toString(),
      page: pageToReturn ? pageToReturn.toString() : '1'
    }
  });
};

// Load user data when the component is mounted
onMounted(() => {
  fetchUser();
});
</script>

<template>
  <div>
    <div class="mb-6">
      <Button 
        variant="ghost" 
        class="pl-0 flex items-center text-gray-500 hover:text-gray-700"
        @click="handleCancel"
      >
        <ArrowLeft class="h-4 w-4 mr-2" />
        Back to Users
      </Button>
      <h1 class="text-2xl font-bold mt-2">Edit User</h1>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <RefreshCw class="h-8 w-8 text-primary animate-spin" />
    </div>

    <div v-else class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Username Field -->
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input
            id="username"
            v-model="formData.username"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
            :class="{ 'border-red-500': formErrors.username }"
          />
          <p v-if="formErrors.username" class="mt-1 text-sm text-red-600">{{ formErrors.username }}</p>
        </div>
        
        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
            :class="{ 'border-red-500': formErrors.email }"
          />
          <p v-if="formErrors.email" class="mt-1 text-sm text-red-600">{{ formErrors.email }}</p>
        </div>
        
        <!-- Password Field (Optional) -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password <span class="text-gray-400 text-xs">(Leave empty to keep current)</span>
          </label>
          <input
            id="password"
            v-model="formData.password"
            type="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
            :class="{ 'border-red-500': formErrors.password }"
            autocomplete="on"
          />
          <p v-if="formErrors.password" class="mt-1 text-sm text-red-600">{{ formErrors.password }}</p>
        </div>
        
        <!-- Success message -->
        <div v-if="updateSuccess" class="p-4 bg-green-50 text-green-700 rounded-md border border-green-200">
          <div class="flex items-center mb-3">
            <div class="mr-2 text-green-500 text-xl">✓</div>
            <span class="font-medium">User updated successfully!</span>
          </div>
          <div class="mt-2">
            <Button 
              type="button"
              variant="default"
              class="bg-green-600 hover:bg-green-700"
              @click="navigateToUserList"
            >
              <ArrowLeft class="h-4 w-4 mr-2" />
              Back to User List
            </Button>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 pt-4">
          <!-- Show action buttons only if update wasn't successful -->
          <template v-if="!updateSuccess">
            <Button 
              type="button"
              variant="outline" 
              @click="handleCancel"
              :disabled="isSaving"
            >
              Cancel
            </Button>
            <Button 
              type="submit"
              variant="default"
              :disabled="isSaving"
            >
              <RefreshCw v-if="isSaving" class="h-4 w-4 mr-2 animate-spin" />
              {{ isSaving ? 'Saving...' : 'Save Changes' }}
            </Button>
          </template>
        </div>
      </form>
    </div>
  </div>
</template> 