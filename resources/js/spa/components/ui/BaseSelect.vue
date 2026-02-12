<script setup lang="ts">
const modelValue = defineModel<string | number | boolean>({ required: true });

withDefaults(
  defineProps<{
    label?: string;
    options: Array<{ label: string; value: string | number | boolean }>;
  }>(),
  {
    label: '',
  },
);
</script>

<template>
  <label class="block space-y-1.5">
    <span v-if="label" class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 dark:text-slate-400">{{ label }}</span>
    <select
      v-model="modelValue"
      class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/40"
    >
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
  </label>
</template>
