<template>
  <DashboardLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h1 class="text-2xl font-semibold mb-4">
              {{ $t('dashboard.welcomeBack') }}, {{ authStore.user?.name }}!
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Quick Actions -->
              <div class="bg-blue-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium text-blue-900 mb-4">
                  {{ $t('dashboard.quickActions') }}
                </h3>
                <div class="space-y-3">
                  <Link
                    href="/dictionary"
                    class="block w-full text-left px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors"
                  >
                    {{ $t('dashboard.searchWordDefinitions') }}
                  </Link>
                  <Link
                    href="/favorites"
                    class="block w-full text-left px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors"
                  >
                    {{ $t('favorites.title') }}
                  </Link>
                  <Link
                    href="/settings"
                    class="block w-full text-left px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors"
                  >
                    {{ $t('settings.title') }}
                  </Link>
                </div>
              </div>

              <!-- Account Info -->
              <div class="bg-green-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium text-green-900 mb-4">
                  {{ $t('dashboard.accountInfo') }}
                </h3>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-sm text-green-700"
                      >{{ $t('common.name') }}:</span
                    >
                    <span class="text-sm font-medium text-green-900">{{
                      authStore.user?.name
                    }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-green-700"
                      >{{ $t('common.email') }}:</span
                    >
                    <span class="text-sm font-medium text-green-900">{{
                      authStore.user?.email
                    }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-green-700"
                      >{{ $t('dashboard.emailVerified') }}:</span
                    >
                    <span
                      class="text-sm font-medium"
                      :class="
                        authStore.user?.email_verified_at
                          ? 'text-green-600'
                          : 'text-red-600'
                      "
                    >
                      {{
                        authStore.user?.email_verified_at
                          ? $t('dashboard.verified')
                          : $t('dashboard.notVerified')
                      }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- System Status -->
              <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                  {{ $t('dashboard.systemStatus') }}
                </h3>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-700"
                      >{{ $t('dashboard.apiStatus') }}:</span
                    >
                    <span class="text-sm font-medium text-green-600">{{
                      $t('dashboard.online')
                    }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-700"
                      >{{ $t('dashboard.queueWorkers') }}:</span
                    >
                    <span class="text-sm font-medium text-green-600">{{
                      $t('dashboard.running')
                    }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Welcome Message -->
            <div
              class="mt-8 bg-gradient-to-r from-blue-500 to-purple-600 p-6 rounded-lg text-white"
            >
              <h2 class="text-xl font-semibold mb-2">
                {{ $t('dashboard.welcomeToDashboard') }}
              </h2>
              <p class="text-blue-100">
                {{ $t('dashboard.dashboardDescription') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
  import { Link } from '@inertiajs/vue3';
  import DashboardLayout from './Dashboard/DashboardLayout.vue';
  import { useAuthStore } from '@/stores/auth';

  const authStore = useAuthStore();
</script>
