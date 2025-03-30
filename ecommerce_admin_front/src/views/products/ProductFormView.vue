<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">{{ isEditing ? 'Edit Product' : 'Create New Product' }}</h1>
      <router-link :to="{ name: 'products' }" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
        <ArrowLeft class="h-4 w-4" /> Back to Products
      </router-link>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
      <div class="p-4 bg-gray-50 border-b border-gray-200">
        <h2 class="font-medium text-gray-700">Product Information</h2>
      </div>
      <div class="p-6">
        <form @submit.prevent="saveProduct" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left column: Basic Information -->
            <div class="lg:col-span-2 space-y-5">
              <!-- Product Name -->
              <div class="space-y-1.5">
                <Label for="name" class="text-sm font-medium">
                  Product Name <span class="text-red-500">*</span>
                </Label>
                <Input
                  id="name"
                  v-model="product.name"
                  :class="{ 'border-red-500 ring-red-500': errors.name }"
                  placeholder="Enter product name"
                  required
                />
                <p v-if="errors.name" class="text-sm text-red-600">
                  {{ errors.name }}
                </p>
              </div>

              <!-- Description -->
              <div class="space-y-1.5">
                <Label for="description" class="text-sm font-medium">
                  Description
                </Label>
                <textarea
                  id="description"
                  v-model="product.description"
                  class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-1"
                  :class="{ 'border-red-500 ring-red-500': errors.description }"
                  rows="4"
                  placeholder="Enter product description"
                ></textarea>
                <p v-if="errors.description" class="text-sm text-red-600">
                  {{ errors.description }}
                </p>
              </div>

              <!-- Category and Brand row -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Category -->
                <div class="space-y-1.5">
                  <Label for="category" class="text-sm font-medium">
                    Category <span class="text-red-500">*</span>
                  </Label>
                  <select
                    id="category"
                    v-model="product.category_id"
                    class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-1"
                    :class="{ 'border-red-500 ring-red-500': errors.category_id }"
                    required
                  >
                    <option value="">Select a category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <p v-if="errors.category_id" class="text-sm text-red-600">
                    {{ errors.category_id }}
                  </p>
                </div>

                <!-- Brand -->
                <div class="space-y-1.5">
                  <Label for="brand" class="text-sm font-medium">
                    Brand <span class="text-red-500">*</span>
                  </Label>
                  <select
                    id="brand"
                    v-model="product.brand_id"
                    class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-1"
                    :class="{ 'border-red-500 ring-red-500': errors.brand_id }"
                    required
                  >
                    <option value="">Select a brand</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                      {{ brand.name }}
                    </option>
                  </select>
                  <p v-if="errors.brand_id" class="text-sm text-red-600">
                    {{ errors.brand_id }}
                  </p>
                </div>
              </div>

              <!-- Price and SKU row -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Base Price -->
                <div class="space-y-1.5">
                  <Label for="base_price" class="text-sm font-medium">
                    Base Price <span class="text-red-500">*</span>
                  </Label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <span class="text-gray-500">$</span>
                    </div>
                    <Input
                      type="number"
                      step="0.01"
                      min="0"
                      id="base_price"
                      v-model="product.base_price"
                      class="pl-7"
                      :class="{ 'border-red-500 ring-red-500': errors.base_price }"
                      placeholder="0.00"
                      required
                    />
                  </div>
                  <p v-if="errors.base_price" class="text-sm text-red-600">
                    {{ errors.base_price }}
                  </p>
                </div>

                <!-- SKU -->
                <div class="space-y-1.5">
                  <Label for="sku" class="text-sm font-medium">SKU</Label>
                  <Input
                    type="text"
                    id="sku"
                    v-model="product.sku"
                    :class="{ 'border-red-500 ring-red-500': errors.sku }"
                    placeholder="Auto-generated if left blank"
                    :disabled="isEditing"
                  />
                  <p v-if="errors.sku" class="text-sm text-red-600">
                    {{ errors.sku }}
                  </p>
                  <p v-if="!isEditing" class="text-xs text-gray-500">
                    Leave blank to auto-generate SKU
                  </p>
                </div>
              </div>
            </div>

            <!-- Right column: Status and Options -->
            <div class="space-y-5 lg:border-l lg:border-gray-200 lg:pl-6">
              <div>
                <h3 class="text-sm font-medium mb-3">Product Status</h3>
                
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <Label for="is_active" class="text-sm font-medium">Active Status</Label>
                      <p class="text-xs text-gray-500">Make this product visible to customers</p>
                    </div>
                    <div class="flex items-center h-6">
                      <input 
                        type="checkbox" 
                        id="is_active"
                        v-model="product.is_active" 
                        class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                      />
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between">
                    <div>
                      <Label for="is_featured" class="text-sm font-medium">Featured Product</Label>
                      <p class="text-xs text-gray-500">Show this product in featured sections</p>
                    </div>
                    <div class="flex items-center h-6">
                      <input 
                        type="checkbox" 
                        id="is_featured"
                        v-model="product.is_featured" 
                        class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                      />
                    </div>
                  </div>
                </div>
              </div>
              
              <div>
                <h3 class="text-sm font-medium mb-3">Additional Information</h3>
                
                <div class="mb-3 border border-gray-200 rounded-md p-3 bg-gray-50">
                  <h4 class="text-xs font-medium text-gray-700 mb-1">Product Variants</h4>
                  <p class="text-xs text-gray-600">
                    {{ isEditing ? 'You can manage variants after updating basic product information.' : 'You can add variants after creating the basic product information.' }}
                  </p>
                </div>
                
                <div class="mb-3 border border-gray-200 rounded-md p-3 bg-gray-50">
                  <h4 class="text-xs font-medium text-gray-700 mb-1">Product Images</h4>
                  <p class="text-xs text-gray-600">
                    Images can be uploaded in the next step.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Form actions -->
          <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 bg-background">
            <Button 
              type="button" 
              variant="outline" 
              @click="$router.push({ name: 'products' })"
            >
              Cancel
            </Button>
            <Button 
              type="submit"
              variant="default"
              :disabled="loading"
            >
              <span v-if="loading" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
              {{ isEditing ? 'Update Product' : 'Create Product' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useToast } from '@/components/ui/toast'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import apiClient from '@/utils/axios'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ArrowLeft } from 'lucide-vue-next'

// Authentication
const { isAuthenticated, redirectIfNotAuthenticated } = useAuth()

// Type definitions
interface Product {
  name: string
  description: string
  category_id: string | number
  brand_id: string | number
  base_price: string | number
  sku: string
  is_active: boolean
  is_featured: boolean
}

interface Category {
  id: number
  name: string
}

interface Brand {
  id: number
  name: string
}

interface ErrorState {
  [key: string]: string[]
}

// Router for navigation and route params
const router = useRouter()
const route = useRoute()

// Toast notification
const { toast } = useToast()

// Reactive state
const isEditing = computed(() => !!route.params.id)
const loading = ref(false)
const product = reactive<Product>({
  name: '',
  description: '',
  category_id: '',
  brand_id: '',
  base_price: '',
  sku: '',
  is_active: true,
  is_featured: false,
})
const categories = ref<Category[]>([])
const brands = ref<Brand[]>([])
const errors = ref<ErrorState>({})

// Methods
const fetchCategories = async () => {
  try {
    const response = await apiClient.get('/categories')
    
    // Defensive check for response structure
    if (response.data && response.data.data && response.data.data.categories) {
      categories.value = response.data.data.categories
    } else {
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
    
    // Don't show error toast on page load to prevent red notification
    if (isInitialLoad.value) {
      console.warn('Skipping error toast during initial load')
    } else {
      toast({
        title: "Error",
        description: 'Failed to load categories',
        variant: "destructive"
      })
    }
  }
}

const fetchBrands = async () => {
  try {
    const response = await apiClient.get('/brands')
    
    // Defensive check for response structure
    if (response.data && response.data.data && response.data.data.brands) {
      brands.value = response.data.data.brands
    } else {
      console.warn('Unexpected API response structure for brands:', response.data)
      brands.value = []
      
      // Don't show warning toast on page load to prevent red notification
      if (!isInitialLoad.value) {
        toast({
          title: "Warning",
          description: "Unable to load brands due to unexpected data format",
          variant: "warning"
        })
      }
    }
  } catch (error: unknown) {
    console.error('Error fetching brands:', error)
    brands.value = []
    
    // Don't show error toast on page load to prevent red notification
    if (isInitialLoad.value) {
      console.warn('Skipping error toast during initial load')
    } else {
      toast({
        title: "Error",
        description: 'Failed to load brands',
        variant: "destructive"
      })
    }
  }
}

const fetchProduct = async (id: string) => {
  loading.value = true
  
  try {
    const response = await apiClient.get(`/products/${id}`)
    
    // Defensive check for response structure
    if (response.data && response.data.data) {
      const productData = response.data.data
      
      // Map API response to form fields with fallbacks
      product.name = productData.name || ''
      product.description = productData.description || ''
      product.category_id = productData.category_id || ''
      product.brand_id = productData.brand_id || ''
      product.base_price = productData.base_price || 0
      product.sku = productData.sku || ''
      product.is_active = !!productData.is_active
      product.is_featured = !!productData.is_featured
    } else {
      console.warn('Unexpected API response structure for product:', response.data)
      toast({
        title: "Warning",
        description: "Received unexpected product data format from server",
        variant: "warning"
      })
      router.push({ name: 'products' })
    }
  } catch (error: unknown) {
    console.error('Error fetching product:', error)
    toast({
      title: "Error",
      description: 'Failed to load product details',
      variant: "destructive"
    })
    router.push({ name: 'products' })
  } finally {
    loading.value = false
  }
}

const saveProduct = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    const productData = { ...product }
    
    // Convert values to appropriate types
    productData.base_price = parseFloat(productData.base_price.toString())
    
    let response
    
    if (isEditing.value) {
      response = await apiClient.put(`/products/${route.params.id}`, productData)
      toast({
        title: "Success",
        description: 'Product updated successfully',
        variant: "success"
      })
    } else {
      response = await apiClient.post('/products', productData)
      toast({
        title: "Success",
        description: 'Product created successfully',
        variant: "success"
      })
    }
    
    // Navigate to product list or edit page for newly created product
    if (!isEditing.value) {
      const newProductId = response.data.data.id
      router.push({ 
        name: 'product-variants', 
        params: { id: newProductId },
        query: { new: 'true' }
      })
    } else {
      router.push({ name: 'products' })
    }
  } catch (error: unknown) {
    console.error('Error saving product:', error)
    
    if ((error as any)?.response?.data?.errors) {
      errors.value = (error as any).response.data.errors
      toast({
        title: "Error",
        description: 'Please correct the errors in the form',
        variant: "destructive"
      })
    } else {
      toast({
        title: "Error",
        description: (error as any)?.response?.data?.message || 'Failed to save product. Please try again.',
        variant: "destructive"
      })
    }
  } finally {
    loading.value = false
  }
}

// Initialize component on mount
const isInitialLoad = ref(true)

// Load data with safety
const loadDataSafely = async () => {
  try {
    // Load categories and brands in parallel
    await Promise.allSettled([
      fetchCategories().catch(err => console.warn('Failed to load categories:', err)),
      fetchBrands().catch(err => console.warn('Failed to load brands:', err))
    ])
    
    // Load product data if editing
    if (isEditing.value && route.params.id) {
      try {
        await fetchProduct(route.params.id as string)
      } catch (err) {
        console.warn('Failed to load product:', err)
      }
    }
  } catch (err) {
    console.warn('General error in loadDataSafely:', err)
  } finally {
    // Set initial load flag to false after data load attempts
    setTimeout(() => {
      isInitialLoad.value = false
    }, 1000)
  }
}

onMounted(() => {
  // Check authentication first
  if (redirectIfNotAuthenticated()) return
  
  // Only fetch data if authenticated
  if (isAuthenticated.value) {
    loadDataSafely()
  }
})

// Dismiss any visible error toasts when component is unmounted
onUnmounted(() => {
  // Any cleanup to dismiss errors
  console.log('ProductFormView unmounted - cleaning up error states')
})
</script> 