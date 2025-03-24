<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-3xl font-bold tracking-tight">Products</h2>
        <p class="text-muted-foreground">
          Manage your product inventory
        </p>
      </div>
      <Button>Add Product</Button>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Products List</CardTitle>
      </CardHeader>
      <CardContent>
        <p>This is a placeholder for the products list.</p>
      </CardContent>
    </Card>
  </div>
</template> 