<template>
  <DashboardLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 text-gray-900">
        <h2 class="text-2xl font-bold mb-4">{{ $t('app.welcome') }}</h2>
        <p class="text-gray-600 mb-6">
          {{ $t('app.description') }}
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-blue-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">
              {{ $t('dictionary.search') }}
            </h3>
            <p class="text-blue-700">
              {{ $t('dictionary.searchPlaceholder') }}
            </p>
          </div>

          <div class="bg-green-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-green-900 mb-2">
              {{ $t('dictionary.favorites') }}
            </h3>
            <p class="text-green-700">
              {{ $t('dictionary.addToFavorites') }}
            </p>
          </div>

          <div class="bg-purple-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-purple-900 mb-2">
              {{ $t('dictionary.definitions') }}
            </h3>
            <p class="text-purple-700">
              {{ $t('dictionary.examples') }} &
              {{ $t('dictionary.synonyms') }}
            </p>
          </div>
        </div>

        <div class="mt-8">
          <h3 class="text-xl font-semibold mb-4">
            {{ $t('navigation.search') }}
          </h3>
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-gray-600 mb-4">
              {{ $t('dictionary.searchPlaceholder') }}
            </p>
            <Link href="/search" class="btn btn-primary">
              {{ $t('navigation.search') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
  import { Link } from '@inertiajs/vue3';
  import DashboardLayout from './Dashboard/DashboardLayout.vue';
</script>
