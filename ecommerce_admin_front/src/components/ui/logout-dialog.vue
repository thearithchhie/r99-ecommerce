<template>
  <Dialog v-if="modelValue" :open="true" @update:open="$emit('update:modelValue', $event)">
    <DialogContent class="sm:max-w-[425px]" @close="$emit('update:modelValue', false)">
      <DialogHeader>
        <DialogTitle>Are you sure you want to logout?</DialogTitle>
        <DialogDescription class="pt-2">
          This will end your current session. You will need to login again to access your account.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter class="mt-4">
        <Button variant="outline" @click="$emit('update:modelValue', false)">Cancel</Button>
        <Button variant="destructive" @click="handleLogout">Logout</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, defineEmits, defineProps } from 'vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { useToast } from '@/components/ui/toast';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'logout-success', 'logout-error']);

const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();

const handleLogout = async () => {
  try {
    emit('update:modelValue', false);
    await authStore.logout();
    
    toast.toast({
      title: 'Logged out successfully',
      description: 'You have been logged out of your account.',
    });
    
    emit('logout-success');
  } catch (error) {
    toast.toast({
      title: 'Error',
      description: 'Failed to complete logout process. You have been logged out locally.',
      variant: 'destructive',
    });
    
    emit('logout-error', error);
  }
};
</script> 