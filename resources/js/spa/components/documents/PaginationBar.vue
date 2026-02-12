<script setup lang="ts">
const props = defineProps<{
  currentPage: number;
  lastPage: number;
}>();

const emit = defineEmits<{
  change: [page: number];
}>();

function go(page: number) {
  if (page < 1 || page > props.lastPage || page === props.currentPage) {
    return;
  }

  emit('change', page);
}
</script>

<template>
  <div class="flex items-center justify-between rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 px-4 py-3 shadow-sm">
    <p class="text-sm text-slate-600 dark:text-slate-400">Page {{ currentPage }} of {{ lastPage }}</p>
    <div class="flex gap-2">
      <button
        class="rounded-md border border-slate-300 px-3 py-1 text-sm disabled:opacity-50"
        :disabled="currentPage <= 1"
        @click="go(currentPage - 1)"
      >
        Prev
      </button>
      <button
        class="rounded-md border border-slate-300 px-3 py-1 text-sm disabled:opacity-50"
        :disabled="currentPage >= lastPage"
        @click="go(currentPage + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
