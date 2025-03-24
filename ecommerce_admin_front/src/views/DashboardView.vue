<script setup lang="ts">
import { ref } from 'vue'
import { TrendingUp, Users, ShoppingBag, CreditCard } from 'lucide-vue-next'
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

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'completed': return 'success'
    case 'processing': return 'warning'
    case 'cancelled': return 'destructive'
    default: return 'secondary'
  }
}
</script>

<template>
  <div class="space-y-6">
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