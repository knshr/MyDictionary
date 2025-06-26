<template>
  <DashboardLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-6">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">My Favorites</h1>
              <p class="mt-1 text-sm text-gray-500">
                Your saved words and definitions
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
                <span v-else>Refresh</span>
              </button>
              <button
                @click="navigateToDictionary"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
              >
                Search Dictionary
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
                placeholder="Search your favorites..."
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div class="flex items-center space-x-4">
              <select
                v-model="sortBy"
                class="px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="created_at">Date Added</option>
                <option value="word">Word</option>
                <option value="updated_at">Last Modified</option>
              </select>
              <button
                @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
                class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                :title="sortOrder === 'asc' ? 'Ascending' : 'Descending'"
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
                <p class="text-sm font-medium text-gray-500">Total Favorites</p>
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
                <p class="text-sm font-medium text-gray-500">With Notes</p>
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
                <p class="text-sm font-medium text-gray-500">Recently Added</p>
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
          <p class="text-gray-600">Loading your favorites...</p>
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
              {{ searchQuery ? 'No favorites found' : 'No favorites yet' }}
            </h3>
            <p class="text-gray-500 mb-6">
              {{
                searchQuery
                  ? 'Try adjusting your search terms.'
                  : 'Start by searching for words in the dictionary and adding them to your favorites.'
              }}
            </p>
            <button
              @click="navigateToDictionary"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
            >
              Search Dictionary
            </button>
          </div>
        </div>

        <div v-else class="space-y-6">
          <div
            v-for="favorite in filteredFavorites"
            :key="favorite.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <h3 class="text-xl font-semibold text-gray-900">
                    {{ favorite.word }}
                  </h3>
                  <FavoriteButton
                    :word="favorite.word"
                    :definition="favorite.definition"
                    @added="handleFavoriteAdded"
                    @removed="handleFavoriteRemoved"
                  />
                </div>
                <p class="text-gray-700">
                  {{ favorite.definition }}
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="searchWord(favorite.word)"
                  class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                  title="Search this word"
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
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                  </svg>
                </button>
                <button
                  @click="removeFavorite(favorite.id)"
                  class="p-2 text-red-400 hover:text-red-600 transition-colors duration-200"
                  title="Remove from favorites"
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
            <div class="border-t pt-4">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Notes</h4>
              <FavoriteNotes
                :favorite="favorite"
                @updated="handleFavoriteUpdated"
              />
            </div>

            <div class="mt-4 text-xs text-gray-500">
              Added {{ formatDate(favorite.created_at) }}
              <span v-if="favorite.updated_at !== favorite.created_at">
                • Updated {{ formatDate(favorite.updated_at) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue';
  import { router } from '@inertiajs/vue3';
  import { useFavoritesStore } from '@/stores/favorites';
  import DashboardLayout from '@/Pages/Dashboard/DashboardLayout.vue';
  import FavoriteButton from '@/components/FavoriteButton.vue';
  import FavoriteNotes from '@/components/FavoriteNotes.vue';

  const favoritesStore = useFavoritesStore();
  const searchQuery = ref('');
  const sortBy = ref('created_at');
  const sortOrder = ref('desc');

  const isLoading = computed(() => favoritesStore.isLoading);
  const favorites = computed(() => favoritesStore.favorites);
  const favoritesCount = computed(() => favoritesStore.favoritesCount);

  const filteredFavorites = computed(() => {
    let filtered = favorites.value;

    // Search filter
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      filtered = filtered.filter(
        fav =>
          fav.word.toLowerCase().includes(query) ||
          fav.definition.toLowerCase().includes(query) ||
          (fav.notes && fav.notes.toLowerCase().includes(query))
      );
    }

    // Sort
    filtered.sort((a, b) => {
      let aVal = a[sortBy.value];
      let bVal = b[sortBy.value];

      if (sortBy.value === 'word') {
        aVal = aVal.toLowerCase();
        bVal = bVal.toLowerCase();
      }

      if (sortOrder.value === 'asc') {
        return aVal > bVal ? 1 : -1;
      } else {
        return aVal < bVal ? 1 : -1;
      }
    });

    return filtered;
  });

  const favoritesWithNotes = computed(() => {
    return favorites.value.filter(fav => fav.notes && fav.notes.trim()).length;
  });

  const recentFavorites = computed(() => {
    const oneWeekAgo = new Date();
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
    return favorites.value.filter(fav => new Date(fav.created_at) > oneWeekAgo)
      .length;
  });

  onMounted(() => {
    favoritesStore.fetchFavorites();
  });

  const refreshFavorites = () => {
    favoritesStore.fetchFavorites();
  };

  const navigateToDictionary = () => {
    router.visit('/dictionary');
  };

  const searchWord = word => {
    router.visit(`/dictionary?q=${encodeURIComponent(word)}`);
  };

  const removeFavorite = async id => {
    if (
      confirm('Are you sure you want to remove this word from your favorites?')
    ) {
      await favoritesStore.removeFavorite(id);
    }
  };

  const handleFavoriteAdded = favorite => {
    // Refresh the list to show the new favorite
    favoritesStore.fetchFavorites();
  };

  const handleFavoriteRemoved = favorite => {
    // The store already updates the list
  };

  const handleFavoriteUpdated = favorite => {
    // The store already updates the list
  };

  const formatDate = dateString => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));

    if (diffInDays === 0) return 'Today';
    if (diffInDays === 1) return 'Yesterday';
    if (diffInDays < 7) return `${diffInDays} days ago`;
    if (diffInDays < 30) return `${Math.floor(diffInDays / 7)} weeks ago`;
    if (diffInDays < 365) return `${Math.floor(diffInDays / 30)} months ago`;
    return `${Math.floor(diffInDays / 365)} years ago`;
  };
</script>
