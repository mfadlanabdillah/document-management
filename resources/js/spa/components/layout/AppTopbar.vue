<script setup lang="ts">
import { useRouter } from 'vue-router';
import BaseButton from '../ui/BaseButton.vue';
import { useAuthStore } from '../../stores/authStore';

const router = useRouter();
const { state, clearAuth } = useAuthStore();

function logout() {
  clearAuth();
  router.push({ name: 'login' });
}
</script>

<template>
  <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
      <router-link :to="{ name: 'dashboard' }" class="text-lg font-semibold text-slate-900">
        Document Manager
      </router-link>

      <div class="flex items-center gap-2">
        <router-link
          v-if="state.role === 'admin'"
          :to="{ name: 'master-data' }"
          class="rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100"
        >
          Master Data
        </router-link>

        <router-link
          v-if="state.role === 'admin'"
          :to="{ name: 'users' }"
          class="rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100"
        >
          Users
        </router-link>

        <span v-if="state.email" class="hidden text-sm text-slate-600 sm:inline">{{ state.email }}</span>
        <BaseButton variant="secondary" @click="logout">Logout</BaseButton>
      </div>
    </div>
  </header>
</template>
