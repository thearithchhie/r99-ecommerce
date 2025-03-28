<template>
  <div v-if="permissionError" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
    <div class="flex">
      <div class="flex-shrink-0">
        <AlertCircle class="h-5 w-5 text-red-400" />
      </div>
      <div class="ml-3">
        <h3 class="text-sm font-medium text-red-800">
          Permission Error
        </h3>
        <div class="mt-2 text-sm text-red-700">
          <p>{{ permissionError }}</p>
        </div>
        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <Button 
              variant="outline" 
              class="bg-red-50 text-red-800 border-red-300 hover:bg-red-100"
              @click="permissionError = null"
            >
              Dismiss
            </Button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { AlertCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const route = useRoute();
const permissionError = ref<string | null>(null);

onMounted(() => {
  if (route.query.permissionError) {
    permissionError.value = route.query.permissionError as string;
  }
});
</script> 