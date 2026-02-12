<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import BaseButton from '../components/ui/BaseButton.vue';
import BaseInput from '../components/ui/BaseInput.vue';
import { login, me } from '../services/auth';
import { ApiError } from '../services/http';
import { useAuthStore } from '../stores/authStore';
import { useNotificationStore } from '../stores/notificationStore';

const router = useRouter();
const { setAuth, setProfile } = useAuthStore();
const { push } = useNotificationStore();

const form = reactive({
  email: '',
  password: '',
});

const loading = ref(false);

async function onSubmit() {
  loading.value = true;
  try {
    const response = await login(form);
    setAuth(response.token);

    const profile = await me(response.token);
    setProfile(profile);

    push('success', 'Login success');
    await router.push({ name: 'dashboard' });
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Login failed';
    push('error', message);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-100 via-sky-50 to-indigo-100 px-4 dark:from-slate-900 dark:via-slate-800 dark:to-indigo-900">
    <div class="pointer-events-none absolute -left-20 -top-16 h-72 w-72 rounded-full bg-sky-300/30 blur-3xl dark:bg-sky-600/20"></div>
    <div class="pointer-events-none absolute -right-24 bottom-8 h-80 w-80 rounded-full bg-indigo-300/30 blur-3xl dark:bg-indigo-600/20"></div>
    <section class="relative w-full max-w-md rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-6 shadow-[0_24px_48px_-24px_rgba(15,23,42,0.5)]">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Login</h1>
      <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Sign in to manage your documents.</p>

      <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
        <BaseInput v-model="form.email" type="email" label="Email" required />
        <BaseInput v-model="form.password" type="password" label="Password" required />

        <div class="flex items-center justify-between">
          <router-link :to="{ name: 'register' }" class="text-sm text-sky-700 hover:text-sky-900 hover:underline dark:text-sky-400 dark:hover:text-sky-300">
            Create account
          </router-link>
          <BaseButton type="submit" :disabled="loading">{{ loading ? 'Loading...' : 'Login' }}</BaseButton>
        </div>
      </form>
    </section>
  </main>
</template>
