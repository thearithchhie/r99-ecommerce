<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Brands</h1>
      <Button @click="openCreateModal">Create New Brand</Button>
    </div>

    <!-- Loading state -->
    <div v-if="isLoading" class="flex justify-center my-12">
      <RefreshCw class="h-8 w-8 animate-spin text-primary" />
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="p-4 bg-red-50 text-red-700 rounded-md mb-6">
      <p>{{ error }}</p>
      <Button variant="outline" class="mt-2" @click="fetchBrands">Try Again</Button>
    </div>

    <!-- Brands List Table -->
    <div v-else-if="brands.length" class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="brand in brands" :key="brand.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">{{ brand.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <img v-if="brand.logo" :src="brand.logo" alt="Brand logo" class="h-10 w-10 object-contain" />
              <div v-else class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                <span class="text-xs text-gray-500">No logo</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ brand.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="brand.is_featured" class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Featured</span>
              <span v-else class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">No</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">{{ brand.order }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex space-x-2">
                <Button variant="outline" size="sm" @click="editBrand(brand)">
                  <Pencil class="h-4 w-4" />
                </Button>
                <Button variant="outline" size="sm" @click="deleteBrand(brand)" class="text-red-600 hover:bg-red-50">
                  <Trash class="h-4 w-4" />
                </Button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="py-3 px-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
          <Button
            variant="outline"
            size="sm"
            :disabled="isLoading || pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            :disabled="isLoading || pagination.current_page === pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
          >
            Next
          </Button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ pagination.from }}</span> to
              <span class="font-medium">{{ pagination.to }}</span> of
              <span class="font-medium">{{ pagination.total }}</span> brands
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-700" for="per-page">Items per page:</label>
            <select
              v-model="filters.per_page"
              id="per-page"
              @change="onFilterChange"
              class="h-9 w-20 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
          <Pagination
            :itemsPerPage="pagination.per_page"
            :total="pagination.total"
            :page="pagination.current_page"
          >
            <PaginationList>
              <PaginationFirst
                :disabled="isLoading || pagination.current_page === 1"
                @click="changePage(1)"
              />
              <PaginationPrev
                :disabled="isLoading || pagination.current_page === 1"
                @click="changePage(pagination.current_page - 1)"
              />

              <!-- Page Numbers -->
              <div class="flex items-center">
                <template v-for="page in getPaginationRange()" :key="page">
                  <!-- Show ellipses instead of large gaps -->
                  <PaginationEllipsis v-if="page === '...'" />

                  <!-- Render the page number button -->
                  <PaginationListItem
                    v-else
                    :value="typeof page === 'number' ? page : 0" 
                    :isActive="pagination.current_page === page"
                    @click="changePage(Number(page))"
                  >
                    {{ page }}
                  </PaginationListItem>
                </template>
              </div>

              <PaginationNext
                :disabled="isLoading || pagination.current_page === pagination.last_page"
                @click="changePage(pagination.current_page + 1)"
              />
              <PaginationLast
                :disabled="isLoading || pagination.current_page === pagination.last_page"
                @click="changePage(pagination.last_page)"
              />
            </PaginationList>
          </Pagination>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="p-6 text-center bg-white rounded-lg shadow-md">
      <h3 class="text-lg font-medium text-gray-900 mb-2">No brands found</h3>
      <p class="text-gray-500 mb-4">Get started by creating a new brand.</p>
      <Button @click="openCreateModal">Create New Brand</Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from '@/lib/axios';
import { Button } from '@/components/ui/button';
import { useToast } from '@/components/ui/toast';
import { 
  Pagination, 
  PaginationList, 
  PaginationFirst, 
  PaginationPrev, 
  PaginationNext, 
  PaginationLast,
  PaginationListItem,
  PaginationEllipsis
} from '@/components/ui/pagination';
import { RefreshCw, Pencil, Trash } from 'lucide-vue-next';

// Types
interface Brand {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  logo: string | null;
  web_url: string | null;
  is_featured: boolean;
  order: number;
  status_id: number;
  created_at: string;
  updated_at: string;
}

interface PaginationState {
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

interface FilterState {
  search: string;
  featured: string | null;
  status_id: string | null;
  sort_by: string;
  sort_direction: string;
  page: number;
  per_page: number;
}

// State setup
const toast = useToast();
const brands = ref<Brand[]>([]);
const isLoading = ref<boolean>(true);
const error = ref<string | null>(null);

// Default per page from environment variable
const defaultPerPage = computed(() => Number(import.meta.env.VITE_PER_PAGE) || 10);

// Pagination state
const pagination = ref<PaginationState>({
  current_page: 1,
  from: 0,
  last_page: 1,
  per_page: defaultPerPage.value,
  to: 0,
  total: 0
});

// Filter state
const filters = ref<FilterState>({
  search: '',
  featured: null,
  status_id: null,
  sort_by: 'name',
  sort_direction: 'asc',
  page: 1,
  per_page: defaultPerPage.value
});

// Fetch brands from API
const fetchBrands = async () => {
  isLoading.value = true;
  error.value = null;
  
  try {
    // Build query parameters
    const params: Record<string, any> = {
      page: filters.value.page,
      per_page: filters.value.per_page,
      sort_by: filters.value.sort_by,
      sort_direction: filters.value.sort_direction
    };
    
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.featured) params.featured = filters.value.featured;
    if (filters.value.status_id) params.status_id = filters.value.status_id;
    
    // Make API request
    console.log('Fetching brands with params:', params);
    const response = await axios.get('/brands', { params });
    console.log('API Response:', response.data);
    
    // Update state with response data
    brands.value = response.data.data.brands;
    
    // Update pagination data
    if (response.data.meta?.pagination) {
      pagination.value = response.data.meta.pagination;
    }
  } catch (err) {
    console.error('Error fetching brands:', err);
    error.value = 'Failed to load brands. Please try again.';
    toast.toast({
      title: 'Error',
      description: 'Failed to load brands',
      variant: 'destructive'
    });
  } finally {
    isLoading.value = false;
  }
};

// Handle page change
const changePage = (page: number) => {
  if (isLoading.value || page === filters.value.page) return;
  
  filters.value.page = page;
  fetchBrands();
};

// Handle filter change
const onFilterChange = () => {
  filters.value.page = 1; // Reset to first page when filters change
  fetchBrands();
};

// Helper function to determine which page numbers to show
const getPaginationRange = () => {
  const totalPages = pagination.value.last_page;
  const currentPage = pagination.value.current_page;
  const range = [];
  
  if (totalPages <= 7) {
    // If few pages, show all
    for (let i = 1; i <= totalPages; i++) {
      range.push(i);
    }
  } else {
    // Always show first page
    range.push(1);
    
    // Show elipsis if not adjacent to first page
    if (currentPage > 3) {
      range.push('...');
    }
    
    // Pages around current page
    const start = Math.max(2, currentPage - 1);
    const end = Math.min(totalPages - 1, currentPage + 1);
    
    for (let i = start; i <= end; i++) {
      range.push(i);
    }
    
    // Show elipsis if not adjacent to last page
    if (currentPage < totalPages - 2) {
      range.push('...');
    }
    
    // Always show last page
    if (totalPages > 1) {
      range.push(totalPages);
    }
  }
  
  return range;
};

// Placeholder functions for CRUD operations
const openCreateModal = () => {
  toast.toast({
    title: 'Coming Soon',
    description: 'Create Brand functionality will be implemented soon',
  });
};

const editBrand = (brand: Brand) => {
  toast.toast({
    title: 'Coming Soon',
    description: 'Edit Brand functionality will be implemented soon',
  });
};

const deleteBrand = (brand: Brand) => {
  toast.toast({
    title: 'Coming Soon',
    description: 'Delete Brand functionality will be implemented soon',
  });
};

// Load data when component mounts
onMounted(() => {
  fetchBrands();
});
</script> 