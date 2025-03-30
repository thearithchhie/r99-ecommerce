<template>
  <div class="product-variants-container">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0 text-gray-800">Product Variants</h1>
      <div>
        <router-link :to="{ name: 'product-edit', params: { id: productId } }" class="btn btn-info mr-2">
          <i class="fas fa-edit mr-1"></i> Edit Product
        </router-link>
        <router-link :to="{ name: 'products' }" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Back to Products
        </router-link>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <div v-else>
      <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
          <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                    Product Details
                  </div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ product.name }}</div>
                  <div class="text-sm text-gray-600 mt-2">
                    <p class="mb-1"><strong>SKU:</strong> {{ product.sku }}</p>
                    <p class="mb-1"><strong>Base Price:</strong> ${{ formatPrice(product.base_price) }}</p>
                    <p class="mb-1"><strong>Category:</strong> {{ product.category?.name || 'N/A' }}</p>
                    <p class="mb-0"><strong>Brand:</strong> {{ product.brand?.name || 'N/A' }}</p>
                  </div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-box-open fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-8 col-md-6 mb-4">
          <div class="alert alert-info">
            <h5 class="alert-heading"><i class="fas fa-info-circle mr-2"></i>Product Variants</h5>
            <p class="mb-0">This is a placeholder for the product variants feature. Here, you would be able to manage different variants of your product based on colors, sizes, etc.</p>
          </div>
          
          <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
              <h6 class="m-0 font-weight-bold text-primary">Variants</h6>
              <button class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle mr-1"></i> Add Variant
              </button>
            </div>
            <div class="card-body">
              <div class="text-center py-4">
                <i class="fas fa-cubes fa-3x text-gray-300 mb-3"></i>
                <p class="text-gray-600">No variants have been created yet.</p>
                <p class="text-muted">Create variants to offer different options of this product (colors, sizes, etc.)</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from '@/components/ui/toast'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/utils/axios'

// Type definitions for better type safety
interface ProductCategory {
  id: number
  name: string
}

interface ProductBrand {
  id: number
  name: string
}

interface Product {
  id?: number
  name: string
  sku: string
  description?: string
  base_price: number
  category?: ProductCategory
  brand?: ProductBrand
  is_active?: boolean
  is_featured?: boolean
  [key: string]: any // For any additional properties
}

// Use composition API
const { toast } = useToast()
const router = useRouter()
const route = useRoute()

// Reactive state
const productId = ref<string | null>(null)
const product = ref<Product>({
  name: '',
  sku: '',
  base_price: 0
})
const loading = ref(true)

// Lifecycle hook
onMounted(() => {
  productId.value = route.params.id as string
  
  // Check if this is a newly created product from the query parameter
  const isNew = route.query.new === 'true'
  
  if (isNew) {
    toast({
      title: "Success",
      description: 'Product created successfully! Now you can add variants.',
      variant: "success"
    })
  }
  
  fetchProduct()
})

// Methods
const fetchProduct = async () => {
  loading.value = true
  
  try {
    if (!productId.value) {
      throw new Error('Product ID is missing')
    }
    
    const response = await apiClient.get(`/products/${productId.value}`)
    
    // Defensive check for API response
    if (response.data && response.data.data) {
      product.value = response.data.data
    } else {
      console.warn('Unexpected API response structure:', response.data)
      toast({
        title: "Warning",
        description: "Received unexpected data format from server",
        variant: "warning"
      })
      router.push({ name: 'products' })
    }
  } catch (error: any) {
    console.error('Error fetching product:', error)
    
    // Check if it's an authentication error (redirect to login)
    if (error?.response?.status === 401 || error?.response?.status === 302) {
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
        description: error.message || 'Failed to load product details',
        variant: "destructive"
      })
      router.push({ name: 'products' })
    }
  } finally {
    loading.value = false
  }
}

const formatPrice = (price: number | undefined) => {
  if (price === undefined) return '0.00'
  return parseFloat(price.toString()).toFixed(2)
}
</script>

<style scoped>
.product-variants-container {
  padding: 1.5rem;
}
</style> 