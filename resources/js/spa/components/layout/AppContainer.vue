<template>
  <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-100 via-sky-50 to-indigo-100 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950">
    <div class="pointer-events-none absolute -left-20 -top-20 h-72 w-72 rounded-full bg-sky-300/30 blur-3xl dark:bg-sky-700/25"></div>
    <div class="pointer-events-none absolute -right-20 top-24 h-80 w-80 rounded-full bg-indigo-300/30 blur-3xl dark:bg-indigo-700/25"></div>
    <div class="pointer-events-none absolute bottom-0 left-1/3 h-64 w-64 rounded-full bg-cyan-200/40 blur-3xl dark:bg-cyan-700/20"></div>
    <div class="relative">
      <slot />
    </div>
  </div>
</template>
