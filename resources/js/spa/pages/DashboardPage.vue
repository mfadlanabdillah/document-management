<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppContainer from '../components/layout/AppContainer.vue';
import AppTopbar from '../components/layout/AppTopbar.vue';
import DocumentFilters from '../components/documents/DocumentFilters.vue';
import DocumentTable from '../components/documents/DocumentTable.vue';
import PaginationBar from '../components/documents/PaginationBar.vue';
import BaseButton from '../components/ui/BaseButton.vue';
import TrashTable from '../components/documents/TrashTable.vue';
import { getDocuments, getTrashedDocuments, deleteDocument, restoreDocument } from '../services/documents';
import { getCategories } from '../services/meta';
import { getStatuses } from '../services/masterData';
import { normalizeCategories, normalizeDocuments } from '../services/normalize';
import { ApiError } from '../services/http';
import { useAuthStore } from '../stores/authStore';
import { useNotificationStore } from '../stores/notificationStore';
import type { DocumentFilters as Filters, DocumentItem, StatusItem } from '../types';

const router = useRouter();
const { state } = useAuthStore();
const { push } = useNotificationStore();

const loading = ref(false);
const documents = ref<DocumentItem[]>([]);
const categories = ref<Array<{ id: string; name: string }>>([]);
const statuses = ref<StatusItem[]>([]);
const recentlyDeleted = ref<{ id: string; title: string } | null>(null);
const activeTab = ref<'documents' | 'trash'>('documents');

const pagination = reactive({
  current_page: 1,
  last_page: 1,
});

const filters = reactive<Filters>({
  status: '',
  category_id: '',
  search: '',
  sort: 'updated_at',
  direction: 'desc',
  page: 1,
});

async function loadCategories() {
  if (!state.token) return;

  try {
    const response = await getCategories(state.token);
    categories.value = normalizeCategories(response);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load categories';
    push('error', message);
  }
}

async function loadStatuses() {
  if (!state.token) return;

  try {
    const response = await getStatuses(state.token);
    statuses.value = response.filter((status) => status.is_active);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load statuses';
    push('error', message);
  }
}

async function loadDocuments() {
  if (!state.token) return;

  loading.value = true;
  try {
    const response =
      activeTab.value === 'documents'
        ? await getDocuments(state.token, filters)
        : await getTrashedDocuments(state.token, {
            ...filters,
            status: '',
            sort: filters.sort || 'deleted_at',
          });

    const normalized = normalizeDocuments(response);
    documents.value = normalized.data;
    pagination.current_page = normalized.meta?.current_page ?? 1;
    pagination.last_page = normalized.meta?.last_page ?? 1;
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load documents';
    push('error', message);
  } finally {
    loading.value = false;
  }
}

function applyFilters(payload: { status: string; category_id: string; search: string }) {
  filters.status = payload.status;
  filters.category_id = payload.category_id;
  filters.search = payload.search;
  filters.page = 1;
  void loadDocuments();
}

function resetFilters() {
  filters.status = '';
  filters.category_id = '';
  filters.search = '';
  filters.page = 1;
  void loadDocuments();
}

function setTab(tab: 'documents' | 'trash') {
  activeTab.value = tab;
  filters.page = 1;
  if (tab === 'trash') {
    filters.sort = 'deleted_at';
    filters.direction = 'desc';
  }
  void loadDocuments();
}

function openDetail(id: string) {
  router.push({ name: 'document-detail', params: { id } });
}

function editDocument(id: string) {
  router.push({ name: 'document-edit', params: { id } });
}

async function removeDocument(id: string) {
  if (!state.token) return;

  const confirmed = window.confirm('Delete this document?');
  if (!confirmed) return;

  try {
    const deletedDocument = documents.value.find((item) => item.id === id);
    await deleteDocument(state.token, id);
    push('success', 'Document deleted');
    recentlyDeleted.value = {
      id,
      title: deletedDocument?.title ?? 'Document',
    };
    await loadDocuments();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Delete failed';
    push('error', message);
  }
}

async function restoreById(id: string) {
  if (!state.token) return;

  try {
    await restoreDocument(state.token, id);
    push('success', 'Document restored');

    if (recentlyDeleted.value?.id === id) {
      recentlyDeleted.value = null;
    }

    await loadDocuments();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Restore failed';
    push('error', message);
  }
}

async function restoreLastDeleted() {
  if (!recentlyDeleted.value) return;
  await restoreById(recentlyDeleted.value.id);
}

function onPageChange(page: number) {
  filters.page = page;
  void loadDocuments();
}

onMounted(async () => {
  await loadCategories();
  await loadStatuses();
  await loadDocuments();
});
</script>

<template>
  <AppContainer>
    <AppTopbar />

    <main class="mx-auto max-w-6xl space-y-4 px-4 py-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
        <BaseButton v-if="activeTab === 'documents'" @click="router.push({ name: 'document-create' })">
          Create Document
        </BaseButton>
      </div>

      <section class="flex gap-2">
        <BaseButton
          :variant="activeTab === 'documents' ? 'primary' : 'secondary'"
          @click="setTab('documents')"
        >
          Documents
        </BaseButton>
        <BaseButton :variant="activeTab === 'trash' ? 'primary' : 'secondary'" @click="setTab('trash')">
          Trash
        </BaseButton>
      </section>

      <DocumentFilters
        :status="activeTab === 'documents' ? filters.status : ''"
        :category-id="filters.category_id"
        :search="filters.search"
        :categories="categories"
        :statuses="statuses"
        @apply="applyFilters"
        @reset="resetFilters"
      />

      <section
        v-if="recentlyDeleted"
        class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm"
      >
        <p class="text-emerald-800">
          {{ recentlyDeleted.title }} deleted.
        </p>
        <BaseButton variant="secondary" @click="restoreLastDeleted">Restore</BaseButton>
      </section>

      <div v-if="loading" class="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-600 shadow-sm">
        Loading documents...
      </div>

      <DocumentTable
        v-else-if="activeTab === 'documents'"
        :documents="documents"
        @open="openDetail"
        @edit="editDocument"
        @remove="removeDocument"
      />

      <TrashTable v-else :documents="documents" @restore="restoreById" />

      <PaginationBar
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        @change="onPageChange"
      />
    </main>
  </AppContainer>
</template>
