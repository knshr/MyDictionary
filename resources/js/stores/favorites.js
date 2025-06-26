import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useFavoritesStore = defineStore('favorites', () => {
  const favorites = ref([]);
  const isLoading = ref(false);
  const error = ref(null);

  const hasFavorites = computed(() => favorites.value.length > 0);
  const favoritesCount = computed(() => favorites.value.length);

  const fetchFavorites = async () => {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await axios.get('/api/favorites');
      favorites.value = response.data.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch favorites';
    } finally {
      isLoading.value = false;
    }
  };

  const addFavorite = async (word, definition, notes = '') => {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await axios.post('/api/favorites', {
        word,
        definition,
        notes,
      });

      favorites.value.unshift(response.data.data);
      return { success: true, data: response.data.data };
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to add favorite';
      return { success: false, error: error.value };
    } finally {
      isLoading.value = false;
    }
  };

  const updateFavorite = async (id, notes) => {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await axios.put(`/api/favorites/${id}`, { notes });

      const index = favorites.value.findIndex(fav => fav.id === id);
      if (index !== -1) {
        favorites.value[index] = response.data.data;
      }

      return { success: true, data: response.data.data };
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update favorite';
      return { success: false, error: error.value };
    } finally {
      isLoading.value = false;
    }
  };

  const removeFavorite = async id => {
    isLoading.value = true;
    error.value = null;

    try {
      await axios.delete(`/api/favorites/${id}`);

      const index = favorites.value.findIndex(fav => fav.id === id);
      if (index !== -1) {
        favorites.value.splice(index, 1);
      }

      return { success: true };
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to remove favorite';
      return { success: false, error: error.value };
    } finally {
      isLoading.value = false;
    }
  };

  const isFavorite = word => {
    return favorites.value.some(
      fav => fav.word.toLowerCase() === word.toLowerCase()
    );
  };

  const getFavorite = word => {
    return favorites.value.find(
      fav => fav.word.toLowerCase() === word.toLowerCase()
    );
  };

  const clearError = () => {
    error.value = null;
  };

  return {
    // State
    favorites,
    isLoading,
    error,

    // Computed
    hasFavorites,
    favoritesCount,

    // Actions
    fetchFavorites,
    addFavorite,
    updateFavorite,
    removeFavorite,
    isFavorite,
    getFavorite,
    clearError,
  };
});
