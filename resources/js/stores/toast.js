import { defineStore } from 'pinia';
import { ref } from 'vue';

let nextId = 1;

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([]);

  function showToast({ message, type = 'info', duration = 3000 }) {
    const id = nextId++;
    toasts.value.push({ id, message, type });
    if (duration > 0) {
      setTimeout(() => removeToast(id), duration);
    }
  }

  function removeToast(id) {
    toasts.value = toasts.value.filter(t => t.id !== id);
  }

  function clearToasts() {
    toasts.value = [];
  }

  function showSuccess(message, duration = 3000) {
    showToast({ message, type: 'success', duration });
  }

  function showError(message, duration = 3000) {
    showToast({ message, type: 'error', duration });
  }

  return {
    toasts,
    showToast,
    showSuccess,
    showError,
    removeToast,
    clearToasts,
  };
});
