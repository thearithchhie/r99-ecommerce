<template>
  <div class="flex items-center space-x-2">
    <div
      :class="[
        'h-4 w-4 rounded-sm border flex items-center justify-center focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2',
        isChecked ? 'bg-primary border-primary' : 'border-gray-300',
        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
      ]"
      @click="handleClick"
    >
      <input
        type="checkbox"
        class="sr-only"
        :id="id"
        :name="name"
        :checked="isChecked"
        :value="value"
        :disabled="disabled"
        :required="required"
        @change="handleChange"
        ref="inputRef"
      />
      <svg
        v-if="isChecked"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="h-3 w-3 text-white"
      >
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
    </div>
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps({
  checked: {
    type: [Boolean, Array],
    default: undefined,
  },
  defaultChecked: {
    type: Boolean,
    default: false,
  },
  value: {
    type: [String, Number],
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  name: {
    type: String,
  },
  id: {
    type: String,
  },
  required: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:checked']);
const inputRef = ref<HTMLInputElement | null>(null);

// Manage both boolean and array modes
const isArrayValue = computed(() => Array.isArray(props.checked));
const isControlled = computed(() => props.checked !== undefined);
const internalChecked = ref(props.defaultChecked);

const isChecked = computed(() => {
  // If the value is bound to an array (for checkboxes with multiple values)
  if (isArrayValue.value && props.value !== undefined) {
    return (props.checked as any[]).includes(props.value);
  }
  
  // For controlled boolean checkboxes
  if (isControlled.value) {
    return props.checked as boolean;
  }
  
  // For uncontrolled checkboxes
  return internalChecked.value;
});

const handleChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  
  if (props.disabled) return;
  
  if (isArrayValue.value && props.value !== undefined) {
    const newArray = [...(props.checked as any[])];
    
    if (target.checked) {
      if (!newArray.includes(props.value)) {
        newArray.push(props.value);
      }
    } else {
      const index = newArray.indexOf(props.value);
      if (index !== -1) {
        newArray.splice(index, 1);
      }
    }
    
    emit('update:checked', newArray);
  } else {
    if (isControlled.value) {
      emit('update:checked', target.checked);
    } else {
      internalChecked.value = target.checked;
    }
  }
};

const handleClick = () => {
  if (!props.disabled && inputRef.value) {
    // Toggle the checkbox by triggering a click on the hidden input
    inputRef.value.click();
  }
};
</script> 