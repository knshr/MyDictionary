import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useDictionaryStore = defineStore('dictionary', () => {
  const searchResults = ref(null);
  const searchHistory = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const currentQuery = ref('');

  const hasResults = computed(() => searchResults.value !== null);
  const hasError = computed(() => error.value !== null);
  const totalSearches = computed(() => searchHistory.value.length);
  const todaySearches = computed(() => {
    const today = new Date().toDateString();
    return searchHistory.value.filter(
      item => new Date(item.timestamp).toDateString() === today
    ).length;
  });

  const searchWord = async query => {
    if (!query.trim()) return;

    isLoading.value = true;
    error.value = null;
    currentQuery.value = query.trim();

    try {
      const response = await axios.get(
        `/api/dictionary/search?q=${encodeURIComponent(query.trim())}`
      );

      if (response.data.success) {
        searchResults.value = response.data.data;
        addToHistory(query.trim());
      } else {
        error.value = response.data.message || 'Word not found';
        searchResults.value = null;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to search word';
      searchResults.value = null;
    } finally {
      isLoading.value = false;
    }
  };

  const addToHistory = query => {
    const existingIndex = searchHistory.value.findIndex(
      item => item.query === query
    );
    if (existingIndex > -1) {
      searchHistory.value.splice(existingIndex, 1);
    }
    searchHistory.value.unshift({
      query,
      timestamp: new Date().toISOString(),
    });

    // Keep only last 10 searches
    if (searchHistory.value.length > 10) {
      searchHistory.value = searchHistory.value.slice(0, 10);
    }
  };

  const clearHistory = () => {
    searchHistory.value = [];
  };

  const clearResults = () => {
    searchResults.value = null;
    error.value = null;
    currentQuery.value = '';
  };

  const getPronunciation = phonetics => {
    if (!phonetics || !Array.isArray(phonetics)) return null;

    // Find phonetic with audio
    const withAudio = phonetics.find(p => p.audio);
    if (withAudio) return withAudio;

    // Fallback to first phonetic
    return phonetics[0] || null;
  };

  const playAudio = async audioUrl => {
    try {
      const audio = new Audio(audioUrl);
      await audio.play();
    } catch (err) {
      console.error('Failed to play audio:', err);
    }
  };

  return {
    // State
    searchResults,
    searchHistory,
    isLoading,
    error,
    currentQuery,

    // Computed
    hasResults,
    hasError,
    totalSearches,
    todaySearches,

    // Actions
    searchWord,
    addToHistory,
    clearHistory,
    clearResults,
    getPronunciation,
    playAudio,
  };
});
