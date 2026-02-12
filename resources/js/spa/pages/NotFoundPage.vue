<template>
  <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-100 via-sky-50 to-indigo-100 px-4">
    <div class="pointer-events-none absolute -left-24 top-4 h-72 w-72 rounded-full bg-sky-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-24 bottom-0 h-72 w-72 rounded-full bg-indigo-300/30 blur-3xl"></div>
    <section class="relative rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-8 text-center shadow-[0_24px_48px_-24px_rgba(15,23,42,0.5)]">
      <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">404</h1>
      <p class="mt-2 text-slate-600 dark:text-slate-400">Page not found.</p>
      <router-link :to="{ name: 'dashboard' }" class="mt-4 inline-block text-sky-700 hover:underline">
        Back to dashboard
      </router-link>
    </section>
  </main>
</template>
