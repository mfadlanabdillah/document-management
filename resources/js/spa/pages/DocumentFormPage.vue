<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppContainer from '../components/layout/AppContainer.vue';
import AppTopbar from '../components/layout/AppTopbar.vue';
import BaseButton from '../components/ui/BaseButton.vue';
import DocumentForm from '../components/documents/DocumentForm.vue';
import { createDocument, getDocumentById, updateDocument } from '../services/documents';
import { getCategories, getTags } from '../services/meta';
import { extractDocument, normalizeCategories, normalizeTags } from '../services/normalize';
import { ApiError } from '../services/http';
import { useAuthStore } from '../stores/authStore';
import { useNotificationStore } from '../stores/notificationStore';

const route = useRoute();
const router = useRouter();
const { state } = useAuthStore();
const { push } = useNotificationStore();

const loading = ref(false);
const submitting = ref(false);
const categories = ref<Array<{ id: string; name: string }>>([]);
const tags = ref<Array<{ id: string; name: string }>>([]);

const isEditMode = computed(() => Boolean(route.params.id));
const documentId = computed(() => String(route.params.id ?? ''));

const form = reactive({
  title: '',
  category_id: '',
  tag_ids: [] as string[],
  notes: '',
  file: null as File | null,
});

async function loadMetadata() {
  if (!state.token) return;

  const [categoriesResponse, tagsResponse] = await Promise.all([
    getCategories(state.token),
    getTags(state.token),
  ]);

  categories.value = normalizeCategories(categoriesResponse);
  tags.value = normalizeTags(tagsResponse);
}

async function loadDocumentIfEdit() {
  if (!state.token || !isEditMode.value) {
    return;
  }

  const response = await getDocumentById(state.token, documentId.value);
  const document = extractDocument(response);

  form.title = document.title;
  form.category_id = document.category?.id ?? '';
  form.tag_ids = (document.tags ?? []).map((tag) => tag.id);
  form.notes = '';
}

async function submit() {
  if (!state.token) return;

  submitting.value = true;
  try {
    if (isEditMode.value) {
      await updateDocument(state.token, documentId.value, {
        title: form.title,
        category_id: form.category_id,
        tag_ids: form.tag_ids,
        notes: form.notes,
      });
      push('success', 'Document updated');
      await router.push({ name: 'document-detail', params: { id: documentId.value } });
    } else {
      const created = await createDocument(state.token, {
        title: form.title,
        category_id: form.category_id,
        tag_ids: form.tag_ids,
        notes: form.notes,
        file: form.file,
      });

      const document = extractDocument(created);
      push('success', 'Document created');
      await router.push({ name: 'document-detail', params: { id: document.id } });
    }
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Save failed';
    push('error', message);
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  loading.value = true;
  try {
    await loadMetadata();
    await loadDocumentIfEdit();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load form data';
    push('error', message);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <AppContainer>
    <AppTopbar />

    <main class="mx-auto max-w-3xl space-y-4 px-4 py-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
          {{ isEditMode ? 'Edit Document' : 'Create Document' }}
        </h1>
        <BaseButton variant="secondary" @click="router.push({ name: 'dashboard' })">Back</BaseButton>
      </div>

      <section v-if="loading" class="rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-6 shadow-sm">Loading...</section>

      <section v-else class="rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-6 shadow-sm">
        <DocumentForm
          v-model="form"
          :categories="categories"
          :tags="tags"
          :show-file-input="!isEditMode"
          :loading="submitting"
          :submit-label="isEditMode ? 'Update Document' : 'Create Document'"
          @submit="submit"
        />
      </section>
    </main>
  </AppContainer>
</template>
