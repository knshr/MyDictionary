<template>
  <div class="relative">
    <!-- Notes Display -->
    <div v-if="!isEditing" class="group">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <div
            v-if="favorite.notes"
            class="text-sm text-gray-700 bg-gray-50 p-3 rounded-md"
          >
            {{ favorite.notes }}
          </div>
          <div v-else class="text-sm text-gray-500 italic">
            {{ $t('favorites.noNotes') }}
          </div>
        </div>
        <button
          @click="startEditing"
          class="ml-2 p-1 text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
          title="$t('favorites.edit') + ' ' + $t('favorites.notes').toLowerCase()"
        >
          <svg
            class="w-4 h-4"
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
      </div>
    </div>

    <!-- Notes Editor -->
    <div v-else class="space-y-3">
      <textarea
        v-model="editedNotes"
        rows="3"
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
        placeholder="$t('favorites.addNotes')"
      ></textarea>

      <div class="flex justify-end space-x-2">
        <button
          @click="cancelEditing"
          class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition-colors duration-200"
        >
          {{ $t('favorites.cancel') }}
        </button>
        <button
          @click="saveNotes"
          :disabled="isSaving"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        >
          <span v-if="isSaving">{{ $t('auth.saving') }}</span>
          <span v-else>{{ $t('favorites.save') }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue';
  import { useFavoritesStore } from '@/stores/favorites';

  const props = defineProps({
    favorite: {
      type: Object,
      required: true,
    },
  });

  const emit = defineEmits(['updated']);

  const favoritesStore = useFavoritesStore();
  const isEditing = ref(false);
  const editedNotes = ref('');
  const isSaving = ref(false);

  const startEditing = () => {
    editedNotes.value = props.favorite.notes || '';
    isEditing.value = true;
  };

  const cancelEditing = () => {
    isEditing.value = false;
    editedNotes.value = '';
  };

  const saveNotes = async () => {
    isSaving.value = true;

    try {
      const result = await favoritesStore.updateFavorite(
        props.favorite.id,
        editedNotes.value
      );
      if (result.success) {
        isEditing.value = false;
        emit('updated', result.data);
      }
    } catch (error) {
      console.error('Failed to save notes:', error);
    } finally {
      isSaving.value = false;
    }
  };
</script>
