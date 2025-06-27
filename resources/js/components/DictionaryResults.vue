<template>
  <div v-if="results" class="space-y-6">
    <!-- Word Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ results.word }}
          </h1>
          <div v-if="pronunciation" class="flex items-center space-x-3">
            <span class="text-lg text-gray-600 font-mono">{{
              pronunciation.text
            }}</span>
            <button
              v-if="pronunciation.audio"
              @click="playAudio(pronunciation.audio)"
              class="p-2 bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 transition-colors duration-200"
              :title="$t('dictionary.pronunciation')"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.794L4.383 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.383l4.617-3.794a1 1 0 011.383.87zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <FavoriteButton
            :word="results.word"
            :definition="firstDefinition"
            @added="handleFavoriteAdded"
            @removed="handleFavoriteRemoved"
          />
          <button
            @click="$emit('clear')"
            class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
            :title="$t('dictionary.clearResults')"
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
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Meanings -->
    <div
      v-for="(meaning, index) in results.meanings"
      :key="index"
      class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
    >
      <!-- Part of Speech -->
      <div class="flex items-center mb-4">
        <span
          class="inline-block px-3 py-1 text-sm font-medium text-blue-600 bg-blue-100 rounded-full"
        >
          {{ meaning.partOfSpeech }}
        </span>
      </div>

      <!-- Definitions -->
      <div class="space-y-4">
        <div
          v-for="(definition, defIndex) in meaning.definitions"
          :key="defIndex"
          class="border-l-4 border-gray-200 pl-4"
        >
          <div class="mb-2">
            <span class="text-sm font-medium text-gray-500"
              >{{ defIndex + 1 }}.</span
            >
            <span class="text-gray-900 ml-2">{{ definition.definition }}</span>
          </div>

          <!-- Example -->
          <div v-if="definition.example" class="mt-2 text-gray-600 italic">
            <span class="text-sm">"{{ definition.example }}"</span>
          </div>

          <!-- Synonyms -->
          <div
            v-if="definition.synonyms && definition.synonyms.length > 0"
            class="mt-3"
          >
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-sm font-medium text-gray-500"
                >{{ $t('dictionary.synonyms') }}:</span
              >
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="synonym in definition.synonyms.slice(0, 5)"
                  :key="synonym"
                  class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded-md hover:bg-green-200 cursor-pointer transition-colors duration-150"
                  @click="$emit('search-synonym', synonym)"
                >
                  {{ synonym }}
                </span>
                <span
                  v-if="definition.synonyms.length > 5"
                  class="text-xs text-gray-500"
                >
                  +{{ definition.synonyms.length - 5 }} {{ $t('common.more') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Results Message -->
    <div
      v-if="results.meanings.length === 0"
      class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center"
    >
      <div class="text-gray-500">
        <svg
          class="mx-auto h-12 w-12 text-gray-400 mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
        <p class="text-lg font-medium">{{ $t('dictionary.noResults') }}</p>
        <p class="text-sm">
          {{ $t('dictionary.tryDifferentWord') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed } from 'vue';
  import { useDictionaryStore } from '@/stores/dictionary';
  import FavoriteButton from '@/components/FavoriteButton.vue';

  const props = defineProps({
    results: {
      type: Object,
      required: true,
    },
  });

  const emit = defineEmits([
    'clear',
    'search-synonym',
    'favorite-added',
    'favorite-removed',
  ]);

  const dictionaryStore = useDictionaryStore();

  const pronunciation = computed(() => {
    return dictionaryStore.getPronunciation(props.results.phonetics);
  });

  const firstDefinition = computed(() => {
    if (!props.results.meanings || props.results.meanings.length === 0)
      return '';
    const firstMeaning = props.results.meanings[0];
    if (!firstMeaning.definitions || firstMeaning.definitions.length === 0)
      return '';
    return firstMeaning.definitions[0].definition;
  });

  const playAudio = audioUrl => {
    dictionaryStore.playAudio(audioUrl);
  };

  const handleFavoriteAdded = favorite => {
    emit('favorite-added', favorite);
  };

  const handleFavoriteRemoved = favorite => {
    emit('favorite-removed', favorite);
  };
</script>
