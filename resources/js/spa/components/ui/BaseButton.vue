<script setup lang="ts">
withDefaults(
  defineProps<{
    type?: 'button' | 'submit' | 'reset';
    variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
    disabled?: boolean;
  }>(),
  {
    type: 'button',
    variant: 'primary',
    disabled: false,
  },
);

const styles = {
  primary:
    'border border-sky-600 bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-[0_10px_24px_-12px_rgba(2,132,199,0.8)] hover:from-sky-500 hover:to-indigo-500',
  secondary:
    'border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/85 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:bg-slate-800/60 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-100 dark:hover:bg-slate-700',
  danger:
    'border border-rose-600 bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-[0_10px_24px_-12px_rgba(225,29,72,0.8)] hover:from-rose-500 hover:to-red-500',
  ghost:
    'border border-transparent bg-transparent text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800/60/80 dark:text-slate-300 dark:hover:bg-slate-800/70',
};
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-60"
    :class="styles[variant]"
  >
    <slot />
  </button>
</template>
