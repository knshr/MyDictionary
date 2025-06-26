<template>
  <button
    @click="toggleFavorite"
    :disabled="isLoading"
    class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
    :class="buttonClasses"
    :title="buttonTitle"
  >
    <svg
      v-if="isFavorite"
      class="w-5 h-5 mr-1"
      fill="currentColor"
      viewBox="0 0 20 20"
    >
      <path
        fill-rule="evenodd"
        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
        clip-rule="evenodd"
      />
    </svg>
    <svg
      v-else
      class="w-5 h-5 mr-1"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
      />
    </svg>

    <span v-if="showText">
      {{ isFavorite ? 'Remove' : 'Add' }}
    </span>

    <svg
      v-if="isLoading"
      class="animate-spin w-4 h-4 ml-1"
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
  </button>
</template>

<script setup>
  import { computed } from 'vue';
  import { useFavoritesStore } from '@/stores/favorites';

  const props = defineProps({
    word: {
      type: String,
      required: true,
    },
    definition: {
      type: String,
      required: true,
    },
    showText: {
      type: Boolean,
      default: false,
    },
    size: {
      type: String,
      default: 'md', // sm, md, lg
      validator: value => ['sm', 'md', 'lg'].includes(value),
    },
  });

  const emit = defineEmits(['added', 'removed']);

  const favoritesStore = useFavoritesStore();

  const isFavorite = computed(() => favoritesStore.isFavorite(props.word));
  const isLoading = computed(() => favoritesStore.isLoading);

  const buttonClasses = computed(() => {
    const baseClasses = 'focus:ring-red-500';

    if (isFavorite.value) {
      return `${baseClasses} bg-red-100 text-red-700 hover:bg-red-200 border border-red-300`;
    } else {
      return `${baseClasses} bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300`;
    }
  });

  const buttonTitle = computed(() => {
    if (isLoading.value) return 'Loading...';
    return isFavorite.value ? 'Remove from favorites' : 'Add to favorites';
  });

  const toggleFavorite = async () => {
    if (isLoading.value) return;

    if (isFavorite.value) {
      const favorite = favoritesStore.getFavorite(props.word);
      if (favorite) {
        const result = await favoritesStore.removeFavorite(favorite.id);
        if (result.success) {
          emit('removed', favorite);
        }
      }
    } else {
      const result = await favoritesStore.addFavorite(
        props.word,
        props.definition
      );
      if (result.success) {
        emit('added', result.data);
      }
    }
  };
</script>
