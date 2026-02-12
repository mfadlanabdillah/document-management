import { computed, ref } from 'vue';

type Theme = 'light' | 'dark';

const STORAGE_KEY = 'document_app_theme';
const theme = ref<Theme>('light');
let initialized = false;

function applyTheme(value: Theme) {
  theme.value = value;
  document.documentElement.classList.toggle('dark', value === 'dark');
  localStorage.setItem(STORAGE_KEY, value);
}

function initTheme() {
  if (initialized) {
    return;
  }

  initialized = true;
  const saved = localStorage.getItem(STORAGE_KEY);

  if (saved === 'dark' || saved === 'light') {
    applyTheme(saved);
    return;
  }

  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  applyTheme(prefersDark ? 'dark' : 'light');
}

export function useTheme() {
  const isDark = computed(() => theme.value === 'dark');

  function toggleTheme() {
    applyTheme(theme.value === 'dark' ? 'light' : 'dark');
  }

  return {
    theme,
    isDark,
    initTheme,
    toggleTheme,
  };
}
