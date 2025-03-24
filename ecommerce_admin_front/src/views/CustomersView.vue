<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-3xl font-bold tracking-tight">Customers</h2>
      <p class="text-muted-foreground">
        Manage your customers
      </p>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Customers List</CardTitle>
      </CardHeader>
      <CardContent>
        <p>This is a placeholder for the customers list.</p>
      </CardContent>
    </Card>
  </div>
</template> 