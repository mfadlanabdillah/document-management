<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Moon, Sun } from 'lucide-vue-next';
import BaseButton from '../ui/BaseButton.vue';
import { useAuthStore } from '../../stores/authStore';
import { useTheme } from '../../composables/useTheme';

const route = useRoute();
const router = useRouter();
const { state, clearAuth } = useAuthStore();
const { isDark, toggleTheme } = useTheme();

function logout() {
  clearAuth();
  router.push({ name: 'login' });
}

const isMasterDataActive = computed(() => route.name === 'master-data');
const isUsersActive = computed(() => route.name === 'users');
</script>

<template>
  <header class="sticky top-0 z-20 border-b border-white/40 bg-white/65 backdrop-blur-xl dark:backdrop-brightness-75 dark:border-slate-800 dark:bg-slate-900/70">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
      <router-link :to="{ name: 'dashboard' }" class="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-slate-100 dark:text-slate-100">
        <span class="inline-block h-2.5 w-2.5 rounded-full bg-gradient-to-r from-sky-500 to-indigo-600"></span>
        Document Management System
      </router-link>

      <div class="flex items-center gap-2">
        <router-link
          v-if="state.role === 'admin'"
          :to="{ name: 'master-data' }"
          class="rounded-xl px-3 py-2 text-sm font-medium transition"
          :class="
            isMasterDataActive
              ? 'border border-sky-600 bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-[0_10px_24px_-12px_rgba(2,132,199,0.8)]'
              : 'border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/85 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:bg-slate-800/60 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-700'
          "
        >
          Master Data
        </router-link>

        <router-link
          v-if="state.role === 'admin'"
          :to="{ name: 'users' }"
          class="rounded-xl px-3 py-2 text-sm font-medium transition"
          :class="
            isUsersActive
              ? 'border border-sky-600 bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-[0_10px_24px_-12px_rgba(2,132,199,0.8)]'
              : 'border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/85 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:bg-slate-800/60 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-700'
          "
        >
          Users
        </router-link>

        <button
          type="button"
          class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/85 px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 transition hover:bg-slate-100 dark:bg-slate-800/60 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-100 dark:hover:bg-slate-700"
          @click="toggleTheme"
        >
          <component :is="isDark ? Sun : Moon" class="h-4 w-4" />
          {{ isDark ? 'Light' : 'Dark' }}
        </button>

        <span
          v-if="state.email"
          class="hidden rounded-xl border border-white/60 bg-white/70 px-3 py-2 text-sm text-slate-600 dark:text-slate-400 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 sm:inline"
        >
          {{ state.email }}
        </span>
        <BaseButton variant="secondary" @click="logout">Logout</BaseButton>
      </div>
    </div>
  </header>
</template>
