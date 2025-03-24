<script setup lang="ts">
import { ref, computed } from 'vue';
import { Search, ChevronLeft, ChevronRight, RefreshCw, UserPlus, ArrowUp, ArrowDown, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useRouter } from 'vue-router';

const router = useRouter();

// Dummy user data
const users = ref([
  { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Admin', status: 'Active', lastLogin: '2023-03-20 14:30' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-19 09:15' },
  { id: 3, name: 'Robert Johnson', email: 'robert@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-15 11:45' },
  { id: 4, name: 'Emily Davis', email: 'emily@example.com', role: 'Manager', status: 'Active', lastLogin: '2023-03-18 16:20' },
  { id: 5, name: 'Michael Wilson', email: 'michael@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-17 13:10' },
  { id: 6, name: 'Sarah Thompson', email: 'sarah@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-16 10:30' },
  { id: 7, name: 'David Anderson', email: 'david@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-10 09:45' },
  { id: 8, name: 'Jennifer Lee', email: 'jennifer@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-14 15:20' },
  { id: 9, name: 'William Martinez', email: 'william@example.com', role: 'Manager', status: 'Active', lastLogin: '2023-03-13 11:35' },
  { id: 10, name: 'Jessica Taylor', email: 'jessica@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-12 14:50' },
  { id: 11, name: 'Daniel Brown', email: 'daniel@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-08 09:15' },
  { id: 12, name: 'Amanda White', email: 'amanda@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-11 16:30' },
  { id: 13, name: 'Matthew Miller', email: 'matthew@example.com', role: 'Manager', status: 'Active', lastLogin: '2023-03-09 13:45' },
  { id: 14, name: 'Olivia Harris', email: 'olivia@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-07 10:20' },
  { id: 15, name: 'Andrew Clark', email: 'andrew@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-05 15:10' },
  { id: 16, name: 'Sophia Lewis', email: 'sophia@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-06 11:30' },
  { id: 17, name: 'Joseph Young', email: 'joseph@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-04 09:45' },
  { id: 18, name: 'Emma Walker', email: 'emma@example.com', role: 'Manager', status: 'Active', lastLogin: '2023-03-03 14:20' },
  { id: 19, name: 'Alexander Hall', email: 'alexander@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-02 10:35' },
  { id: 20, name: 'Ava Allen', email: 'ava@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-01 15:50' },
]);

// Delete confirmation
const showDeleteConfirm = ref(false);
const userToDelete = ref<number | null>(null);

const confirmDelete = (userId: number) => {
  userToDelete.value = userId;
  showDeleteConfirm.value = true;
};

const handleDelete = () => {
  if (userToDelete.value !== null) {
    // Remove user from array
    users.value = users.value.filter(user => user.id !== userToDelete.value);
    // Reset
    userToDelete.value = null;
    showDeleteConfirm.value = false;
  }
};

const cancelDelete = () => {
  userToDelete.value = null;
  showDeleteConfirm.value = false;
};

// Edit user
const handleEdit = (userId: number) => {
  // In a real application, this would navigate to an edit form or open a modal
  alert(`Edit user with ID: ${userId}`);
};

// View user details
const handleViewDetails = (userId: number) => {
  // Redirect to user details page
  router.push(`/users/${userId}`);
};

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref(8);
const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value));

// Search
const searchQuery = ref('');
const filteredUsers = computed(() => {
  if (!searchQuery.value) {
    return sortedUsers.value;
  }
  const query = searchQuery.value.toLowerCase();
  return sortedUsers.value.filter(user => 
    user.name.toLowerCase().includes(query) || 
    user.email.toLowerCase().includes(query) ||
    user.role.toLowerCase().includes(query)
  );
});

// Sorting
const sortColumn = ref('name');
const sortDirection = ref('asc');

const toggleSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

const sortedUsers = computed(() => {
  const sorted = [...users.value];
  return sorted.sort((a, b) => {
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
});

// Paginated users
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredUsers.value.slice(start, end);
});

// Navigation
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

// Get status class
const getStatusClass = (status) => {
  return status === 'Active' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-gray-100 text-gray-800';
};

// Get sort icon
const getSortIcon = (column) => {
  if (sortColumn.value !== column) {
    return null;
  }
  return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
};
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Users</h1>
      <Button class="flex items-center gap-2">
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
            />
          </div>
          <Button variant="outline" class="flex items-center gap-2">
            <RefreshCw class="h-4 w-4" />
            Refresh
          </Button>
        </div>
      </div>

      <div class="overflow-x-auto">
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
              <th 
                scope="col" 
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                @click="toggleSort('status')"
              >
                <div class="flex items-center gap-1">
                  Status
                  <component :is="getSortIcon('status')" v-if="getSortIcon('status')" class="h-3 w-3" />
                </div>
              </th>
              <th 
                scope="col" 
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                @click="toggleSort('lastLogin')"
              >
                <div class="flex items-center gap-1">
                  Last Login
                  <component :is="getSortIcon('lastLogin')" v-if="getSortIcon('lastLogin')" class="h-3 w-3" />
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in paginatedUsers" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold mr-3">
                    {{ user.name.charAt(0) }}
                  </div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ user.name }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ user.email }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ user.role }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusClass(user.status)]">
                  {{ user.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ user.lastLogin }}
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
      <div class="px-6 py-4 flex items-center justify-between border-t">
        <div class="text-sm text-gray-500">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to 
          {{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }} of 
          {{ filteredUsers.length }} users
        </div>
        
        <div class="flex-1 flex justify-end">
          <nav class="flex items-center space-x-2">
            <Button 
              variant="outline" 
              size="sm"
              :disabled="currentPage === 1"
              @click="prevPage"
              class="h-8 w-8 p-0 flex items-center justify-center"
            >
              <span class="sr-only">Previous</span>
              <ChevronLeft class="h-4 w-4" />
            </Button>
            
            <div v-for="page in totalPages" :key="page" class="flex items-center">
              <Button 
                variant="outline"
                size="sm"
                :class="page === currentPage ? 'bg-primary text-primary-foreground' : ''"
                @click="goToPage(page)"
                class="h-8 w-8 p-0 flex items-center justify-center"
              >
                {{ page }}
              </Button>
            </div>
            
            <Button 
              variant="outline"
              size="sm" 
              :disabled="currentPage === totalPages"
              @click="nextPage"
              class="h-8 w-8 p-0 flex items-center justify-center"
            >
              <span class="sr-only">Next</span>
              <ChevronRight class="h-4 w-4" />
            </Button>
          </nav>
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