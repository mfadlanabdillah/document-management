<script setup lang="ts">
import { computed } from 'vue';

const modelValue = defineModel<string | number>({ required: true });

const props = withDefaults(
  defineProps<{
    label?: string;
    type?: string;
    placeholder?: string;
    required?: boolean;
  }>(),
  {
    label: '',
    type: 'text',
    placeholder: '',
    required: false,
  },
);

const inputValue = computed<string>({
  get: () => String(modelValue.value ?? ''),
  set: (value) => {
    if (props.type === 'number') {
      if (value.trim() === '') {
        modelValue.value = '';
        return;
      }

      const parsed = Number(value);
      modelValue.value = Number.isNaN(parsed) ? value : parsed;
      return;
    }

    modelValue.value = value;
  },
});
</script>

<template>
  <label class="block space-y-1.5">
    <span v-if="label" class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 dark:text-slate-400">{{ label }}</span>
    <input
      v-model="inputValue"
      :type="type"
      :placeholder="placeholder"
      :required="required"
      class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:text-slate-400 dark:focus:border-sky-500 dark:focus:ring-sky-900/40"
    />
  </label>
</template>
