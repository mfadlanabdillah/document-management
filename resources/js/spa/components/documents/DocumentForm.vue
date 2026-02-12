<script setup lang="ts">
import { computed, ref } from 'vue';
import BaseButton from '../ui/BaseButton.vue';
import BaseInput from '../ui/BaseInput.vue';
import BaseSelect from '../ui/BaseSelect.vue';

interface FormValue {
  title: string;
  category_id: string;
  tag_ids: string[];
  notes: string;
  file?: File | null;
}

const props = withDefaults(
  defineProps<{
    modelValue: FormValue;
    categories: Array<{ id: string; name: string }>;
    tags: Array<{ id: string; name: string }>;
    submitLabel?: string;
    showFileInput?: boolean;
    loading?: boolean;
  }>(),
  {
    submitLabel: 'Save',
    showFileInput: true,
    loading: false,
  },
);

const emit = defineEmits<{
  'update:modelValue': [value: FormValue];
  submit: [];
}>();

const local = computed<FormValue>({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const selectedTag = ref('');

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] ?? null;
  local.value.file = file;
}

function addTag() {
  if (!selectedTag.value || local.value.tag_ids.includes(selectedTag.value)) {
    return;
  }

  local.value.tag_ids.push(selectedTag.value);

  selectedTag.value = '';
}

function removeTag(id: string) {
  local.value.tag_ids = local.value.tag_ids.filter((tagId) => tagId !== id);
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="emit('submit')">
    <BaseInput v-model="local.title" label="Title" required />

    <BaseSelect
      v-model="local.category_id"
      label="Category"
      :options="[
        { label: 'Select category', value: '' },
        ...categories.map((category) => ({ label: category.name, value: category.id })),
      ]"
    />

    <BaseInput v-model="local.notes" label="Notes" placeholder="Optional notes" />

    <div class="space-y-2">
      <BaseSelect
        v-model="selectedTag"
        label="Tags"
        :options="[
          { label: 'Select tag', value: '' },
          ...tags.map((tag) => ({ label: tag.name, value: tag.id })),
        ]"
      />

      <div class="flex gap-2">
        <BaseButton type="button" variant="secondary" @click="addTag">Add Tag</BaseButton>
      </div>

      <div class="flex flex-wrap gap-2">
        <span
          v-for="tagId in local.tag_ids"
          :key="tagId"
          class="inline-flex items-center gap-2 rounded-full bg-slate-200 px-3 py-1 text-xs"
        >
          {{ tags.find((tag) => tag.id === tagId)?.name ?? tagId }}
          <button type="button" class="text-slate-700" @click="removeTag(tagId)">x</button>
        </span>
      </div>
    </div>

    <label v-if="showFileInput" class="block space-y-1">
      <span class="text-sm font-medium text-slate-700">File</span>
      <input
        type="file"
        accept=".pdf,.docx,.xlsx"
        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
        @change="onFileChange"
      />
    </label>

    <div class="pt-2">
      <BaseButton type="submit" :disabled="loading">{{ loading ? 'Processing...' : submitLabel }}</BaseButton>
    </div>
  </form>
</template>
