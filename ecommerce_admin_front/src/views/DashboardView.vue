<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { TrendingUp, Users, ShoppingBag, CreditCard, Package, Tag, Layers } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { useRouter } from 'vue-router'
import axios from 'axios'
import PermissionAlert from '@/components/PermissionAlert.vue'
import apiClient from '@/utils/axios'

const router = useRouter()
const isLoading = ref(true)
const productStats = ref({
  totalProducts: 0,
  activeProducts: 0,
  featuredProducts: 0,
  categories: 0,
  brands: 0
})

const stats = ref([
  {
    name: 'Total Revenue',
    value: '$45,231.89',
    icon: TrendingUp,
    change: '+20.1%',
    changeType: 'positive',
  },
  {
    name: 'Total Customers',
    value: '2,350',
    icon: Users,
    change: '+15.2%',
    changeType: 'positive',
  },
  {
    name: 'Products Sold',
    value: '12,234',
    icon: ShoppingBag,
    change: '+12.2%',
    changeType: 'positive',
  },
  {
    name: 'Pending Orders',
    value: '45',
    icon: CreditCard,
    change: '-2.4%',
    changeType: 'negative',
  },
])

const orders = ref([
  { id: 1001, customer: 'John Doe', status: 'completed', amount: 199.99 },
  { id: 1002, customer: 'Jane Smith', status: 'processing', amount: 299.99 },
  { id: 1003, customer: 'Bob Johnson', status: 'cancelled', amount: 149.99 },
  { id: 1004, customer: 'Alice Brown', status: 'completed', amount: 399.99 },
  { id: 1005, customer: 'Charlie Wilson', status: 'processing', amount: 249.99 },
])

const latestProducts = ref<any[]>([])

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'completed': return 'success'
    case 'processing': return 'warning'
    case 'cancelled': return 'destructive'
    default: return 'secondary'
  }
}

const navigateToProducts = () => {
  router.push({ name: 'products' })
}

const fetchProductStats = async () => {
  try {
    // Fetch product statistics - in a real app, you would have an API endpoint for this
    const productsResponse = await apiClient.get('/products', { params: { per_page: 5 } })
    
    // Safely handle the response with defensive checks
    if (productsResponse.data && productsResponse.data.data) {
      latestProducts.value = productsResponse.data.data.products || []
      const total = productsResponse.data.data.pagination?.total || 0
      
      // Get counts of active and featured products
      let activeCount = 0
      let featuredCount = 0
      
      latestProducts.value.forEach((product: any) => {
        if (product && product.is_active) activeCount++
        if (product && product.is_featured) featuredCount++
      })
      
      // In a real app, you would get these from backend
      productStats.value = {
        totalProducts: total,
        activeProducts: activeCount,
        featuredProducts: featuredCount,
        categories: 12, // Placeholder value
        brands: 8 // Placeholder value
      }
    } else {
      console.warn('Unexpected API response structure for products:', productsResponse.data)
      // Set default values if API response is unexpected
      latestProducts.value = []
      productStats.value = {
        totalProducts: 0,
        activeProducts: 0,
        featuredProducts: 0, 
        categories: 0,
        brands: 0
      }
    }
  } catch (error: any) {
    console.error('Error fetching product statistics:', error)
    // Set default values in case of error
    latestProducts.value = []
    productStats.value = {
      totalProducts: 0,
      activeProducts: 0,
      featuredProducts: 0,
      categories: 0,
      brands: 0
    }
    
    // Check if it's an authentication error
    if (error?.response?.status === 401 || error?.response?.status === 302) {
      // For the dashboard, we'll just show placeholder data
      console.log("Authentication required for product data")
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchProductStats()
})
</script>

<template>
  <div class="space-y-6">
    <PermissionAlert />
    
    <div>
      <h2 class="text-3xl font-bold tracking-tight">Dashboard</h2>
      <p class="text-muted-foreground">
        Overview of your store's performance
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card v-for="stat in stats" :key="stat.name">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">
            {{ stat.name }}
          </CardTitle>
          <div class="rounded-full bg-primary/10 p-2">
            <component
              :is="stat.icon"
              class="h-4 w-4 text-primary"
            />
          </div>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stat.value }}</div>
          <p
            class="text-xs"
            :class="{
              'text-emerald-600': stat.changeType === 'positive',
              'text-rose-600': stat.changeType === 'negative',
            }"
          >
            {{ stat.change }} from last month
          </p>
        </CardContent>
      </Card>
    </div>
    
    <!-- Product Management Section -->
    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
      <!-- Product Stats Card -->
      <Card>
        <CardHeader class="flex items-center justify-between">
          <CardTitle>Product Overview</CardTitle>
          <Button variant="outline" size="sm" @click="navigateToProducts">
            Manage Products
          </Button>
        </CardHeader>
        <CardContent>
          <div v-if="isLoading" class="py-4 text-center">
            <div class="inline-block h-6 w-6 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]"></div>
            <span class="ml-2">Loading product data...</span>
          </div>
          <div v-else class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-2 p-2 rounded-md bg-muted/50">
              <Package class="h-5 w-5 text-primary" />
              <div>
                <div class="text-sm font-medium">Total Products</div>
                <div class="text-xl font-bold">{{ productStats.totalProducts }}</div>
              </div>
            </div>
            
            <div class="flex items-center gap-2 p-2 rounded-md bg-muted/50">
              <ShoppingBag class="h-5 w-5 text-green-500" />
              <div>
                <div class="text-sm font-medium">Active Products</div>
                <div class="text-xl font-bold">{{ productStats.activeProducts }}</div>
              </div>
            </div>
            
            <div class="flex items-center gap-2 p-2 rounded-md bg-muted/50">
              <Tag class="h-5 w-5 text-yellow-500" />
              <div>
                <div class="text-sm font-medium">Categories</div>
                <div class="text-xl font-bold">{{ productStats.categories }}</div>
              </div>
            </div>
            
            <div class="flex items-center gap-2 p-2 rounded-md bg-muted/50">
              <Layers class="h-5 w-5 text-blue-500" />
              <div>
                <div class="text-sm font-medium">Brands</div>
                <div class="text-xl font-bold">{{ productStats.brands }}</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <!-- Latest Products Card -->
      <Card>
        <CardHeader class="flex items-center justify-between">
          <CardTitle>Latest Products</CardTitle>
          <Button variant="outline" size="sm" @click="navigateToProducts">View All</Button>
        </CardHeader>
        <CardContent>
          <div v-if="isLoading" class="py-4 text-center">
            <div class="inline-block h-6 w-6 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]"></div>
            <span class="ml-2">Loading products...</span>
          </div>
          <div v-else-if="latestProducts.length === 0" class="py-4 text-center text-muted-foreground">
            No products found
          </div>
          <div v-else class="space-y-3">
            <div v-for="product in latestProducts.slice(0, 5)" :key="product.id" 
              class="flex items-center gap-3 p-2 rounded-md hover:bg-muted/50 transition-colors"
              @click="router.push({ name: 'product-edit', params: { id: product.id }})"
              style="cursor: pointer;"
            >
              <div class="w-10 h-10 rounded bg-muted flex items-center justify-center">
                <img 
                  v-if="product.primary_image" 
                  :src="product.primary_image" 
                  :alt="product.name" 
                  class="w-full h-full object-cover rounded"
                />
                <ShoppingBag v-else class="h-5 w-5 text-muted-foreground" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-medium truncate">{{ product.name }}</div>
                <div class="text-sm text-muted-foreground">${{ parseFloat(product.base_price).toFixed(2) }}</div>
              </div>
              <div>
                <Badge 
                  :variant="product.is_active ? 'default' : 'secondary'"
                  class="ml-auto"
                >
                  {{ product.is_active ? 'Active' : 'Inactive' }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card>
      <CardHeader class="flex items-center justify-between">
        <CardTitle>Recent Orders</CardTitle>
        <Button variant="outline" size="sm">View All</Button>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Order ID</TableHead>
              <TableHead>Customer</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-right">Amount</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="order in orders" :key="order.id">
              <TableCell class="font-medium">#{{ order.id }}</TableCell>
              <TableCell>{{ order.customer }}</TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(order.status)">
                  {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                </Badge>
              </TableCell>
              <TableCell class="text-right">${{ order.amount.toFixed(2) }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
</template> 