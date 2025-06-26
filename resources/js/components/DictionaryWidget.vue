<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <!-- Widget Header -->
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-900">Dictionary</h3>
        <p class="text-sm text-gray-500">Quick word lookup</p>
      </div>
      <button
        @click="isExpanded = !isExpanded"
        class="p-1 text-gray-400 hover:text-gray-600 transition-colors duration-200"
        :title="isExpanded ? 'Collapse' : 'Expand'"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            v-if="isExpanded"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 15l7-7 7 7"
          />
          <path
            v-else
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 9l-7 7-7-7"
          />
        </svg>
      </button>
    </div>

    <!-- Compact Search -->
    <div v-if="!isExpanded" class="space-y-4">
      <div class="relative">
        <input
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          type="text"
          placeholder="Search for a word..."
          class="w-full px-3 py-2 pl-10 pr-20 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
        />
        <div
          class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
        >
          <svg
            class="h-4 w-4 text-gray-400"
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
          class="absolute inset-y-0 right-0 px-3 flex items-center bg-blue-600 text-white text-sm rounded-r-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        >
          <svg
            v-if="isLoading"
            class="animate-spin h-4 w-4"
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
          <span v-else>Go</span>
        </button>
      </div>

      <!-- Quick Results Preview -->
      <div v-if="hasResults" class="space-y-3">
        <div class="border-t pt-3">
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-medium text-gray-900">
              {{ results.word }}
            </h4>
            <button
              @click="clearResults"
              class="text-xs text-gray-400 hover:text-gray-600"
            >
              Clear
            </button>
          </div>

          <div v-if="pronunciation" class="flex items-center space-x-2 mb-2">
            <span class="text-sm text-gray-600 font-mono">{{
              pronunciation.text
            }}</span>
            <button
              v-if="pronunciation.audio"
              @click="playAudio(pronunciation.audio)"
              class="p-1 bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition-colors duration-200"
              title="Play pronunciation"
            >
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.794L4.383 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.383l4.617-3.794a1 1 0 011.383.87zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </div>

          <div v-if="firstDefinition" class="text-sm text-gray-700">
            <span
              class="inline-block px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full mb-2"
            >
              {{ firstDefinition.partOfSpeech }}
            </span>
            <p class="text-gray-900">
              {{ firstDefinition.definition }}
            </p>
          </div>

          <div class="flex justify-between items-center mt-3">
            <button
              @click="isExpanded = true"
              class="text-xs text-blue-600 hover:text-blue-800"
            >
              View full results →
            </button>
            <span class="text-xs text-gray-500">
              {{ results.meanings.length }} meaning{{
                results.meanings.length !== 1 ? 's' : ''
              }}
            </span>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="hasError" class="text-sm text-red-600 bg-red-50 p-2 rounded">
        {{ error }}
      </div>
    </div>

    <!-- Expanded View -->
    <div v-else class="space-y-4">
      <DictionarySearch @search="handleSearch" :show-history="false" />

      <div v-if="hasResults">
        <DictionaryResults
          :results="results"
          @clear="clearResults"
          @search-synonym="searchSynonym"
        />
      </div>

      <div v-if="hasError" class="text-sm text-red-600 bg-red-50 p-3 rounded">
        {{ error }}
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="border-t pt-4 mt-4">
      <div class="flex justify-between items-center">
        <button
          @click="$emit('navigate', '/dictionary')"
          class="text-sm text-blue-600 hover:text-blue-800"
        >
          Open Dictionary →
        </button>
        <span class="text-xs text-gray-500">
          {{ searchHistory.length }} recent searches
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue';
  import { useDictionaryStore } from '@/stores/dictionary';
  import DictionarySearch from '@/components/DictionarySearch.vue';
  import DictionaryResults from '@/components/DictionaryResults.vue';

  const props = defineProps({
    initialQuery: {
      type: String,
      default: '',
    },
  });

  const emit = defineEmits(['navigate']);

  const dictionaryStore = useDictionaryStore();
  const searchQuery = ref(props.initialQuery);
  const isExpanded = ref(false);

  const isLoading = computed(() => dictionaryStore.isLoading);
  const error = computed(() => dictionaryStore.error);
  const results = computed(() => dictionaryStore.searchResults);
  const hasResults = computed(() => dictionaryStore.hasResults);
  const hasError = computed(() => dictionaryStore.hasError);
  const searchHistory = computed(() => dictionaryStore.searchHistory);

  const pronunciation = computed(() => {
    if (!results.value) return null;
    return dictionaryStore.getPronunciation(results.value.phonetics);
  });

  const firstDefinition = computed(() => {
    if (!results.value || !results.value.meanings.length) return null;
    const firstMeaning = results.value.meanings[0];
    if (!firstMeaning.definitions.length) return null;

    return {
      partOfSpeech: firstMeaning.partOfSpeech,
      definition: firstMeaning.definitions[0].definition,
    };
  });

  const handleSearch = query => {
    if (!query.trim()) return;
    dictionaryStore.searchWord(query);
    searchQuery.value = query;
  };

  const searchSynonym = word => {
    dictionaryStore.searchWord(word);
    searchQuery.value = word;
  };

  const clearResults = () => {
    dictionaryStore.clearResults();
    searchQuery.value = '';
  };

  const playAudio = audioUrl => {
    dictionaryStore.playAudio(audioUrl);
  };

  // Auto-search if initial query is provided
  if (props.initialQuery) {
    handleSearch(props.initialQuery);
  }
</script>
