import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

export const useLanguageStore = defineStore('language', () => {
  const currentLanguage = ref(localStorage.getItem('language') || 'en');
  const { locale } = useI18n();

  const setLanguage = language => {
    currentLanguage.value = language;
    locale.value = language;
    localStorage.setItem('language', language);
  };

  const getCurrentLanguage = () => {
    return currentLanguage.value;
  };

  const initializeLanguage = () => {
    const savedLanguage = localStorage.getItem('language');
    if (savedLanguage) {
      setLanguage(savedLanguage);
    } else {
      setLanguage('en');
    }
  };

  return {
    currentLanguage,
    setLanguage,
    getCurrentLanguage,
    initializeLanguage,
  };
});
