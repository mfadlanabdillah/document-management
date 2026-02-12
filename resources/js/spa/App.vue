<script setup lang="ts">
import { onMounted } from 'vue';
import NotificationStack from './components/ui/NotificationStack.vue';
import { me } from './services/auth';
import { useAuthStore } from './stores/authStore';

const { state, setProfile, clearAuth } = useAuthStore();

onMounted(async () => {
  if (!state.token) {
    return;
  }

  try {
    const profile = await me(state.token);
    setProfile(profile);
  } catch {
    clearAuth();
  }
});
</script>

<template>
  <router-view />
  <NotificationStack />
</template>
