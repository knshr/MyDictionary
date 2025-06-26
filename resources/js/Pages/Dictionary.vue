<template>
  <DashboardLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-6">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Dictionary</h1>
              <p class="mt-1 text-sm text-gray-500">
                Search for word definitions, pronunciations, and examples
              </p>
            </div>
            <div class="flex items-center space-x-4">
              <button
                @click="showHistory = !showHistory"
                class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                title="Toggle search history"
              >
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
              </button>
              <button
                @click="clearAll"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200"
              >
                Clear All
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-3">
            <!-- Search Section -->
            <div
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6"
            >
              <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Search Words
              </h2>
              <DictionarySearch @search="handleSearch" />
            </div>

            <!-- Results Section -->
            <div v-if="dictionaryStore.hasResults">
              <DictionaryResults
                :results="dictionaryStore.searchResults"
                @clear="clearResults"
                @search-synonym="searchSynonym"
                @favorite-added="handleFavoriteAdded"
                @favorite-removed="handleFavoriteRemoved"
              />
            </div>

            <!-- Empty State -->
            <div
              v-else-if="!dictionaryStore.isLoading"
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center"
            >
              <div class="max-w-md mx-auto">
                <svg
                  class="mx-auto h-16 w-16 text-gray-400 mb-4"
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                  Start Searching
                </h3>
                <p class="text-gray-500 mb-6">
                  Enter a word above to find its definition, pronunciation, and
                  usage examples.
                </p>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-blue-600 font-medium">Definitions</div>
                    <div class="text-blue-500">Clear explanations</div>
                  </div>
                  <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-green-600 font-medium">Pronunciation</div>
                    <div class="text-green-500">Audio & phonetic</div>
                  </div>
                  <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-purple-600 font-medium">Examples</div>
                    <div class="text-purple-500">Usage in context</div>
                  </div>
                  <div class="text-center p-3 bg-orange-50 rounded-lg">
                    <div class="text-orange-600 font-medium">Synonyms</div>
                    <div class="text-orange-500">Related words</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Loading State -->
            <div
              v-if="dictionaryStore.isLoading"
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center"
            >
              <div
                class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"
              ></div>
              <p class="text-gray-600">Searching for definitions...</p>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1">
            <!-- Search History -->
            <div
              v-if="showHistory"
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6"
            >
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                  Recent Searches
                </h3>
                <button
                  @click="dictionaryStore.clearHistory"
                  class="text-sm text-red-600 hover:text-red-800"
                >
                  Clear
                </button>
              </div>
              <div
                v-if="dictionaryStore.searchHistory.length > 0"
                class="space-y-2"
              >
                <button
                  v-for="item in dictionaryStore.searchHistory"
                  :key="item.timestamp"
                  @click="searchSynonym(item.query)"
                  class="w-full text-left p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors duration-150 flex justify-between items-center"
                >
                  <span class="font-medium">{{ item.query }}</span>
                  <span class="text-xs text-gray-500">{{
                    formatTime(item.timestamp)
                  }}</span>
                </button>
              </div>
              <div v-else class="text-center text-gray-500 text-sm py-4">
                No recent searches
              </div>
            </div>

            <!-- Quick Stats -->
            <div
              class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
              <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Quick Stats
              </h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600"> Total Searches </span>
                  <span class="text-sm font-medium text-gray-900">
                    {{ dictionaryStore.totalSearches }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600"> Today's Searches </span>
                  <span class="text-sm font-medium text-gray-900">
                    {{ dictionaryStore.todaySearches }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600"> Favorites </span>
                  <span class="text-sm font-medium text-gray-900">
                    {{ favoritesStore.favoritesCount }}
                  </span>
                </div>
              </div>
            </div>

            <!-- API Info -->
            <div class="bg-blue-50 rounded-lg p-6 mt-6">
              <h3 class="text-lg font-semibold text-blue-900 mb-2">
                Powered by
              </h3>
              <p class="text-sm text-blue-700 mb-3">
                Free Dictionary API provides comprehensive word definitions,
                pronunciations, and examples.
              </p>
              <a
                href="https://dictionaryapi.dev/"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm text-blue-600 hover:text-blue-800 underline"
              >
                Learn more →
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
  import { ref, onMounted, watch } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  import { useDictionaryStore } from '@/stores/dictionary';
  import { useFavoritesStore } from '@/stores/favorites';
  import DashboardLayout from '@/Pages/Dashboard/DashboardLayout.vue';
  import DictionarySearch from '@/components/DictionarySearch.vue';
  import DictionaryResults from '@/components/DictionaryResults.vue';

  const page = usePage();
  const dictionaryStore = useDictionaryStore();
  const favoritesStore = useFavoritesStore();
  const showHistory = ref(false);

  // Handle URL query parameter for initial search
  onMounted(() => {
    const query = page.props.query;
    if (query) {
      handleSearch(query);
    }
  });

  const handleSearch = async query => {
    await dictionaryStore.searchWord(query);
  };

  const clearResults = () => {
    dictionaryStore.clearResults();
  };

  const clearAll = () => {
    dictionaryStore.clearResults();
    dictionaryStore.clearHistory();
  };

  const searchSynonym = synonym => {
    handleSearch(synonym);
  };

  const handleFavoriteAdded = favorite => {
    // Show a success message or update UI
    console.log('Favorite added:', favorite);
  };

  const handleFavoriteRemoved = favorite => {
    // Show a success message or update UI
    console.log('Favorite removed:', favorite);
  };

  const formatTime = timestamp => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));

    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
    return `${Math.floor(diffInMinutes / 1440)}d ago`;
  };
</script>
