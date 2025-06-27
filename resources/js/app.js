import './bootstrap.js';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';

// Import locales
import en from './locales/en.js';
import tl from './locales/tl.js';
import ja from './locales/ja.js';

// Import components
import Register from './Pages/Register.vue';
import Dashboard from './Pages/Dashboard/DashboardLayout.vue';
import Dictionary from './Pages/Dictionary.vue';
import Home from './Pages/Home.vue';

// Get initial language from localStorage
const initialLanguage = localStorage.getItem('language') || 'en';

// Create i18n instance
const i18n = createI18n({
  legacy: false,
  locale: initialLanguage,
  fallbackLocale: 'en',
  messages: {
    en,
    tl,
    ja,
  },
});

// Create Pinia instance
const pinia = createPinia();

createInertiaApp({
  resolve: name =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    ),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });

    app.use(plugin);
    app.use(pinia);
    app.use(i18n);

    app.mount(el);
  },
});
