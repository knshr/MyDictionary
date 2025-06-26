<template>
  <div class="min-h-screen bg-gray-50">
    <Toast />
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <h1 class="text-xl font-bold text-gray-900">
                {{ $t('navigation.appName') }}
              </h1>
            </div>
            <div class="hidden md:ml-6 md:flex md:space-x-8">
              <Link
                href="/dashboard"
                :class="[
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                  $page.url === '/dashboard'
                    ? 'border-blue-500 text-gray-900'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                ]"
              >
                {{ $t('navigation.dashboard') }}
              </Link>
              <Link
                href="/dictionary"
                :class="[
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                  $page.url === '/dictionary'
                    ? 'border-blue-500 text-gray-900'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                ]"
              >
                {{ $t('navigation.dictionary') }}
              </Link>
              <Link
                href="/favorites"
                :class="[
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                  $page.url === '/favorites'
                    ? 'border-blue-500 text-gray-900'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                ]"
              >
                <svg
                  class="w-4 h-4 mr-1"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                    clip-rule="evenodd"
                  />
                </svg>
                {{ $t('navigation.favorites') }}
              </Link>
              <Link
                href="/settings"
                :class="[
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                  $page.url === '/settings'
                    ? 'border-blue-500 text-gray-900'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                ]"
              >
                <svg
                  class="w-4 h-4 mr-1"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                {{ $t('navigation.settings') }}
              </Link>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <!-- User Menu -->
            <div class="relative">
              <div class="flex items-center space-x-3">
                <span class="text-gray-700">{{
                  $page.props.auth?.user?.name || authStore.user?.name
                }}</span>
                <button
                  @click="logout"
                  class="text-gray-600 hover:text-gray-800 transition-colors duration-200"
                >
                  {{ $t('auth.logout') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="py-10">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
  import { onMounted } from 'vue';
  import { Link, router } from '@inertiajs/vue3';
  import { useAuthStore } from '@/stores/auth';
  import Toast from '@/components/Toast.vue';

  const authStore = useAuthStore();

  const logout = async () => {
    await authStore.logout();
    router.visit('/login');
  };

  // Check authentication on mount
  onMounted(async () => {
    if (!authStore.isAuthenticated) {
      router.visit('/login');
      return;
    }

    // Get current user info if not already loaded
    if (!authStore.user) {
      await authStore.getCurrentUser();
    }
  });
</script>
