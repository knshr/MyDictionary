<template>
  <DashboardLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-6">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">
                {{ $t('favorites.title') }}
              </h1>
              <p class="mt-1 text-sm text-gray-500">
                {{ $t('favorites.subtitle') }}
              </p>
            </div>
            <div class="flex items-center space-x-4">
              <button
                @click="refreshFavorites"
                :disabled="isLoading"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200 disabled:opacity-50"
              >
                <svg
                  v-if="isLoading"
                  class="animate-spin w-4 h-4"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <span v-else>{{ $t('favorites.refresh') }}</span>
              </button>
              <button
                @click="navigateToDictionary"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
              >
                {{ $t('favorites.searchDictionary') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search and Filter -->
        <div
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6"
        >
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="$t('favorites.searchFavorites')"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div class="flex items-center space-x-4">
              <select
                v-model="sortBy"
                class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="created_at">
                  {{ $t('favorites.dateAdded') }}
                </option>
                <option value="word">{{ $t('favorites.word') }}</option>
                <option value="updated_at">
                  {{ $t('favorites.lastModified') }}
                </option>
              </select>
              <button
                @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
                class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                :title="
                  sortOrder === 'asc'
                    ? $t('favorites.ascending')
                    : $t('favorites.descending')
                "
              >
                <svg
                  class="w-5 h-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    v-if="sortOrder === 'asc'"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"
                  />
                  <path
                    v-else
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">
                  {{ $t('favorites.totalFavorites') }}
                </p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ favoritesCount }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 text-green-600 rounded-lg">
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
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">
                  {{ $t('favorites.withNotes') }}
                </p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ favoritesWithNotes }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
              <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
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
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">
                  {{ $t('favorites.recentlyAdded') }}
                </p>
                <p class="text-2xl font-semibold text-gray-900">
                  {{ recentFavorites }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Favorites List -->
        <div
          v-if="isLoading"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center"
        >
          <div
            class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"
          ></div>
          <p class="text-gray-600">
            {{ $t('favorites.loadingYourFavorites') }}
          </p>
        </div>

        <div
          v-else-if="filteredFavorites.length === 0"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center"
        >
          <div class="max-w-md mx-auto">
            <svg
              class="mx-auto h-16 w-16 text-gray-400 mb-4"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                clip-rule="evenodd"
              />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">
              {{ $t('favorites.noFavorites') }}
            </h3>
            <p class="text-gray-500 mb-6">
              {{ $t('favorites.noFavoritesDescription') }}
            </p>
            <button
              @click="navigateToDictionary"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              {{ $t('favorites.searchDictionary') }}
            </button>
          </div>
        </div>

        <div v-else class="space-y-6">
          <div
            v-for="favorite in paginatedFavorites"
            :key="favorite.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
          >
            <div class="flex justify-between items-start mb-4">
              <div class="flex-1">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                  {{ favorite.word }}
                </h3>
                <div class="text-sm text-gray-500 mb-2">
                  {{ $t('favorites.dateAdded') }}:
                  {{ formatDate(favorite.created_at) }}
                </div>
                <div
                  v-if="favorite.definition"
                  class="text-gray-700 mb-4"
                  v-html="favorite.definition"
                ></div>
              </div>
              <div class="flex items-center space-x-2 ml-4">
                <button
                  @click="editNotes(favorite)"
                  class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                  :title="$t('favorites.edit')"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                    />
                  </svg>
                </button>
                <button
                  @click="removeFavorite(favorite.id)"
                  class="p-2 text-red-400 hover:text-red-600 transition-colors duration-200"
                  :title="$t('favorites.delete')"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Notes Section -->
            <div v-if="favorite.notes" class="mt-4 p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-900 mb-2">
                {{ $t('favorites.notes') }}
              </h4>
              <p class="text-gray-700 text-sm">{{ favorite.notes }}</p>
            </div>
          </div>

          <!-- Pagination -->
          <div
            v-if="totalPages > 1"
            class="flex justify-center items-center space-x-2 mt-8"
          >
            <button
              @click="currentPage = Math.max(1, currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ $t('common.previous') }}
            </button>
            <span class="text-sm text-gray-700">
              {{ $t('common.page') }} {{ currentPage }} {{ $t('common.of') }}
              {{ totalPages }}
            </span>
            <button
              @click="currentPage = Math.min(totalPages, currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ $t('common.next') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Notes Modal -->
      <div
        v-if="showNotesModal"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        @click="closeNotesModal"
      >
        <div
          class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
          @click.stop
        >
          <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              {{ $t('favorites.addNotes') }}
            </h3>
            <textarea
              v-model="editingNotes"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="$t('favorites.addNotes')"
              autofocus
            ></textarea>
            <div class="flex justify-end space-x-3 mt-4">
              <button
                @click="closeNotesModal"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200"
              >
                {{ $t('favorites.cancel') }}
              </button>
              <button
                @click="saveNotes"
                :disabled="isSavingNotes"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
              >
                <span v-if="isSavingNotes">Saving...</span>
                <span v-else>{{ $t('favorites.save') }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  import { router } from '@inertiajs/vue3';
  import { useFavoritesStore } from '@/stores/favorites';
  import { useToastStore } from '@/stores/toast';
  import DashboardLayout from './Dashboard/DashboardLayout.vue';

  const favoritesStore = useFavoritesStore();
  const toastStore = useToastStore();

  const isLoading = ref(false);
  const searchQuery = ref('');
  const sortBy = ref('created_at');
  const sortOrder = ref('desc');
  const currentPage = ref(1);
  const itemsPerPage = ref(10);
  const showNotesModal = ref(false);
  const editingFavorite = ref(null);
  const editingNotes = ref('');
  const isSavingNotes = ref(false);

  const favorites = computed(() => favoritesStore.favorites);
  const favoritesCount = computed(() => favorites.value.length);
  const favoritesWithNotes = computed(
    () => favorites.value.filter(f => f.notes && f.notes.trim()).length
  );
  const recentFavorites = computed(() => {
    const oneWeekAgo = new Date();
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
    return favorites.value.filter(f => new Date(f.created_at) > oneWeekAgo)
      .length;
  });

  const filteredFavorites = computed(() => {
    let filtered = favorites.value;

    if (searchQuery.value) {
      filtered = filtered.filter(
        f =>
          f.word.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          (f.definition &&
            f.definition
              .toLowerCase()
              .includes(searchQuery.value.toLowerCase())) ||
          (f.notes &&
            f.notes.toLowerCase().includes(searchQuery.value.toLowerCase()))
      );
    }

    filtered.sort((a, b) => {
      let aValue, bValue;

      switch (sortBy.value) {
        case 'word':
          aValue = a.word.toLowerCase();
          bValue = b.word.toLowerCase();
          break;
        case 'updated_at':
          aValue = new Date(a.updated_at);
          bValue = new Date(b.updated_at);
          break;
        default:
          aValue = new Date(a.created_at);
          bValue = new Date(b.created_at);
      }

      if (sortOrder.value === 'asc') {
        return aValue > bValue ? 1 : -1;
      } else {
        return aValue < bValue ? 1 : -1;
      }
    });

    return filtered;
  });

  const totalPages = computed(() =>
    Math.ceil(filteredFavorites.value.length / itemsPerPage.value)
  );

  const paginatedFavorites = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredFavorites.value.slice(start, end);
  });

  const refreshFavorites = async () => {
    isLoading.value = true;
    try {
      await favoritesStore.fetchFavorites();
      toastStore.showSuccess('Favorites refreshed successfully');
    } catch (error) {
      toastStore.showError('Failed to refresh favorites');
    } finally {
      isLoading.value = false;
    }
  };

  const navigateToDictionary = () => {
    router.visit('/dictionary');
  };

  const removeFavorite = async id => {
    try {
      await favoritesStore.removeFavorite(id);
      toastStore.showSuccess('Favorite removed successfully');
    } catch (error) {
      toastStore.showError('Failed to remove favorite');
    }
  };

  const editNotes = favorite => {
    editingFavorite.value = favorite;
    editingNotes.value = favorite.notes || '';
    showNotesModal.value = true;
  };

  const closeNotesModal = () => {
    console.log('closeNotesModal called');
    showNotesModal.value = false;
    editingFavorite.value = null;
    editingNotes.value = '';
    isSavingNotes.value = false;
  };

  const handleKeydown = event => {
    if (event.key === 'Escape' && showNotesModal.value) {
      closeNotesModal();
    }
  };

  const saveNotes = async () => {
    console.log('saveNotes called');
    if (!editingFavorite.value || isSavingNotes.value) return;

    isSavingNotes.value = true;
    try {
      const result = await favoritesStore.updateFavorite(
        editingFavorite.value.id,
        editingNotes.value
      );

      console.log('updateFavorite result:', result);

      if (result.success) {
        toastStore.showSuccess('Notes saved successfully');
        closeNotesModal();
      } else {
        toastStore.showError(result.error || 'Failed to save notes');
      }
    } catch (error) {
      console.error('Error in saveNotes:', error);
      toastStore.showError('Failed to save notes');
    } finally {
      isSavingNotes.value = false;
    }
  };

  const formatDate = dateString => {
    return new Date(dateString).toLocaleDateString();
  };

  onMounted(async () => {
    isLoading.value = true;
    try {
      await favoritesStore.fetchFavorites();
    } catch (error) {
      toastStore.showError('Failed to load favorites');
    } finally {
      isLoading.value = false;
    }

    window.addEventListener('keydown', handleKeydown);
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
  });
</script>
