<template>
  <div
    class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">
          {{ $t('app.name') }}
        </h1>
        <p class="text-gray-600">{{ $t('app.description') }}</p>
      </div>

      <!-- Language Switcher -->
      <div class="flex justify-center space-x-4">
        <button
          @click="changeLocale('en')"
          :class="[
            'px-3 py-1 rounded-md text-sm font-medium transition-colors',
            $i18n.locale === 'en'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50',
          ]"
        >
          English
        </button>
        <button
          @click="changeLocale('tl')"
          :class="[
            'px-3 py-1 rounded-md text-sm font-medium transition-colors',
            $i18n.locale === 'tl'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50',
          ]"
        >
          Tagalog
        </button>
        <button
          @click="changeLocale('ja')"
          :class="[
            'px-3 py-1 rounded-md text-sm font-medium transition-colors',
            $i18n.locale === 'ja'
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50',
          ]"
        >
          日本語
        </button>
      </div>

      <!-- Main Content -->
      <div class="bg-white py-8 px-6 shadow-xl rounded-lg">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
  import { onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { locale } = useI18n();

  const changeLocale = newLocale => {
    locale.value = newLocale;
    localStorage.setItem('locale', newLocale);
  };

  // Set initial locale from localStorage
  onMounted(() => {
    const savedLocale = localStorage.getItem('locale');
    if (savedLocale && ['en', 'tl', 'ja'].includes(savedLocale)) {
      locale.value = savedLocale;
    }
  });
</script>
