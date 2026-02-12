<script setup lang="ts">
import { useNotificationStore } from '../../stores/notificationStore';

const { state, remove } = useNotificationStore();

function styleFor(type: 'success' | 'error' | 'info') {
  if (type === 'success') {
    return 'border-emerald-300 bg-emerald-50 text-emerald-800';
  }

  if (type === 'error') {
    return 'border-red-300 bg-red-50 text-red-800';
  }

  return 'border-sky-300 bg-sky-50 text-sky-800';
}
</script>

<template>
  <div class="pointer-events-none fixed right-4 top-4 z-40 flex w-full max-w-sm flex-col gap-2">
    <div
      v-for="item in state.items"
      :key="item.id"
      class="pointer-events-auto rounded-lg border px-4 py-3 text-sm shadow"
      :class="styleFor(item.type)"
      @click="remove(item.id)"
    >
      {{ item.message }}
    </div>
  </div>
</template>
