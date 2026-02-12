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
  <main class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
    <section class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-semibold text-slate-900">Login</h1>
      <p class="mt-1 text-sm text-slate-600">Sign in to manage your documents.</p>

      <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
        <BaseInput v-model="form.email" type="email" label="Email" required />
        <BaseInput v-model="form.password" type="password" label="Password" required />

        <div class="flex items-center justify-between">
          <router-link :to="{ name: 'register' }" class="text-sm text-sky-700 hover:underline">
            Create account
          </router-link>
          <BaseButton type="submit" :disabled="loading">{{ loading ? 'Loading...' : 'Login' }}</BaseButton>
        </div>
      </form>
    </section>
  </main>
</template>
