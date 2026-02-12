<script setup lang="ts">
import type { DocumentItem } from '../../types';

defineProps<{
  documents: DocumentItem[];
}>();

const emit = defineEmits<{
  restore: [id: string];
}>();
</script>

<template>
  <section class="overflow-x-auto rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-100 dark:bg-slate-800/60 text-left text-slate-600 dark:text-slate-400">
        <tr>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Category</th>
          <th class="px-4 py-3">Deleted At</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="documents.length === 0">
          <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">Trash is empty.</td>
        </tr>

        <tr v-for="doc in documents" :key="doc.id" class="border-t border-slate-100 dark:border-slate-800">
          <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ doc.title }}</td>
          <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ doc.category?.name ?? '-' }}</td>
          <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
            {{ doc.deleted_at ? new Date(doc.deleted_at).toLocaleString() : '-' }}
          </td>
          <td class="px-4 py-3 text-right">
            <button class="text-emerald-700 hover:underline" @click="emit('restore', doc.id)">Restore</button>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>
