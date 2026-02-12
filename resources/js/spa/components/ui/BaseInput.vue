<script setup lang="ts">
const modelValue = defineModel<string>({ required: true });

withDefaults(
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
</script>

<template>
  <label class="block space-y-1">
    <span v-if="label" class="text-sm font-medium text-slate-700">{{ label }}</span>
    <input
      v-model="modelValue"
      :type="type"
      :placeholder="placeholder"
      :required="required"
      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-slate-500"
    />
  </label>
</template>
