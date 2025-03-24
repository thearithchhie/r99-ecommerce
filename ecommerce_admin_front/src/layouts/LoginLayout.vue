<script setup lang="ts">
// Login layout for authentication screens
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <h1 class="text-3xl font-extrabold text-gray-900">E-Commerce Admin</h1>
        <p class="mt-2 text-sm text-gray-600">Sign in to your account</p>
      </div>
      
      <!-- Router view will render the login component -->
      <router-view />
      
      <div class="mt-6 text-center text-sm text-gray-600">
        <p>© {{ new Date().getFullYear() }} E-Commerce Admin. All rights reserved.</p>
      </div>
    </div>
  </div>
</template> 