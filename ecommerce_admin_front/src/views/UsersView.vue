<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Search, ChevronLeft, ChevronRight, RefreshCw, UserPlus, ArrowUp, ArrowDown, Pencil, Trash2, Eye, Undo2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useRouter, useRoute } from 'vue-router';
import axios from '@/lib/axios';
import { useToast } from '@/components/ui/toast';
import { Pagination, PaginationList, PaginationFirst, PaginationPrev, PaginationNext, PaginationLast, PaginationListItem, PaginationEllipsis } from '@/components/ui/pagination';


// Define user type
interface User {
  id: number;
  username: string;
  email: string;
  role?: string;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  [key: string]: any; // Allow for additional properties
}

interface Pagination {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

const router = useRouter();
const route = useRoute();
const toast = useToast();

// User data state
const users = ref<User[]>([]);
const isLoading = ref(false);
const loadError = ref<string | null>(null);

// View state - 'active' or 'deleted'
const viewMode = ref<'active' | 'deleted'>('active');

// Pagination state from API
const pagination = ref<Pagination>({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1
});

// Delete confirmation
const showDeleteConfirm = ref(false);
const userToDelete = ref<number | null>(null);

// Add these variables for edit modal
const showEditModal = ref(false);
const userToEdit = ref<User | null>(null);
const editFormData = ref({
  username: '',
  email: '',
  password: '' // Optional - will only be sent if not empty
});
const editFormErrors = ref({
  username: '',
  email: '',
  password: ''
});

// Track recently updated user to highlight
const recentlyUpdatedUserId = ref<number | null>(null);

// Fetch users from the API
const fetchUsers = async (page = 1, perPage = 10, search = '') => {
  try {
    isLoading.value = true;
    loadError.value = null;
    
    // Determine the endpoint based on view mode
    const endpoint = viewMode.value === 'active' ? '/users' : '/users/trashed';
    
    console.log(`Fetching users for ${viewMode.value} view, page ${page}, perPage ${perPage}, search: "${search}"`);
    console.log(`Using endpoint: ${endpoint}`);
    
    // Make API request with selected parameters
    const response = await axios.get(endpoint, {
      params: { 
        page,
        per_page: perPage,
        search: search || undefined
      }
    });
    
    console.log('API Response status:', response.status);
    
    // Extract data from response and handle accordingly
    const responseData = response.data;
    
    
    // Handle different API response structures
    if (responseData) {
      try {
        // Extract users data
        let usersData: any[] = [];
        
        if (Array.isArray(responseData)) {
          // Direct array of users
          usersData = responseData;
        } 
        else if (responseData.data && Array.isArray(responseData.data)) {
          // Object with data property containing array
          usersData = responseData.data;
        }
        else if (responseData.data && responseData.data.users && Array.isArray(responseData.data.users)) {
          // Nested structure: data.users array
          usersData = responseData.data.users;
        }
        else if (responseData.users && Array.isArray(responseData.users)) {
          // Object with users property containing array
          usersData = responseData.users;
        }
        else {
          console.error('Could not find users array in response:', responseData);
          usersData = [];
        }
        
        // Update the users state
        users.value = usersData.map((user: any) => ({
          id: user.id,
          username: user.username || user.name || '',
          email: user.email || '',
          role: user.role || 'User',
          deleted_at: user.deleted_at || null,
          ...user
        }));
        
        // Extract pagination data
        let paginationData = {
          current_page: page,
          total: usersData.length,
          per_page: perPage,
          last_page: Math.ceil(usersData.length / perPage) || 1
        };
        
        // Try to find pagination data in different locations
        if (responseData.meta && responseData.meta.pagination) {
          // Format: { meta: { pagination: {...} } }
          paginationData = {
            current_page: responseData.meta.pagination.current_page || page,
            total: responseData.meta.pagination.total || usersData.length,
            per_page: responseData.meta.pagination.per_page || perPage,
            last_page: responseData.meta.pagination.last_page || Math.ceil(usersData.length / perPage) || 1
          };
        } 
        else if (responseData.pagination) {
          // Format: { pagination: {...} }
          paginationData = {
            current_page: responseData.pagination.current_page || page,
            total: responseData.pagination.total || usersData.length,
            per_page: responseData.pagination.per_page || perPage,
            last_page: responseData.pagination.last_page || Math.ceil(usersData.length / perPage) || 1
          };
        }
        else if (responseData.current_page !== undefined) {
          // Format: direct pagination properties in response
          paginationData = {
            current_page: responseData.current_page || page,
            total: responseData.total || usersData.length,
            per_page: responseData.per_page || perPage,
            last_page: responseData.last_page || Math.ceil(usersData.length / perPage) || 1
          };
        }
        
        // Update pagination state
        pagination.value = paginationData;
        
        console.log(`Loaded ${users.value.length} users`);
        console.log(`Pagination: page ${pagination.value.current_page}/${pagination.value.last_page}, total: ${pagination.value.total}`);
      } catch (parseError) {
        console.error('Error processing API response:', parseError);
        users.value = [];
      }
    } else {
      console.error('Empty or invalid API response');
      users.value = [];
    }
    
    // Update URL if needed
    updateUrlWithCurrentPage();
    
    // Process highlight user if needed
    processHighlightedUser();
    
  } catch (error: any) {
    console.error('Error fetching users:', error);
    loadError.value = error.response?.data?.message || error.message || 'Failed to fetch users';
    toast.toast({
      title: 'Error',
      description: loadError.value,
      variant: 'destructive'
    });
  } finally {
    isLoading.value = false;
  }
};

// Update URL with current page
const updateUrlWithCurrentPage = () => {
  // Only proceed if we have a valid pagination state
  if (pagination.value && pagination.value.current_page) {
    const currentPage = parseInt(route.query.page as string) || 1;
    if (currentPage !== pagination.value.current_page) {
      console.log(`Updating URL: page ${currentPage} → ${pagination.value.current_page}`);
      router.replace({
        query: {
          ...route.query,
          page: String(pagination.value.current_page)
        }
      });
    }
  }
};

// Separate function to process highlighted user
const processHighlightedUser = () => {
  if (route.query.highlight_user) {
    const highlightId = parseInt(route.query.highlight_user as string);
    // Find the user in the current page
    const highlightedUser = users.value.find(user => user.id === highlightId);
    if (highlightedUser) {
      console.log('Found highlighted user:', highlightedUser);
      recentlyUpdatedUserId.value = highlightId;
      // Remove the highlight_user from the URL after a short delay
      setTimeout(() => {
        const currentQuery = { ...route.query };
        delete currentQuery.highlight_user;
        router.replace({ query: currentQuery });
        // Clear the highlight after the animation finishes
        setTimeout(() => {
          recentlyUpdatedUserId.value = null;
        }, 2000);
      }, 500);
    } else {
      console.log(`Highlighted user ${highlightId} not found on current page`);
    }
  }
};

// Refresh data
const refreshData = () => {
  fetchUsers(pagination.value.current_page, pagination.value.per_page, searchQuery.value);
};

// Toggle view between active and deleted users
const toggleView = () => {
  // Toggle the view mode
  viewMode.value = viewMode.value === 'active' ? 'deleted' : 'active';
  
  console.log(`View mode changed to: ${viewMode.value}`);
  
  // Always reset to page 1 when switching views
  router.replace({
    query: {
      ...route.query,
      page: '1'
    }
  });
  
  // Fetch users with new view mode
  fetchUsers(1, pagination.value.per_page, searchQuery.value);
};

// Confirm delete user
const confirmDelete = (userId: number) => {
  userToDelete.value = userId;
  showDeleteConfirm.value = true;
};

// Delete user
const handleDelete = async () => {
  if (userToDelete.value !== null) {
    try {
      const response = await axios.delete(`/users/${userToDelete.value}`);
      
      if (response.data.success) {
        toast.toast({
          title: 'Success',
          description: 'User deleted successfully'
        });
        
        // Refresh the user list
        refreshData();
      } else {
        throw new Error(response.data.message || 'Failed to delete user');
      }
    } catch (error: any) {
      toast.toast({
        title: 'Error',
        description: error.response?.data?.message || error.message || 'Failed to delete user',
        variant: 'destructive'
      });
      console.error('Error deleting user:', error);
    } finally {
      // Reset delete confirmation
      userToDelete.value = null;
      showDeleteConfirm.value = false;
    }
  }
};

// Restore deleted user
const handleRestore = async (userId: number) => {
  try {
    isLoading.value = true;
    
    const response = await axios.patch(`/users/${userId}/restore`);
    
    if (response.data.success) {
      toast.toast({
        title: 'Success',
        description: 'User restored successfully'
      });
      
      // Refresh the user list
      refreshData();
    } else {
      throw new Error(response.data.message || 'Failed to restore user');
    }
  } catch (error: any) {
    toast.toast({
      title: 'Error',
      description: error.response?.data?.message || error.message || 'Failed to restore user',
      variant: 'destructive'
    });
    console.error('Error restoring user:', error);
  } finally {
    isLoading.value = false;
  }
};

// Cancel delete
const cancelDelete = () => {
  userToDelete.value = null;
  showDeleteConfirm.value = false;
};

// Edit user
const handleEdit = (user: User) => {
  // Get the current page directly from the URL if available, otherwise from pagination state
  const currentPage = route.query.page ? route.query.page.toString() : pagination.value.current_page.toString();
  console.log('Sending to edit with page:', currentPage);
  
  // Redirect to user edit page with current page information
  router.push({
    path: `/users/${user.id}/edit`,
    query: { from_page: currentPage }
  });
};

// View user details
const handleViewDetails = (userId: number) => {
  // Redirect to user details page
  router.push(`/users/${userId}`);
};

// Add new user
const handleAddUser = () => {
  router.push('/users/create');
};

// Search
const searchQuery = ref('');

// Initialize searchQuery from URL on component mount if it exists
watch(() => route.query.search, (newSearch) => {
  if (newSearch !== undefined && newSearch !== searchQuery.value) {
    console.log(`Setting search query from URL: "${newSearch}"`);
    searchQuery.value = newSearch as string;
  }
}, { immediate: true });

const handleSearch = () => {
  console.log(`Searching for: "${searchQuery.value}"`);
  
  // When searching, we always start from page 1
  // Update the URL to reflect the search and page reset
  const queryParams: Record<string, string> = { page: '1' };
  
  // Only add search param if there's actually a search term
  if (searchQuery.value) {
    queryParams.search = searchQuery.value;
  }
  
  // Replace URL with new search parameters
  router.replace({ query: queryParams });
  
  // Force a fetch since we're changing the search parameters
  fetchUsers(1, pagination.value.per_page, searchQuery.value);
};

// Sorting
const sortColumn = ref('username');
const sortDirection = ref('asc');

const toggleSort = (column: string) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
  
  // In a real application, you would send these sorting parameters to the backend
  // For now, we'll just do client-side sorting
  users.value.sort((a, b) => {
    const aValue = a[sortColumn.value];
    const bValue = b[sortColumn.value];
    
    if (sortDirection.value === 'asc') {
      if (aValue < bValue) return -1;
      if (aValue > bValue) return 1;
      return 0;
    } else {
      if (aValue > bValue) return -1;
      if (aValue < bValue) return 1;
      return 0;
    }
  });
};

// Pagination navigation
const goToPage = (page: number) => {
  if (isNaN(page) || page < 1) {
    console.warn(`Invalid page number: ${page}, defaulting to page 1`);
    page = 1;
  }
  
  const lastPage = pagination.value.last_page || 1;
  if (page > lastPage) {
    console.warn(`Page ${page} exceeds last page ${lastPage}, setting to last page`);
    page = lastPage;
  }
  
  console.log(`Going to page ${page} via goToPage function`);
  
  // Update URL first
  router.replace({
    query: { 
      ...route.query,
      page: page.toString() 
    }
  });
  
  // The actual data fetching will be handled by the route watcher
};

// Helper functions
const getStatusClass = (status: string) => {
  return status === 'active' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-gray-100 text-gray-800';
};

const getSortIcon = (column: string) => {
  if (sortColumn.value !== column) {
    return null;
  }
  return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
};

const paginationConfig = computed(() => {
  console.log('Computing pagination config:', {
    itemsPerPage: pagination.value.per_page || 10,
    total: pagination.value.total || 0,
    currentPage: pagination.value.current_page || 1,
    lastPage: pagination.value.last_page || 1
  });
  
  return {
    itemsPerPage: pagination.value.per_page || 10,
    total: pagination.value.total || 0,
    currentPage: pagination.value.current_page || 1,
    lastPage: pagination.value.last_page || 1
  };
});

// Handle page change from shadcn pagination component
const handlePageChange = (newPage: number) => {
  if (!newPage || isNaN(newPage) || newPage < 1) {
    console.warn(`Invalid page number: ${newPage}, defaulting to page 1`);
    newPage = 1;
  }
  
  console.log(`Changing to page ${newPage} via handlePageChange function`);
  goToPage(newPage);
};

// Watch for route changes to update the page
watch(() => route.query.page, (newPage, oldPage) => {
  console.log(`Route page changed from ${oldPage} to ${newPage}`);
  
  try {
    if (newPage) {
      const page = parseInt(newPage as string);
      
      if (isNaN(page) || page < 1) {
        console.warn(`Invalid page number in URL: ${newPage}, defaulting to page 1`);
        fetchUsers(1, pagination.value.per_page || 10, searchQuery.value);
        return;
      }
      
      // Only fetch if the page is different from current pagination state
      // This prevents duplicate fetches when we update the URL ourselves
      if (page !== pagination.value.current_page) {
        console.log(`Fetching data for page ${page} due to route change`);
        fetchUsers(page, pagination.value.per_page || 10, searchQuery.value);
      } else {
        console.log(`Page ${page} already matches current pagination state, skipping fetch`);
      }
    } else {
      // If page is removed from URL, default to page 1
      console.log('No page in URL, defaulting to page 1');
      fetchUsers(1, pagination.value.per_page || 10, searchQuery.value);
    }
  } catch (error) {
    console.error('Error in page route watcher:', error);
    // Fallback to page 1 if there's an error
    fetchUsers(1, pagination.value.per_page || 10, searchQuery.value);
  }
}, { immediate: true });

// Initial component setup
onMounted(() => {
  console.log('Users component mounted');
  
  // Force initial fetch to ensure we have data
  const initialPage = route.query.page ? parseInt(route.query.page as string) : 1;
  
  // Only fetch if we don't already have users (which might happen if the route watcher triggered first)
  if (users.value.length === 0) {
    console.log(`Initial fetch for page ${initialPage} - no users loaded yet`);
    fetchUsers(initialPage, pagination.value.per_page || 10, searchQuery.value);
  } else {
    console.log(`Skipping initial fetch - ${users.value.length} users already loaded`);
  }
});

// Function to open edit modal with user data
const openEditModal = (user: User) => {
  userToEdit.value = user;
  editFormData.value = {
    username: user.username,
    email: user.email,
    password: '' // Start with empty password
  };
  // Clear previous errors
  resetEditFormErrors();
  showEditModal.value = true;
};

// Function to clear form errors
const resetEditFormErrors = () => {
  editFormErrors.value = {
    username: '',
    email: '',
    password: ''
  };
};

// Function to handle user edit form submission
const handleEditSubmit = async () => {
  resetEditFormErrors();
  
  // Simple validation
  let hasErrors = false;
  
  if (!editFormData.value.username.trim()) {
    editFormErrors.value.username = 'Username is required';
    hasErrors = true;
  }
  
  if (!editFormData.value.email.trim()) {
    editFormErrors.value.email = 'Email is required';
    hasErrors = true;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(editFormData.value.email)) {
    editFormErrors.value.email = 'Invalid email format';
    hasErrors = true;
  }
  
  if (editFormData.value.password && editFormData.value.password.length < 8) {
    editFormErrors.value.password = 'Password must be at least 8 characters';
    hasErrors = true;
  }
  
  if (hasErrors) return;
  
  // Proceed with API update
  try {
    isLoading.value = true;
    
    // Prepare data - only include password if it's not empty
    const updateData: Record<string, string> = {
      username: editFormData.value.username,
      email: editFormData.value.email
    };
    
    if (editFormData.value.password) {
      updateData.password = editFormData.value.password;
    }
    
    const response = await axios.put(`/users/${userToEdit.value?.id}`, updateData);
    
    if (response.data.success) {
      toast.toast({
        title: 'Success',
        description: 'User updated successfully'
      });
      
      // Close modal and refresh data
      showEditModal.value = false;
      refreshData();
    } else {
      throw new Error(response.data.message || 'Failed to update user');
    }
  } catch (error: any) {
    // Handle validation errors from API
    if (error.response?.data?.errors) {
      const apiErrors = error.response.data.errors;
      if (apiErrors.username) editFormErrors.value.username = apiErrors.username[0];
      if (apiErrors.email) editFormErrors.value.email = apiErrors.email[0];
      if (apiErrors.password) editFormErrors.value.password = apiErrors.password[0];
    } else {
      toast.toast({
        title: 'Error',
        description: error.response?.data?.message || error.message || 'Failed to update user',
        variant: 'destructive'
      });
    }
    console.error('Error updating user:', error);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Users</h1>
      <div class="flex gap-2">
        <Button 
          variant="outline" 
          class="flex items-center gap-2" 
          @click="toggleView"
        >
          <Undo2 class="h-4 w-4" />
          {{ viewMode === 'active' ? 'Show Deleted Users' : 'Show Active Users' }}
        </Button>
        <Button class="flex items-center gap-2" @click="handleAddUser">
          <UserPlus class="h-4 w-4" />
          Add User
        </Button>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b">
        <div class="flex justify-between items-center">
          <div class="relative w-64">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <Search class="h-4 w-4 text-gray-400" />
            </div>
            <input
              v-model="searchQuery"
              type="search"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary text-sm"
              placeholder="Search users..."
              @keyup.enter="handleSearch"
            />
          </div>
          <Button variant="outline" class="flex items-center gap-2" @click="refreshData" :disabled="isLoading">
            <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': isLoading }" />
            {{ isLoading ? 'Loading...' : 'Refresh' }}
          </Button>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="isLoading && users.length === 0" class="flex justify-center items-center p-12">
        <div class="flex flex-col items-center">
          <RefreshCw class="h-8 w-8 text-primary animate-spin mb-4" />
          <p class="text-gray-500">Loading users...</p>
        </div>
      </div>

      <!-- Error state -->
      <div v-else-if="loadError && users.length === 0" class="flex justify-center items-center p-12">
        <div class="text-center">
          <p class="text-red-500 mb-4">{{ loadError }}</p>
          <Button @click="refreshData">Try Again</Button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="!isLoading && users.length === 0" class="flex justify-center items-center p-12">
        <div class="text-center">
          <p class="text-gray-500 mb-4">
            {{ viewMode === 'active' ? 'No users found' : 'No deleted users found' }}
          </p>
          <Button @click="handleAddUser" v-if="viewMode === 'active'">Add Your First User</Button>
          <Button @click="toggleView" v-else>View Active Users</Button>
        </div>
      </div>

      <!-- User table with loading overlay -->
      <div v-else class="overflow-x-auto relative">
        <!-- Transparent loading overlay when refreshing data -->
        <div v-if="isLoading && users.length > 0" class="absolute inset-0 bg-white bg-opacity-60 z-10 flex items-center justify-center">
          <RefreshCw class="h-8 w-8 text-primary animate-spin" />
        </div>
        
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th 
                scope="col" 
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                @click="toggleSort('username')"
              >
                <div class="flex items-center gap-1">
                  Username
                  <component :is="getSortIcon('username')" v-if="getSortIcon('username')" class="h-3 w-3" />
                </div>
              </th>
              <th 
                scope="col" 
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                @click="toggleSort('email')"
              >
                <div class="flex items-center gap-1">
                  Email
                  <component :is="getSortIcon('email')" v-if="getSortIcon('email')" class="h-3 w-3" />
                </div>
              </th>
              <th 
                scope="col" 
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                @click="toggleSort('role')"
              >
                <div class="flex items-center gap-1">
                  Role
                  <component :is="getSortIcon('role')" v-if="getSortIcon('role')" class="h-3 w-3" />
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="user in users" 
              :key="user.id" 
              :class="{ 
                'bg-red-50': user.deleted_at,
                'bg-blue-50 transition-colors duration-500': recentlyUpdatedUserId === user.id 
              }"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold mr-3">
                    {{ user && (user.name || user.username) ? (user.name || user.username).charAt(0) : '?' }}
                  </div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ user && (user.name || user.username) ? (user.name || user.username) : 'Unnamed User' }}
                    <span v-if="user.deleted_at" class="ml-2 text-xs font-normal text-red-500">
                      (Deleted)
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ user && user.email ? user.email : 'No email' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ user && user.role ? user.role : 'User' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex space-x-2">
                  <!-- Show restore button for deleted users -->
                  <div v-if="viewMode === 'deleted'" class="relative inline-flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border-2 border-purple-400"></div>
                    <Button 
                      variant="ghost" 
                      size="sm" 
                      class="h-8 w-8 p-0 flex items-center justify-center text-purple-600 hover:text-purple-800 hover:bg-purple-50 z-10"
                      @click="handleRestore(user.id)"
                    >
                      <Undo2 class="h-3.5 w-3.5" />
                      <span class="sr-only">Restore User</span>
                    </Button>
                  </div>
                  
                  <!-- Show view button only for active users -->
                  <div v-if="viewMode === 'active'" class="relative inline-flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border-2 border-green-400"></div>
                    <Button 
                      variant="ghost" 
                      size="sm" 
                      class="h-8 w-8 p-0 flex items-center justify-center text-green-600 hover:text-green-800 hover:bg-green-50 z-10"
                      @click="handleViewDetails(user.id)"
                    >
                      <Eye class="h-3.5 w-3.5" />
                      <span class="sr-only">View Details</span>
                    </Button>
                  </div>
                  
                  <!-- Show edit button only for active users -->
                  <div v-if="viewMode === 'active'" class="relative inline-flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border-2 border-blue-400"></div>
                    <Button 
                      variant="ghost" 
                      size="sm" 
                      class="h-8 w-8 p-0 flex items-center justify-center text-blue-600 hover:text-blue-800 hover:bg-blue-50 z-10"
                      @click="handleEdit(user)"
                    >
                      <Pencil class="h-3.5 w-3.5" />
                      <span class="sr-only">Edit</span>
                    </Button>
                  </div>
                  
                  <!-- Show delete button only for active users -->
                  <div v-if="viewMode === 'active'" class="relative inline-flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border-2 border-red-400"></div>
                    <Button 
                      variant="ghost" 
                      size="sm" 
                      class="h-8 w-8 p-0 flex items-center justify-center text-red-600 hover:text-red-800 hover:bg-red-50 z-10"
                      @click="confirmDelete(user.id)"
                    >
                      <Trash2 class="h-3.5 w-3.5" />
                      <span class="sr-only">Delete</span>
                    </Button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="users.length > 0" class="px-6 py-4 flex items-center justify-between border-t">
        <div class="text-sm text-gray-500">
          Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to 
          {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of 
          {{ pagination.total }} users
        </div>
        
        <div class="flex-1 flex justify-end">
          <Pagination 
            v-slot="{ page }" 
            :key="`pagination-${paginationConfig.currentPage}-${paginationConfig.total}`"
            :items-per-page="paginationConfig.itemsPerPage" 
            :total="paginationConfig.total" 
            :sibling-count="1"
            show-edges 
            :default-page="paginationConfig.currentPage"
            @update:page="handlePageChange"
          >
            <PaginationList v-slot="{ items }" class="flex items-center space-x-2">
              <PaginationFirst class="h-9 w-9 p-0" as-child>
                <Button
                  variant="ghost" 
                  size="sm"
                  class="h-8 w-8 p-0 flex items-center justify-center"
                  :disabled="isLoading"
                >
                  <ChevronLeft class="h-4 w-4 mr-1" />
                  <ChevronLeft class="h-4 w-4 -ml-3" />
                  <span class="sr-only">First Page</span>
                </Button>
              </PaginationFirst>
              
              <PaginationPrev class="h-9 w-9 p-0" as-child>
                <Button
                  variant="ghost" 
                  size="sm"
                  class="h-8 w-8 p-0 flex items-center justify-center"
                  :disabled="isLoading"
                >
                  <ChevronLeft class="h-4 w-4" />
                  <span class="sr-only">Previous Page</span>
                </Button>
              </PaginationPrev>

              <template v-for="(item, index) in items">
                <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                  <Button 
                    variant="ghost"
                    size="sm"
                    :class="item.value === page ? 'bg-primary text-primary-foreground' : ''"
                    :disabled="isLoading"
                    class="h-9 w-9 p-0 flex items-center justify-center"
                  >
                    {{ item.value }}
                  </Button>
                </PaginationListItem>
                <PaginationEllipsis v-else :key="item.type" :index="index" class="px-2" />
              </template>

              <PaginationNext class="h-9 w-9 p-0" as-child>
                <Button
                  variant="ghost" 
                  size="sm"
                  class="h-8 w-8 p-0 flex items-center justify-center"
                  :disabled="isLoading"
                >
                  <ChevronRight class="h-4 w-4" />
                  <span class="sr-only">Next Page</span>
                </Button>
              </PaginationNext>
              
              <PaginationLast class="h-9 w-9 p-0" as-child>
                <Button
                  variant="ghost" 
                  size="sm"
                  class="h-8 w-8 p-0 flex items-center justify-center"
                  :disabled="isLoading"
                >
                  <ChevronRight class="h-4 w-4 mr-1" />
                  <ChevronRight class="h-4 w-4 -ml-3" />
                  <span class="sr-only">Last Page</span>
                </Button>
              </PaginationLast>
            </PaginationList>
          </Pagination>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Deletion</h3>
        <p class="text-sm text-gray-500 mb-6">
          Are you sure you want to delete this user? This action can be undone later.
        </p>
        <div class="flex justify-end space-x-3">
          <Button 
            variant="outline" 
            @click="cancelDelete"
          >
            Cancel
          </Button>
          <Button 
            variant="default"
            class="bg-red-600 hover:bg-red-700"
            @click="handleDelete"
          >
            Delete
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>