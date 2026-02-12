<script setup lang="ts">
import { useNotificationStore } from '../../stores/notificationStore';

const { state, remove } = useNotificationStore();

function styleFor(type: 'success' | 'error' | 'info') {
  if (type === 'success') {
    return 'border-emerald-200 bg-emerald-50/90 text-emerald-900';
  }

  if (type === 'error') {
    return 'border-rose-200 bg-rose-50/90 text-rose-900';
  }

  return 'border-sky-200 bg-sky-50/90 text-sky-900';
}
</script>

<template>
  <div class="pointer-events-none fixed right-4 top-4 z-40 flex w-full max-w-sm flex-col gap-2">
    <div
      v-for="item in state.items"
      :key="item.id"
      class="pointer-events-auto rounded-2xl border px-4 py-3 text-sm shadow-[0_18px_40px_-20px_rgba(15,23,42,0.45)] backdrop-blur"
      :class="styleFor(item.type)"
      @click="remove(item.id)"
    >
      {{ item.message }}
    </div>
  </div>
</template>
