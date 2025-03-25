<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Search, ChevronLeft, ChevronRight, RefreshCw, UserPlus, ArrowUp, ArrowDown, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useRouter } from 'vue-router';
import axios from '@/lib/axios';
import { useToast } from '@/components/ui/toast';
import { Pagination, PaginationList, PaginationFirst, PaginationPrev, PaginationNext, PaginationLast, PaginationListItem, PaginationEllipsis } from '@/components/ui/pagination';


// Define user type
interface User {
  id: number;
  name: string;
  email: string;
  role?: string;
  created_at?: string;
  updated_at?: string;
  [key: string]: any; // Allow for additional properties
}

interface Pagination {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}

const router = useRouter();
const toast = useToast();

// User data state
const users = ref<User[]>([]);
const isLoading = ref(false);
const loadError = ref<string | null>(null);

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

// Fetch users from the API
const fetchUsers = async (page = 1, perPage = 10, query = '') => {
  isLoading.value = true;
  loadError.value = null;
  
  try {
    const params = new URLSearchParams();
    params.append('page', page.toString());
    params.append('per_page', perPage.toString());
    
    if (query) {
      params.append('search', query);
    }
    
    const response = await axios.get(`/users?${params.toString()}`);
    
    if (response.data.success) {
      // Extract users and pagination data from the API response format
      if (response.data.data && Array.isArray(response.data.data.users)) {
        // Ensure each user has at least empty strings for required properties
        
        //FIXME: When we implement the backend, we need to remove this
        users.value = response.data.data.users.map((user: any) => ({
          id: user.id,
          name: user.name || '',
          email: user.email || '',
          role: user.role || 'User',
          created_at: user.created_at || '',
          updated_at: user.updated_at || '',
          ...user
        }));
      } else {
        console.warn('Unexpected API response format:', response.data);
        users.value = [];
      }
      
      // Extract pagination data if available
      if (response.data.meta && response.data.meta.pagination) {
        pagination.value = response.data.meta.pagination;
      }
    } else {
      throw new Error(response.data.message || 'Failed to fetch users');
    }
  } catch (error: any) {
    loadError.value = error.response?.data?.message || error.message || 'Failed to fetch users';
    toast.toast({
      title: 'Error',
      description: loadError.value,
      variant: 'destructive'
    });
    console.error('Error fetching users:', error);
  } finally {
    // Always set loading to false when done
    isLoading.value = false;
  }
};

// Refresh data
const refreshData = () => {
  fetchUsers(pagination.value.current_page, pagination.value.per_page, searchQuery.value);
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

// Cancel delete
const cancelDelete = () => {
  userToDelete.value = null;
  showDeleteConfirm.value = false;
};

// Edit user
const handleEdit = (userId: number) => {
  // Redirect to user edit page
  router.push(`/users/${userId}/edit`);
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
const handleSearch = () => {
  // Reset to first page when searching
  fetchUsers(1, pagination.value.per_page, searchQuery.value);
};

// Sorting
const sortColumn = ref('name');
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
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUsers(page, pagination.value.per_page, searchQuery.value);
  }
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

const paginationConfig = computed(() => ({
  itemsPerPage: pagination.value.per_page,
  total: pagination.value.total,
  currentPage: pagination.value.current_page
}));

// Handle page change from shadcn pagination component
const handlePageChange = (newPage: number) => {
  goToPage(newPage);
};

// Initial data loading
onMounted(() => {
  fetchUsers(1, 10);
});
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Users</h1>
      <Button class="flex items-center gap-2" @click="handleAddUser">
        <UserPlus class="h-4 w-4" />
        Add User
      </Button>
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
          <p class="text-gray-500 mb-4">No users found</p>
          <Button @click="handleAddUser">Add Your First User</Button>
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
                @click="toggleSort('name')"
              >
                <div class="flex items-center gap-1">
                  Name
                  <component :is="getSortIcon('name')" v-if="getSortIcon('name')" class="h-3 w-3" />
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
            <tr v-for="user in users" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold mr-3">
                    {{ user && user.name ? user.name.charAt(0) : '?' }}
                  </div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ user && user.name ? user.name : 'Unnamed User' }}
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
                  <div class="relative inline-flex items-center justify-center">
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
                  
                  <div class="relative inline-flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border-2 border-blue-400"></div>
                    <Button 
                      variant="ghost" 
                      size="sm" 
                      class="h-8 w-8 p-0 flex items-center justify-center text-blue-600 hover:text-blue-800 hover:bg-blue-50 z-10"
                      @click="handleEdit(user.id)"
                    >
                      <Pencil class="h-3.5 w-3.5" />
                      <span class="sr-only">Edit</span>
                    </Button>
                  </div>
                  
                  <div class="relative inline-flex items-center justify-center">
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
          Are you sure you want to delete this user? This action cannot be undone.
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