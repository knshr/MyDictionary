<template>
  <div class="w-full" ref="containerRef">
    <!-- Search Input -->
    <div class="relative">
      <div class="relative">
        <input
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          @input="handleInput"
          @focus="showDropdown = true"
          type="text"
          :placeholder="$t('dictionary.searchPlaceholder')"
          class="w-full px-4 py-3 pl-12 pr-12 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
          :class="{ 'border-red-500': showError }"
        />
        <div
          class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
        >
          <svg
            class="h-6 w-6 text-gray-400"
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
        </div>
        <button
          @click="handleSearch"
          :disabled="isLoading || !searchQuery.trim()"
          class="absolute inset-y-0 right-0 px-4 flex items-center bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        >
          <svg
            v-if="isLoading"
            class="animate-spin h-5 w-5"
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
          <span v-else>{{ $t('navigation.search') }}</span>
        </button>
      </div>

      <!-- Search History Dropdown -->
      <div
        v-if="showDropdown && showHistory && searchHistory.length > 0"
        class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
      >
        <div class="p-2">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700">{{
              $t('dictionary.recentSearches')
            }}</span>
            <button
              @click="clearHistory"
              class="text-sm text-red-600 hover:text-red-800"
            >
              {{ $t('dictionary.clear') }}
            </button>
          </div>
          <div
            v-for="item in searchHistory"
            :key="item.timestamp"
            class="space-y-1"
          >
            <button
              @click="selectHistoryItem(item.query)"
              class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors duration-150 flex justify-between items-center"
            >
              <span>{{ item.query }}</span>
              <span class="text-xs text-gray-500">{{
                formatTime(item.timestamp)
              }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="showError" class="mt-2 text-sm text-red-600">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useDictionaryStore } from '@/stores/dictionary';

  const { t } = useI18n();

  const props = defineProps({
    placeholder: {
      type: String,
      default: 'Search for a word...',
    },
    showHistory: {
      type: Boolean,
      default: true,
    },
  });

  const emit = defineEmits(['search']);

  const dictionaryStore = useDictionaryStore();
  const searchQuery = ref('');

  const isLoading = computed(() => dictionaryStore.isLoading);
  const error = computed(() => dictionaryStore.error);
  const searchHistory = computed(() => dictionaryStore.searchHistory);
  const showError = computed(
    () => error.value && searchQuery.value === dictionaryStore.currentQuery
  );

  // Dropdown visibility state
  const showDropdown = ref(false);
  const containerRef = ref(null);

  function handleClickOutside(event) {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
      showDropdown.value = false;
    }
  }

  onMounted(() => {
    document.addEventListener('click', handleClickOutside);
  });
  onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
  });

  const handleSearch = () => {
    if (!searchQuery.value.trim()) return;
    dictionaryStore.searchWord(searchQuery.value);
    emit('search', searchQuery.value);
    showDropdown.value = false;
  };

  const handleInput = () => {
    // Clear error when user starts typing
    if (error.value && searchQuery.value !== dictionaryStore.currentQuery) {
      dictionaryStore.error = null;
    }
    showDropdown.value = true;
  };

  const selectHistoryItem = query => {
    searchQuery.value = query;
    handleSearch();
  };

  const clearHistory = () => {
    dictionaryStore.clearHistory();
  };

  const formatTime = timestamp => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));

    if (diffInMinutes < 1) return t('common.justNow');
    if (diffInMinutes < 60)
      return `${diffInMinutes}${t('common.minutes')} ${t('common.ago')}`;
    if (diffInMinutes < 1440)
      return `${Math.floor(diffInMinutes / 60)}${t('common.hours')} ${t('common.ago')}`;
    return date.toLocaleDateString();
  };

  // Watch for external search results
  watch(
    () => dictionaryStore.currentQuery,
    newQuery => {
      if (newQuery !== searchQuery.value) {
        searchQuery.value = newQuery;
      }
    }
  );
</script>
