<template>
  <main class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
    <section class="rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
      <h1 class="text-3xl font-semibold text-slate-900">404</h1>
      <p class="mt-2 text-slate-600">Page not found.</p>
      <router-link :to="{ name: 'dashboard' }" class="mt-4 inline-block text-sky-700 hover:underline">
        Back to dashboard
      </router-link>
    </section>
  </main>
</template>
