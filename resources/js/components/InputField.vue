<template>
  <div class="mb-4">
    <label
      v-if="label"
      :for="id"
      class="block text-sm font-medium text-gray-700 mb-1"
    >
      {{ label }}<span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      :id="id"
      :type="type"
      :placeholder="placeholder"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :autocomplete="autocomplete"
      :class="[
        'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-400 text-gray-900 shadow-sm transition',
        error ? 'border-red-500' : 'border-gray-300',
      ]"
      @blur="validate"
      :required="required"
    />
    <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
  import { ref, watch, computed } from 'vue';
  import { useToastStore } from '@/stores/toast';

  const props = defineProps({
    modelValue: [String, Number],
    type: { type: String, default: 'text' },
    label: String,
    placeholder: String,
    required: { type: Boolean, default: false },
    rules: { type: Array, default: () => [] }, // Array of { validator: fn, message: string }
    id: String,
    autocomplete: { type: String, default: '' },
  });

  const emit = defineEmits(['update:modelValue', 'error']);
  const toastStore = useToastStore();
  const error = ref('');

  function validate() {
    error.value = '';
    if (props.required && !props.modelValue) {
      error.value = `${props.label || 'This field'} is required`;
      toastStore.showToast({ message: error.value, type: 'error' });
      emit('error', error.value);
      return false;
    }
    for (const rule of props.rules) {
      if (!rule.validator(props.modelValue)) {
        error.value = rule.message;
        toastStore.showToast({ message: error.value, type: 'error' });
        emit('error', error.value);
        return false;
      }
    }
    emit('error', '');
    return true;
  }

  // Validate on value change if already errored
  watch(
    () => props.modelValue,
    val => {
      if (error.value) validate();
    }
  );
</script>
