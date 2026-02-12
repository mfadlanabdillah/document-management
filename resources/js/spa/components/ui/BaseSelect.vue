<script setup lang="ts">
const modelValue = defineModel<string>({ required: true });

withDefaults(
  defineProps<{
    label?: string;
    options: Array<{ label: string; value: string }>;
  }>(),
  {
    label: '',
  },
);
</script>

<template>
  <label class="block space-y-1">
    <span v-if="label" class="text-sm font-medium text-slate-700">{{ label }}</span>
    <select
      v-model="modelValue"
      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-slate-500"
    >
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
  </label>
</template>
