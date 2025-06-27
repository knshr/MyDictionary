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
            <!-- Desktop Navigation -->
            <div class="hidden md:ml-6 md:flex md:space-x-8">
              <Link
                href="/dashboard"
                :class="[
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                  currentRoute === '/dashboard'
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
                  currentRoute === '/dictionary'
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
                  currentRoute === '/favorites'
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
                  currentRoute === '/settings'
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
            <!-- Language Switcher -->
            <div class="relative language-menu">
              <button
                @click="toggleLanguageMenu"
                class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:text-gray-900 transition-colors duration-200"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"
                  />
                </svg>
                <span>{{
                  $t(`languages.${languageStore.currentLanguage}`)
                }}</span>
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </button>

              <!-- Language Dropdown -->
              <div
                v-if="showLanguageMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
              >
                <button
                  v-for="(name, code) in availableLanguages"
                  :key="code"
                  @click="handleLanguageButtonClick($event, code)"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                  :class="{
                    'bg-blue-50 text-blue-700':
                      code === languageStore.currentLanguage,
                  }"
                >
                  {{ name }}
                </button>
              </div>
            </div>

            <!-- User Menu -->
            <div class="relative">
              <div class="flex items-center space-x-3">
                <span class="text-gray-700">{{ authStore.user?.name }}</span>
                <button
                  @click="logout"
                  class="text-gray-600 hover:text-gray-800 transition-colors duration-200"
                >
                  {{ $t('auth.logout') }}
                </button>
              </div>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
              <button
                @click="showMobileMenu = !showMobileMenu"
                data-mobile-menu-button
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
              >
                <span class="sr-only">{{ $t('navigation.menu') }}</span>
                <svg
                  class="h-6 w-6"
                  :class="{ hidden: showMobileMenu, block: !showMobileMenu }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                </svg>
                <svg
                  class="h-6 w-6"
                  :class="{ block: showMobileMenu, hidden: !showMobileMenu }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Mobile Navigation -->
        <div
          v-if="showMobileMenu"
          class="md:hidden border-t border-gray-200 py-4 mobile-menu"
        >
          <div class="space-y-2">
            <Link
              href="/dashboard"
              :class="[
                'block px-3 py-2 rounded-md text-base font-medium',
                currentRoute === '/dashboard'
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50',
              ]"
              @click="showMobileMenu = false"
            >
              {{ $t('navigation.dashboard') }}
            </Link>
            <Link
              href="/dictionary"
              :class="[
                'block px-3 py-2 rounded-md text-base font-medium',
                currentRoute === '/dictionary'
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50',
              ]"
              @click="showMobileMenu = false"
            >
              {{ $t('navigation.dictionary') }}
            </Link>
            <Link
              href="/favorites"
              :class="[
                'block px-3 py-2 rounded-md text-base font-medium',
                currentRoute === '/favorites'
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50',
              ]"
              @click="showMobileMenu = false"
            >
              <div class="flex items-center">
                <svg
                  class="w-4 h-4 mr-2"
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
              </div>
            </Link>
            <Link
              href="/settings"
              :class="[
                'block px-3 py-2 rounded-md text-base font-medium',
                currentRoute === '/settings'
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50',
              ]"
              @click="showMobileMenu = false"
            >
              <div class="flex items-center">
                <svg
                  class="w-4 h-4 mr-2"
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
              </div>
            </Link>
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
  import { onMounted, computed, ref } from 'vue';
  import { Link, router, usePage } from '@inertiajs/vue3';
  import { useAuthStore } from '@/stores/auth';
  import { useLanguageStore } from '@/stores/language';
  import Toast from '@/components/Toast.vue';

  const authStore = useAuthStore();
  const languageStore = useLanguageStore();
  const page = usePage();
  const showMobileMenu = ref(false);
  const showLanguageMenu = ref(false);

  // Get current route for navigation highlighting
  const currentRoute = computed(() => page.url);

  // Available languages
  const availableLanguages = {
    en: 'English',
    tl: 'Tagalog',
    ja: '日本語',
  };

  const logout = () => {
    router.post('/logout');
  };

  const changeLanguage = language => {
    languageStore.setLanguage(language);
    showLanguageMenu.value = false;
  };

  const handleLanguageButtonClick = (event, language) => {
    event.stopPropagation();
    changeLanguage(language);
  };

  const toggleLanguageMenu = event => {
    event.stopPropagation();
    showLanguageMenu.value = !showLanguageMenu.value;
  };

  // Close mobile menu when clicking outside
  const closeMobileMenu = () => {
    showMobileMenu.value = false;
  };

  // Close language menu when clicking outside
  const closeLanguageMenu = () => {
    showLanguageMenu.value = false;
  };

  // Check authentication on mount
  onMounted(async () => {
    console.log('DashboardLayout mounted');
    console.log('Auth store state:', {
      user: authStore.user,
      token: authStore.token,
      isAuthenticated: authStore.isAuthenticated,
    });

    // Initialize language store
    languageStore.initializeLanguage();

    // Check if user is authenticated via token
    if (!authStore.isAuthenticated) {
      console.log('Not authenticated, trying to get current user...');
      // Try to get current user from API
      const result = await authStore.getCurrentUser();
      console.log('getCurrentUser result:', result);

      if (!result.success) {
        console.log('getCurrentUser failed, redirecting to login');
        router.visit('/login');
        return;
      }
    }

    console.log('Authentication check passed');

    // Add click outside listeners
    document.addEventListener('click', e => {
      // Close language menu if clicking outside
      if (!e.target.closest('.language-menu')) {
        closeLanguageMenu();
      }

      // Close mobile menu if clicking outside
      if (
        !e.target.closest('.mobile-menu') &&
        !e.target.closest('[data-mobile-menu-button]')
      ) {
        closeMobileMenu();
      }
    });
  });
</script>
