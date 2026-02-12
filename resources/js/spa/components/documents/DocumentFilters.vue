<script setup lang="ts">
import { ref, watch } from 'vue';
import BaseButton from '../ui/BaseButton.vue';
import BaseInput from '../ui/BaseInput.vue';
import BaseSelect from '../ui/BaseSelect.vue';

const props = defineProps<{
  status: string;
  categoryId: string;
  search: string;
  categories: Array<{ id: string; name: string }>;
  statuses?: Array<{ code: string; name: string }>;
}>();

const emit = defineEmits<{
  apply: [payload: { status: string; category_id: string; search: string }];
  reset: [];
}>();

const statusLocal = ref(props.status);
const categoryLocal = ref(props.categoryId);
const searchLocal = ref(props.search);

watch(
  () => [props.status, props.categoryId, props.search],
  ([status, category, search]) => {
    statusLocal.value = status;
    categoryLocal.value = category;
    searchLocal.value = search;
  },
);

function apply() {
  emit('apply', {
    status: statusLocal.value,
    category_id: categoryLocal.value,
    search: searchLocal.value,
  });
}
</script>

<template>
  <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
      <BaseSelect
        v-model="statusLocal"
        label="Status"
        :options="[
          { label: 'All Status', value: '' },
          ...(statuses && statuses.length
            ? statuses.map((status) => ({ label: status.name, value: status.code }))
            : [
                { label: 'Draft', value: 'draft' },
                { label: 'Active', value: 'active' },
                { label: 'Archived', value: 'archived' },
              ]),
        ]"
      />

      <BaseSelect
        v-model="categoryLocal"
        label="Category"
        :options="[
          { label: 'All Category', value: '' },
          ...categories.map((category) => ({ label: category.name, value: category.id })),
        ]"
      />

      <BaseInput v-model="searchLocal" label="Search" placeholder="Search title..." />

      <div class="flex items-end gap-2">
        <BaseButton class="flex-1" @click="apply">Apply</BaseButton>
        <BaseButton class="flex-1" variant="ghost" @click="$emit('reset')">Reset</BaseButton>
      </div>
    </div>
  </section>
</template>
