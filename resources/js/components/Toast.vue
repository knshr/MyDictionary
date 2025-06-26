<template>
  <div
    class="fixed top-6 right-6 z-50 flex flex-col space-y-3"
    style="min-width: 250px; max-width: 350px"
  >
    <transition-group name="toast-fade" tag="div">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="toastClass(toast.type)"
        class="flex items-start px-4 py-3 rounded-lg shadow-lg text-sm font-medium animate-slide-in"
      >
        <span class="flex-1">{{ toast.message }}</span>
        <button
          class="ml-4 text-xl font-bold text-gray-400 hover:text-gray-700 focus:outline-none"
          @click="removeToast(toast.id)"
          aria-label="Close"
        >
          &times;
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
  import { storeToRefs } from 'pinia';
  import { useToastStore } from '@/stores/toast';

  const toastStore = useToastStore();
  const { toasts } = storeToRefs(toastStore);
  const { removeToast } = toastStore;

  function toastClass(type) {
    switch (type) {
      case 'success':
        return 'bg-green-100 text-green-800 border border-green-300';
      case 'error':
        return 'bg-red-100 text-red-800 border border-red-300';
      case 'warning':
        return 'bg-yellow-100 text-yellow-800 border border-yellow-300';
      default:
        return 'bg-gray-100 text-gray-800 border border-gray-300';
    }
  }
</script>

<style scoped>
  .toast-fade-enter-active,
  .toast-fade-leave-active {
    transition:
      opacity 0.3s,
      transform 0.3s;
  }
  .toast-fade-enter-from,
  .toast-fade-leave-to {
    opacity: 0;
    transform: translateY(-20px);
  }
  .animate-slide-in {
    animation: slide-in 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  @keyframes slide-in {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
