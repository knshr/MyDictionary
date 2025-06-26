<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              {{ $t('dashboard.title') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              {{ $t('dashboard.welcomeBack') }},
              {{ user?.name || $t('dashboard.user') }}
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="logout"
              class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200"
            >
              {{ $t('auth.logout') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Welcome Card -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
              {{ $t('dashboard.welcomeToDashboard') }}
            </h2>
            <p class="text-gray-600 mb-4">
              {{ $t('dashboard.dashboardDescription') }}
            </p>
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-blue-600 font-medium">
                  {{ $t('navigation.dictionary') }}
                </div>
                <div class="text-blue-500 text-sm">
                  {{ $t('dictionary.wordLookupTool') }}
                </div>
              </div>
              <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-green-600 font-medium">OTP System</div>
                <div class="text-green-500 text-sm">
                  {{ $t('dictionary.secureAuthentication') }}
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
              {{ $t('dashboard.quickActions') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <button
                @click="navigateToDictionary"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200"
              >
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg mr-4">
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    />
                  </svg>
                </div>
                <div class="text-left">
                  <div class="font-medium text-gray-900">
                    {{ $t('navigation.dictionary') }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ $t('dashboard.searchWordDefinitions') }}
                  </div>
                </div>
              </button>

              <button
                @click="testOtp"
                class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-all duration-200"
              >
                <div class="p-2 bg-green-100 text-green-600 rounded-lg mr-4">
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                  </svg>
                </div>
                <div class="text-left">
                  <div class="font-medium text-gray-900">OTP Test</div>
                  <div class="text-sm text-gray-500">
                    {{ $t('dashboard.testAuthentication') }}
                  </div>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
          <!-- Dictionary Widget -->
          <DictionaryWidget @navigate="navigateTo" />

          <!-- User Info -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              {{ $t('dashboard.accountInfo') }}
            </h3>
            <div class="space-y-3">
              <div>
                <span class="text-sm text-gray-500">{{
                  $t('common.name')
                }}</span>
                <p class="text-gray-900">
                  {{ user?.name || $t('dashboard.na') }}
                </p>
              </div>
              <div>
                <span class="text-sm text-gray-500">{{
                  $t('common.email')
                }}</span>
                <p class="text-gray-900">
                  {{ user?.email || $t('dashboard.na') }}
                </p>
              </div>
              <div>
                <span class="text-sm text-gray-500">{{
                  $t('dashboard.emailVerified')
                }}</span>
                <p class="text-gray-900">
                  <span v-if="user?.email_verified_at" class="text-green-600">{{
                    $t('dashboard.verified')
                  }}</span>
                  <span v-else class="text-red-600">{{
                    $t('dashboard.notVerified')
                  }}</span>
                </p>
              </div>
            </div>
          </div>

          <!-- System Status -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              {{ $t('dashboard.systemStatus') }}
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">{{
                  $t('dashboard.apiStatus')
                }}</span>
                <span class="text-green-600 text-sm">{{
                  $t('dashboard.online')
                }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">{{
                  $t('dashboard.queueWorkers')
                }}</span>
                <span class="text-green-600 text-sm">{{
                  $t('dashboard.running')
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed } from 'vue';
  import { router } from '@inertiajs/vue3';
  import { useAuthStore } from '@/stores/auth';
  import DictionaryWidget from '@/components/DictionaryWidget.vue';
  import { useToastStore } from '@/stores/toast';

  const authStore = useAuthStore();
  const toastStore = useToastStore();

  const user = computed(() => authStore.user);

  const logout = async () => {
    await authStore.logout();
    router.visit('/login');
  };

  const navigateTo = path => {
    router.visit(path);
  };

  const navigateToDictionary = () => {
    router.visit('/dictionary');
  };

  const testOtp = () => {
    toastStore.showToast({
      message: 'OTP testing functionality would be implemented here',
      type: 'info',
    });
  };
</script>
