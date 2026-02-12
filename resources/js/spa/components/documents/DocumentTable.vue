<script setup lang="ts">
import type { DocumentItem } from '../../types';

defineProps<{
  documents: DocumentItem[];
}>();

const emit = defineEmits<{
  open: [id: string];
  edit: [id: string];
  remove: [id: string];
}>();

function statusBadge(status: string) {
  if (status === 'active') return 'bg-emerald-100 text-emerald-800';
  if (status === 'archived') return 'bg-amber-100 text-amber-800';
  return 'bg-slate-100 dark:bg-slate-800/60 text-slate-800 dark:text-slate-200';
}
</script>

<template>
  <section class="overflow-x-auto rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-100 dark:bg-slate-800/60 text-left text-slate-600 dark:text-slate-400">
        <tr>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Category</th>
          <th class="px-4 py-3">Current Version</th>
          <th class="px-4 py-3">Updated At</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="documents.length === 0">
          <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No document found.</td>
        </tr>

        <tr v-for="doc in documents" :key="doc.id" class="border-t border-slate-100 dark:border-slate-800">
          <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ doc.title }}</td>
          <td class="px-4 py-3">
            <span class="rounded-full px-2 py-1 text-xs font-medium" :class="statusBadge(doc.status)">
              {{ doc.status }}
            </span>
          </td>
          <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ doc.category?.name ?? '-' }}</td>
          <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ doc.current_version?.version_number ?? '-' }}</td>
          <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
            {{ new Date(doc.updated_at).toLocaleString() }}
          </td>
          <td class="px-4 py-3">
            <div class="flex justify-end gap-3 text-xs font-medium">
              <button class="text-sky-700 hover:underline" @click="emit('open', doc.id)">Detail</button>
              <button class="text-indigo-700 hover:underline" @click="emit('edit', doc.id)">Edit</button>
              <button class="text-red-700 hover:underline" @click="emit('remove', doc.id)">Delete</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>
