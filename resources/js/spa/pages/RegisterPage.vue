<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import BaseButton from '../components/ui/BaseButton.vue';
import BaseInput from '../components/ui/BaseInput.vue';
import { register, me } from '../services/auth';
import { ApiError } from '../services/http';
import { useAuthStore } from '../stores/authStore';
import { useNotificationStore } from '../stores/notificationStore';

const router = useRouter();
const { setAuth, setProfile } = useAuthStore();
const { push } = useNotificationStore();

const form = reactive({
  email: '',
  password: '',
  password_confirmation: '',
});

const loading = ref(false);

async function onSubmit() {
  loading.value = true;
  try {
    const response = await register(form);
    setAuth(response.token);

    const profile = await me(response.token);
    setProfile(profile);

    push('success', 'Register success');
    await router.push({ name: 'dashboard' });
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Register failed';
    push('error', message);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-100 via-sky-50 to-indigo-100 px-4">
    <div class="pointer-events-none absolute -left-24 bottom-0 h-80 w-80 rounded-full bg-cyan-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-24 top-0 h-80 w-80 rounded-full bg-indigo-300/30 blur-3xl"></div>
    <section class="relative w-full max-w-md rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-6 shadow-[0_24px_48px_-24px_rgba(15,23,42,0.5)]">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Register</h1>
      <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Create account to use document manager.</p>

      <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
        <BaseInput v-model="form.email" type="email" label="Email" required />
        <BaseInput v-model="form.password" type="password" label="Password" required />
        <BaseInput
          v-model="form.password_confirmation"
          type="password"
          label="Confirm Password"
          required
        />

        <div class="flex items-center justify-between">
          <router-link :to="{ name: 'login' }" class="text-sm text-sky-700 hover:underline">Login</router-link>
          <BaseButton type="submit" :disabled="loading">{{ loading ? 'Loading...' : 'Register' }}</BaseButton>
        </div>
      </form>
    </section>
  </main>
</template>
