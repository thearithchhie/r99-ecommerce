<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Product Management</h1>
      <div class="flex gap-2">
        <Button class="flex items-center gap-2" @click="$router.push({ name: 'product-create' })">
          <i class="fas fa-plus-circle mr-1"></i> Add New Product
        </Button>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="p-4 border-b flex justify-between items-center">
        <h6 class="font-medium text-gray-900">Filters</h6>
        <Button variant="outline" class="flex items-center gap-2" @click="resetFilters">
          <i class="fas fa-redo-alt mr-1"></i> Reset Filters
        </Button>
      </div>
      <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input
                v-model="filters.search"
                @input="onFilterChange"
                type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:ring-primary focus:border-primary text-sm"
                id="search"
                placeholder="Search by name, SKU..."
              />
            </div>
          </div>
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select 
              v-model="filters.category_id" 
              @change="onFilterChange" 
              class="block w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-primary focus:border-primary text-sm" 
              id="category"
            >
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div>
            <label for="featured" class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
            <select 
              v-model="filters.featured" 
              @change="onFilterChange" 
              class="block w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-primary focus:border-primary text-sm" 
              id="featured"
            >
              <option value="">All Products</option>
              <option value="true">Featured</option>
              <option value="false">Not Featured</option>
            </select>
          </div>
          <div>
            <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
            <div class="flex">
              <select 
                v-model="filters.sort_by" 
                @change="onFilterChange" 
                class="block w-full py-2 px-3 border border-gray-300 rounded-md rounded-r-none focus:ring-primary focus:border-primary text-sm" 
                id="sort"
              >
                <option value="name">Name</option>
                <option value="created_at">Date Created</option>
                <option value="base_price">Price</option>
              </select>
              <button 
                class="px-3 py-2 border border-gray-300 border-l-0 rounded-md rounded-l-none bg-gray-50 hover:bg-gray-100 focus:outline-none"
                type="button"
                @click="toggleSortDirection"
              >
                <i :class="sortDirectionIcon"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Products List -->
    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b">
        <h6 class="font-medium text-gray-900">All Products</h6>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center p-12">
        <div class="flex flex-col items-center">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary border-t-transparent mb-4"></div>
          <p class="text-gray-500">Loading products...</p>
        </div>
      </div>
      
      <!-- Empty state -->
      <div v-else-if="products.length === 0" class="flex justify-center items-center p-12">
        <div class="text-center">
          <p class="text-gray-500 mb-4">No products found</p>
          <Button @click="$router.push({ name: 'product-create' })">Add Your First Product</Button>
        </div>
      </div>
      
      <!-- Product table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="product in products" :key="product.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <img 
                  v-if="product.primary_image" 
                  :src="product.primary_image" 
                  alt="Product Image" 
                  class="h-10 w-10 rounded-md object-cover"
                />
                <div v-else class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                  <i class="fas fa-image text-gray-400"></i>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.sku }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ product.category ? product.category.name : 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ formatPrice(product.base_price) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ product.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span 
                  v-if="product.is_featured" 
                  class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"
                >
                  Featured
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <router-link 
                    :to="{ name: 'product-edit', params: { id: product.id }}" 
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit"
                  >
                    <i class="fas fa-edit"></i>
                  </router-link>
                  <router-link 
                    :to="{ name: 'product-variants', params: { id: product.id }}" 
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Variants"
                  >
                    <i class="fas fa-cubes"></i>
                  </router-link>
                  <button 
                    class="text-red-600 hover:text-red-900" 
                    @click="confirmDelete(product)"
                    title="Delete"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="px-6 py-4 flex items-center justify-between border-t">
        <div class="text-sm text-gray-500">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} products
        </div>
        
        <div class="flex items-center gap-6">
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-700" for="per-page">Items per page:</label>
            <Select
              v-model="filters.per_page"
              :name="'per-page'"
              @update:modelValue="handlePerPageChange"
              :default-value="defaultPerPage"
            >
              <SelectTrigger class="w-20 h-9" id="per-page">
                <SelectValue :placeholder="filters.per_page.toString()" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <NoCheckSelectItem :value="10">10</NoCheckSelectItem>
                  <NoCheckSelectItem :value="25">25</NoCheckSelectItem>
                  <NoCheckSelectItem :value="50">50</NoCheckSelectItem>
                  <NoCheckSelectItem :value="100">100</NoCheckSelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <Pagination 
            v-slot="{ page }" 
            :key="`pagination-${pagination.current_page}-${pagination.total}`"
            :items-per-page="paginationConfig.itemsPerPage" 
            :total="paginationConfig.total" 
            :sibling-count="1"
            show-edges 
            :default-page="paginationConfig.currentPage"
            @update:page="changePage"
          >
            <PaginationList v-slot="{ items }" class="flex items-center space-x-2">
              <PaginationFirst class="h-9 w-9 p-0" as-child>
                <Button
                  variant="ghost" 
                  size="sm"
                  class="h-8 w-8 p-0 flex items-center justify-center"
                  :disabled="loading"
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
                  :disabled="loading"
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
                    :disabled="loading"
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
                  :disabled="loading"
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
                  :disabled="loading"
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

    <!-- Delete Modal -->
    <div v-if="selectedProduct" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h3>
        <p class="text-sm text-gray-500 mb-2">
          Are you sure you want to delete the product <strong>{{ selectedProduct.name }}</strong>?
        </p>
        <p class="mb-6 text-sm text-red-600">This action cannot be undone.</p>
        <div class="flex justify-end space-x-3">
          <Button 
            variant="outline" 
            @click="selectedProduct = null"
          >
            Cancel
          </Button>
          <Button 
            variant="destructive"
            @click="deleteProduct"
            :disabled="deleteLoading"
          >
            <span v-if="deleteLoading" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
            Delete Product
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, inject } from 'vue'
import axios from 'axios'
import { useToast } from '@/components/ui/toast'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import apiClient from '@/utils/axios'
import { useAuth } from '@/composables/useAuth'
import { 
  Pagination, 
  PaginationList, 
  PaginationFirst, 
  PaginationPrev, 
  PaginationNext, 
  PaginationLast, 
  PaginationListItem, 
  PaginationEllipsis 
} from '@/components/ui/pagination'
import { ChevronLeft, ChevronRight, ChevronDown } from 'lucide-vue-next'
import Select from '@/components/ui/select/Select.vue'
import SelectContent from '@/components/ui/select/SelectContent.vue'
import NoCheckSelectItem from '@/components/ui/select/NoCheckSelectItem.vue'
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue'
import SelectValue from '@/components/ui/select/SelectValue.vue'
import SelectGroup from '@/components/ui/select/SelectGroup.vue'
// Auth hook
const { isAuthenticated, redirectIfNotAuthenticated } = useAuth()

// Type definitions
interface Product {
  id: number
  name: string
  sku: string
  base_price: number
  primary_image?: string
  is_active: boolean
  is_featured: boolean
  category?: {
    id: number
    name: string
  }
}

interface Category {
  id: number
  name: string
}

interface PaginationState {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

interface FilterState {
  search: string
  category_id: string
  featured: string
  sort_by: string
  sort_direction: string
  page: number
  per_page: number
  [key: string]: string | number // Add index signature for dynamic property access
}

// Use router for navigation
const router = useRouter()

// Toast notification
const { toast } = useToast()

// Data properties
const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const deleteLoading = ref(false)
const selectedProduct = ref<Product | null>(null)

// Pagination state
const pagination = ref<PaginationState>({
  current_page: 1,
  last_page: 1,
  per_page: Number(import.meta.env.VITE_PER_PAGE) || 10,
  total: 0,
  from: 0,
  to: 0
})

// Filter state
const filters = ref<FilterState>({
  search: '',
  category_id: '',
  featured: '',
  sort_by: 'name',
  sort_direction: 'asc',
  page: 1,
  per_page: Number(import.meta.env.VITE_PER_PAGE) || 10
})

// Computed properties
const defaultPerPage = computed(() => Number(import.meta.env.VITE_PER_PAGE) || 10)

const sortDirectionIcon = computed(() => {
  return filters.value.sort_direction === 'asc' 
    ? 'fas fa-sort-up' 
    : 'fas fa-sort-down'
})

const paginationPages = computed(() => {
  const { current_page, last_page } = pagination.value
  
  // Logic for generating page numbers
  const delta = 2 // Number of pages before and after current
  let pages: number[] = []
  
  for (
    let i = Math.max(1, current_page - delta);
    i <= Math.min(last_page, current_page + delta);
    i++
  ) {
    pages.push(i)
  }
  
  return pages
})

// Pagination config for shadcn UI pagination component
const paginationConfig = computed(() => {
  return {
    itemsPerPage: pagination.value.per_page || 10,
    total: pagination.value.total || 0,
    currentPage: pagination.value.current_page || 1,
    lastPage: pagination.value.last_page || 1
  }
})

// Methods
const fetchProducts = async () => {
  console.log('fetchProducts called with filters:', filters.value)
  loading.value = true
  
  try {
    // Build query parameters
    const params = { ...filters.value }
    
    // Only include non-empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key]
      }
    })
    
    // Ensure pagination parameters are numeric
    if (params.per_page) {
      params.per_page = Number(params.per_page)
    }
    if (params.page) {
      params.page = Number(params.page)
    }
    
    console.log('Fetching products with params:', params)
    console.log('per_page value being sent:', params.per_page, 'type:', typeof params.per_page)
    
    // Debug info in UI
    const debugMsg = `API Request: per_page=${params.per_page}, page=${params.page}`;
    console.log(debugMsg);
    
    const response = await apiClient.get('/products', { params })
    
    // Defensive check for response structure
    if (response.data && response.data.data) {
      products.value = response.data.data.products || []
      pagination.value = response.data.data.pagination || {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
      }
    } else {
      // Handle unexpected response structure
      console.warn('Unexpected API response structure:', response.data)
      products.value = []
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
      }
      toast({
        title: "Warning",
        description: "Received unexpected data format from server",
        variant: "warning"
      })
    }
    
  } catch (error: unknown) {
    console.error('Error fetching products:', error)
    products.value = []
    
    // Check if it's an authentication error (redirect to login)
    if ((error as any)?.response?.status === 401 || 
        (error as any)?.response?.status === 302) {
      toast({
        title: "Authentication Error",
        description: 'Please log in to access product data',
        variant: "destructive"
      })
      
      // Redirect to login page
      router.push('/auth/login')
    } else {
      toast({
        title: "Error",
        description: 'Failed to load products. Please try again.',
        variant: "destructive"
      })
    }
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await apiClient.get('/categories')
    
    // Defensive check for response structure
    if (response.data && response.data.data) {
      categories.value = response.data.data.categories || []
    } else {
      // Handle unexpected response structure
      console.warn('Unexpected API response structure for categories:', response.data)
      categories.value = []
      toast({
        title: "Warning",
        description: "Unable to load categories due to unexpected data format",
        variant: "warning"
      })
    }
  } catch (error: unknown) {
    console.error('Error fetching categories:', error)
    categories.value = []
    toast({
      title: "Error",
      description: 'Failed to load categories',
      variant: "destructive"
    })
  }
}

const onFilterChange = () => {
  // Reset to first page when filters change
  console.log('onFilterChange called with per_page:', filters.value.per_page)
  filters.value.page = 1
  
  // Explicitly update URL to include per_page
  router.replace({
    query: { 
      ...router.currentRoute.value.query, 
      page: '1',
      per_page: filters.value.per_page.toString() 
    }
  })
  
  fetchProducts()
}

const handleValueChange = (val: string| null) => {
  console.log('onValueChange called with val:', val);
}

const onPerPageChange = (event: Event) => {
  console.log('onPerPageChange called')
  const target = event.target as HTMLSelectElement
  console.log('Selected value:', target.value)
  
  // Since v-model automatically updates filters.per_page, we just need to call onFilterChange
  onFilterChange()
}

const toggleSortDirection = () => {
  filters.value.sort_direction = filters.value.sort_direction === 'asc' ? 'desc' : 'asc'
  onFilterChange()
}

const resetFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    featured: '',
    sort_by: 'name',
    sort_direction: 'asc',
    page: 1,
    per_page: Number(import.meta.env.VITE_PER_PAGE) || 10
  }
  fetchProducts()
}

const changePage = (page: number) => {
  if (loading.value || page === pagination.value.current_page) return
  
  loading.value = true
  
  // Update the page in filters
  filters.value.page = page
  
  // Fetch data with new page
  fetchProducts()
  
  // Update URL query parameters
  router.replace({
    query: { ...router.currentRoute.value.query, page: page.toString() }
  })
}

const confirmDelete = (product: Product) => {
  selectedProduct.value = product
  // Use a type assertion to handle jQuery reference
  ;(window as any).$('#deleteModal').modal('show')
}

const deleteProduct = async () => {
  if (!selectedProduct.value) return
  
  deleteLoading.value = true
  
  try {
    await apiClient.delete(`/products/${selectedProduct.value.id}`)
    
    // Remove from list
    products.value = products.value.filter(p => p.id !== selectedProduct.value?.id)
    
    // Close modal
    // Use a type assertion to handle jQuery reference
    ;(window as any).$('#deleteModal').modal('hide')
    
    // Show success message
    toast({
      title: "Success",
      description: 'Product deleted successfully',
      variant: "success"
    })
    
    // Refresh products if the page is now empty
    if (products.value.length === 0 && pagination.value.current_page > 1) {
      changePage(pagination.value.current_page - 1)
    } else {
      // Just refresh the current page to update pagination counts
      fetchProducts()
    }
  } catch (error: unknown) {
    console.error('Error deleting product:', error)
    toast({
      title: "Error",
      description: (error as any)?.response?.data?.message || 'Failed to delete product. Please try again.',
      variant: "destructive"
    })
  } finally {
    deleteLoading.value = false
    selectedProduct.value = null
  }
}

const formatPrice = (price: number) => {
  return parseFloat(price.toString()).toFixed(2)
}

const handlePerPageChange = (value: any) => {
  console.log('handlePerPageChange called with value:', value);
  
  if (value !== null) {
    // Convert value to number
    filters.value.per_page = Number(value);
    // Call filter change to trigger API call
    onFilterChange();
  }
}

// Lifecycle hooks
onMounted(() => {
  // Redirect if not authenticated
  redirectIfNotAuthenticated()
  
  // Only fetch data if authenticated
  if (isAuthenticated.value) {
    fetchCategories()
    fetchProducts()
  }
})
</script>

<style scoped>
.product-list-container {
  padding: 1.5rem;
}

.table img {
  transition: transform 0.2s;
}

.table img:hover {
  transform: scale(1.2);
  cursor: pointer;
}

.badge-success {
  background-color: #28a745;
}

.badge-secondary {
  background-color: #6c757d;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
}
</style>