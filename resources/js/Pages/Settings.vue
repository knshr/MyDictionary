<template>
  <DashboardLayout>
    <div class="max-w-4xl mx-auto">
      <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h1 class="text-2xl font-bold text-gray-900">
            {{ $t('settings.title') }}
          </h1>
        </div>

        <div class="p-6 space-y-8">
          <!-- Cleanup Settings -->
          <div class="space-y-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 mb-2">
                {{ $t('settings.cleanupSettings') }}
              </h2>
              <p class="text-sm text-gray-600">
                {{ $t('settings.cleanupDescription') }}
              </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="space-y-4">
                <!-- Enable/Disable Cleanup -->
                <div class="flex items-center justify-between">
                  <div>
                    <label class="text-sm font-medium text-gray-700">
                      {{ $t('settings.cleanupInterval') }}
                    </label>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ $t('settings.currentInterval') }}:
                      {{ settings.favorites_cleanup_days }}
                      {{ $t('settings.days') }}
                    </p>
                  </div>
                  <div class="flex items-center">
                    <input
                      v-model="settings.favorites_cleanup_enabled"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm text-gray-700">
                      {{ $t('settings.favoritesOlderThan') }}
                      {{ settings.favorites_cleanup_days }}
                      {{ $t('settings.days') }}
                      {{ $t('settings.willBeDeleted') }}
                    </span>
                  </div>
                </div>

                <!-- Days Input -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      {{ $t('settings.days') }}
                    </label>
                    <input
                      v-model.number="settings.favorites_cleanup_days"
                      type="number"
                      min="1"
                      max="365"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      :disabled="!settings.favorites_cleanup_enabled"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      {{ $t('settings.hours') }}
                    </label>
                    <input
                      v-model.number="hours"
                      type="number"
                      min="0"
                      max="23"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      :disabled="!settings.favorites_cleanup_enabled"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      {{ $t('settings.minutes') }}
                    </label>
                    <input
                      v-model.number="minutes"
                      type="number"
                      min="0"
                      max="59"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      :disabled="!settings.favorites_cleanup_enabled"
                    />
                  </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                  <button
                    @click="saveSettings"
                    :disabled="isSaving"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                  >
                    <span v-if="isSaving">{{ $t('common.loading') }}</span>
                    <span v-else>{{ $t('settings.saveSettings') }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
  import { ref, onMounted, computed } from 'vue';
  import DashboardLayout from './Dashboard/DashboardLayout.vue';
  import { useToastStore } from '@/stores/toast';
  import axios from 'axios';

  const toastStore = useToastStore();
  const isSaving = ref(false);
  const hours = ref(0);
  const minutes = ref(0);

  const settings = ref({
    favorites_cleanup_days: 30,
    favorites_cleanup_enabled: true,
  });

  const loadSettings = async () => {
    try {
      const response = await axios.get('/api/settings/cleanup');
      if (response.data.success) {
        settings.value = response.data.data;
      }
    } catch (error) {
      console.error('Failed to load settings:', error);
      toastStore.showError('Failed to load settings');
    }
  };

  const saveSettings = async () => {
    isSaving.value = true;

    try {
      const response = await axios.put('/api/settings/cleanup', {
        favorites_cleanup_days: settings.value.favorites_cleanup_days,
        favorites_cleanup_enabled: settings.value.favorites_cleanup_enabled,
      });

      if (response.data.success) {
        toastStore.showSuccess($t('settings.settingsSaved'));
      } else {
        toastStore.showError(
          response.data.message || $t('settings.settingsError')
        );
      }
    } catch (error) {
      console.error('Failed to save settings:', error);
      toastStore.showError($t('settings.settingsError'));
    } finally {
      isSaving.value = false;
    }
  };

  onMounted(() => {
    loadSettings();
  });
</script>
