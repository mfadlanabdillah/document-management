<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppContainer from '../components/layout/AppContainer.vue';
import AppTopbar from '../components/layout/AppTopbar.vue';
import BaseButton from '../components/ui/BaseButton.vue';
import BaseSelect from '../components/ui/BaseSelect.vue';
import {
  downloadDocumentVersion,
  getDocumentActivities,
  getDocumentById,
  getDocumentVersions,
  updateDocumentStatus,
  uploadDocumentVersion,
} from '../services/documents';
import { getStatuses } from '../services/masterData';
import { extractDocument, extractList } from '../services/normalize';
import { ApiError } from '../services/http';
import { useAuthStore } from '../stores/authStore';
import { useNotificationStore } from '../stores/notificationStore';
import type { DocumentActivity, DocumentDetail, DocumentStatus, DocumentVersion, StatusItem } from '../types';

const route = useRoute();
const router = useRouter();
const { state } = useAuthStore();
const { push } = useNotificationStore();

const loading = ref(false);
const submittingStatus = ref(false);
const submittingVersion = ref(false);
const document = ref<DocumentDetail | null>(null);
const versions = ref<DocumentVersion[]>([]);
const activities = ref<DocumentActivity[]>([]);
const statuses = ref<StatusItem[]>([]);
const selectedStatus = ref<DocumentStatus>('draft');

const versionForm = reactive({
  file: null as File | null,
  notes: '',
});

const documentId = computed(() => String(route.params.id));
const statusOptions = computed(() => {
  if (!document.value) {
    return statuses.value.map((status) => ({ label: status.name, value: status.code }));
  }

  const current = document.value.status;
  const currentStatus = statuses.value.find((status) => status.code === current);
  const allowedNextCodes = currentStatus?.allowed_next_codes ?? [];
  const allowedCodes = new Set([current, ...allowedNextCodes]);

  const options = statuses.value
    .filter((status) => allowedCodes.has(status.code))
    .map((status) => ({ label: status.name, value: status.code }));

  if (!options.find((option) => option.value === current)) {
    options.unshift({ label: current, value: current });
  }

  return options;
});

async function loadDocument() {
  if (!state.token) return;

  loading.value = true;
  try {
    const response = await getDocumentById(state.token, documentId.value);
    document.value = extractDocument(response);
    selectedStatus.value = document.value.status;
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load document';
    push('error', message);
  } finally {
    loading.value = false;
  }
}

async function loadVersionsAndActivities() {
  if (!state.token) return;

  try {
    const [versionsResponse, activitiesResponse] = await Promise.all([
      getDocumentVersions(state.token, documentId.value),
      getDocumentActivities(state.token, documentId.value),
    ]);

    versions.value = extractList<DocumentVersion>(versionsResponse);
    activities.value = extractList<DocumentActivity>(activitiesResponse);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load history';
    push('error', message);
  }
}

async function loadStatuses() {
  if (!state.token) return;

  try {
    const response = await getStatuses(state.token);
    statuses.value = response.filter((status) => status.is_active);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load status options';
    push('error', message);
  }
}

async function changeStatus() {
  if (!state.token) return;

  submittingStatus.value = true;
  try {
    await updateDocumentStatus(state.token, documentId.value, selectedStatus.value);
    push('success', 'Status updated');
    await Promise.all([loadDocument(), loadVersionsAndActivities()]);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to update status';
    push('error', message);
  } finally {
    submittingStatus.value = false;
  }
}

function onVersionFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  versionForm.file = target.files?.[0] ?? null;
}

async function uploadVersion() {
  if (!state.token || !versionForm.file) {
    push('info', 'Please choose a file first');
    return;
  }

  submittingVersion.value = true;
  try {
    await uploadDocumentVersion(state.token, documentId.value, {
      file: versionForm.file,
      notes: versionForm.notes,
    });

    push('success', 'New version uploaded');
    versionForm.file = null;
    versionForm.notes = '';
    await Promise.all([loadDocument(), loadVersionsAndActivities()]);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to upload version';
    push('error', message);
  } finally {
    submittingVersion.value = false;
  }
}

async function downloadVersion(version: DocumentVersion) {
  if (!state.token) return;

  try {
    await downloadDocumentVersion(state.token, documentId.value, version.id, version.file_name);
  } catch (error) {
    const message = error instanceof Error ? error.message : 'Failed to download version';
    push('error', message);
  }
}

onMounted(async () => {
  await Promise.all([loadDocument(), loadVersionsAndActivities(), loadStatuses()]);
});
</script>

<template>
  <AppContainer>
    <AppTopbar />

    <main class="mx-auto max-w-5xl space-y-4 px-4 py-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Document Detail</h1>
        <div class="flex gap-2">
          <BaseButton variant="secondary" @click="router.push({ name: 'dashboard' })">Back</BaseButton>
          <BaseButton @click="router.push({ name: 'document-edit', params: { id: documentId } })">Edit</BaseButton>
        </div>
      </div>

      <section v-if="loading" class="rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-6 shadow-sm">Loading...</section>

      <section v-else-if="document" class="space-y-4 rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-6 shadow-sm">
        <div class="grid gap-3 md:grid-cols-2">
          <div>
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Title</p>
            <p class="text-slate-900 dark:text-slate-100">{{ document.title }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Category</p>
            <p class="text-slate-900 dark:text-slate-100">{{ document.category?.name ?? '-' }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Created At</p>
            <p class="text-slate-900 dark:text-slate-100">{{ new Date(document.created_at).toLocaleString() }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Updated At</p>
            <p class="text-slate-900 dark:text-slate-100">{{ new Date(document.updated_at).toLocaleString() }}</p>
          </div>
        </div>

        <div>
          <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Current Version</p>
          <p class="text-slate-900 dark:text-slate-100">
            {{
              document.current_version
                ? `v${document.current_version.version_number} - ${document.current_version.file_name}`
                : '-'
            }}
          </p>
        </div>

        <div>
          <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Tags</p>
          <div class="mt-1 flex flex-wrap gap-2">
            <span
              v-for="tag in document.tags ?? []"
              :key="tag.id"
              class="rounded-full bg-slate-200 px-3 py-1 text-xs text-slate-800 dark:bg-slate-700 dark:text-slate-200"
            >
              {{ tag.name }}
            </span>
            <p v-if="!(document.tags && document.tags.length)" class="text-slate-900 dark:text-slate-100">-</p>
          </div>
        </div>

        <div class="space-y-2 border-t border-slate-200 dark:border-slate-700 pt-4">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Update Status</p>
          <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
            <div class="w-full sm:max-w-xs">
              <BaseSelect
                v-model="selectedStatus"
                :options="statusOptions"
              />
            </div>
            <BaseButton :disabled="submittingStatus" @click="changeStatus">
              {{ submittingStatus ? 'Saving...' : 'Save Status' }}
            </BaseButton>
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Versions</h2>

        <div class="mt-3 grid gap-3 md:grid-cols-[1fr,auto]">
          <div class="space-y-2">
            <input
              type="file"
              accept=".pdf,.docx,.xlsx"
              class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/20"
              @change="onVersionFileChange"
            />
            <input
              v-model="versionForm.notes"
              type="text"
              placeholder="Notes (optional)"
              class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/20"
            />
          </div>
          <div class="flex items-end">
            <BaseButton :disabled="submittingVersion" @click="uploadVersion">
              {{ submittingVersion ? 'Uploading...' : 'Upload Version' }}
            </BaseButton>
          </div>
        </div>

        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-100 dark:bg-slate-800/60 text-left text-slate-600 dark:text-slate-400">
              <tr>
                <th class="px-3 py-2">Version</th>
                <th class="px-3 py-2">File</th>
                <th class="px-3 py-2">Notes</th>
                <th class="px-3 py-2">Uploaded At</th>
                <th class="px-3 py-2 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="versions.length === 0">
                <td colspan="5" class="px-3 py-4 text-center text-slate-500 dark:text-slate-400">No versions yet.</td>
              </tr>
              <tr v-for="version in versions" :key="version.id" class="border-t border-slate-100 dark:border-slate-800">
                <td class="px-3 py-2">v{{ version.version_number }}</td>
                <td class="px-3 py-2">{{ version.file_name }}</td>
                <td class="px-3 py-2">{{ version.notes || '-' }}</td>
                <td class="px-3 py-2">{{ new Date(version.uploaded_at).toLocaleString() }}</td>
                <td class="px-3 py-2 text-right">
                  <button class="text-sky-700 hover:text-sky-900 dark:text-sky-400 dark:hover:text-sky-300" @click="downloadVersion(version)">Download</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Activity Logs</h2>

        <div class="mt-3 space-y-2">
          <div v-if="activities.length === 0" class="text-sm text-slate-500 dark:text-slate-400">No activity found.</div>
          <div
            v-for="activity in activities"
            :key="activity.id"
            class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 px-3 py-2 text-sm dark:bg-slate-800/50"
          >
            <p class="font-medium text-slate-800 dark:text-slate-200">{{ activity.action }}</p>
            <p class="text-slate-600 dark:text-slate-400">
              {{ activity.performed_by?.name || 'Unknown user' }} -
              {{ new Date(activity.created_at).toLocaleString() }}
            </p>
          </div>
        </div>
      </section>
    </main>
  </AppContainer>
</template>
