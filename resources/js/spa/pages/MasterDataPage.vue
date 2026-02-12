<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import AppContainer from '../components/layout/AppContainer.vue';
import AppTopbar from '../components/layout/AppTopbar.vue';
import BaseButton from '../components/ui/BaseButton.vue';
import BaseInput from '../components/ui/BaseInput.vue';
import BaseSelect from '../components/ui/BaseSelect.vue';
import {
  createCategoryMaster,
  createStatus,
  createTagMaster,
  deleteCategoryMaster,
  deleteStatusMaster,
  deleteTagMaster,
  getMasterCategories,
  getMasterTags,
  getStatuses,
  getStatusTransitions,
  updateCategoryMaster,
  updateStatusMaster,
  updateStatusTransitions,
  updateTagMaster,
} from '../services/masterData';
import { ApiError } from '../services/http';
import { useAuthStore } from '../stores/authStore';
import { useNotificationStore } from '../stores/notificationStore';
import type { Category, StatusItem, Tag } from '../types';

const { state } = useAuthStore();
const { push } = useNotificationStore();

const activeTab = ref<'statuses' | 'categories' | 'tags'>('statuses');

const statuses = ref<StatusItem[]>([]);
const categories = ref<Category[]>([]);
const tags = ref<Tag[]>([]);

const transitionMap = ref<Record<string, string[]>>({});

const statusForm = reactive({ id: '', code: '', name: '', description: '', is_active: '1', sort_order: '0' });
const categoryForm = reactive({ id: '', name: '', slug: '', description: '' });
const tagForm = reactive({ id: '', name: '', slug: '' });

const activeStatuses = computed(() => statuses.value.filter((status) => status.is_active));

async function loadAll() {
  if (!state.token) return;

  try {
    const [statusResponse, categoryResponse, tagResponse, transitionResponse] = await Promise.all([
      getStatuses(state.token),
      getMasterCategories(state.token),
      getMasterTags(state.token),
      getStatusTransitions(state.token),
    ]);

    statuses.value = statusResponse;
    categories.value = categoryResponse;
    tags.value = tagResponse;

    const nextMap: Record<string, string[]> = {};
    transitionResponse.forEach((row) => {
      nextMap[row.from_status_id] = row.to_status_ids;
    });
    transitionMap.value = nextMap;
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load master data';
    push('error', message);
  }
}

function resetStatusForm() {
  statusForm.id = '';
  statusForm.code = '';
  statusForm.name = '';
  statusForm.description = '';
  statusForm.is_active = '1';
  statusForm.sort_order = '0';
}

function resetCategoryForm() {
  categoryForm.id = '';
  categoryForm.name = '';
  categoryForm.slug = '';
  categoryForm.description = '';
}

function resetTagForm() {
  tagForm.id = '';
  tagForm.name = '';
  tagForm.slug = '';
}

async function saveStatus() {
  if (!state.token) return;

  try {
    const parsedSortOrder = Number.parseInt(statusForm.sort_order || '0', 10);
    const sortOrder = Number.isNaN(parsedSortOrder) ? 0 : parsedSortOrder;

    if (statusForm.id) {
      await updateStatusMaster(state.token, statusForm.id, {
        code: statusForm.code,
        name: statusForm.name,
        description: statusForm.description,
        is_active: statusForm.is_active === '1',
        sort_order: sortOrder,
      });
      push('success', 'Status updated');
    } else {
      await createStatus(state.token, {
        code: statusForm.code,
        name: statusForm.name,
        description: statusForm.description,
        is_active: statusForm.is_active === '1',
        sort_order: sortOrder,
      });
      push('success', 'Status created');
    }

    resetStatusForm();
    await loadAll();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to save status';
    push('error', message);
  }
}

async function removeStatus(id: string) {
  if (!state.token) return;

  if (!window.confirm('Delete this status?')) return;

  try {
    await deleteStatusMaster(state.token, id);
    push('success', 'Status deleted');
    await loadAll();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to delete status';
    push('error', message);
  }
}

async function toggleTransition(fromStatusId: string, toStatusId: string, checked: boolean) {
  if (!state.token) return;

  const existing = transitionMap.value[fromStatusId] ?? [];
  const next = checked ? [...new Set([...existing, toStatusId])] : existing.filter((id) => id !== toStatusId);

  try {
    await updateStatusTransitions(state.token, fromStatusId, next);
    transitionMap.value[fromStatusId] = next;
    push('success', 'Transition updated');
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to update transition';
    push('error', message);
  }
}

function onTransitionToggle(fromStatusId: string, toStatusId: string, event: Event) {
  const target = event.target as HTMLInputElement;
  void toggleTransition(fromStatusId, toStatusId, target.checked);
}

async function saveCategory() {
  if (!state.token) return;

  try {
    if (categoryForm.id) {
      await updateCategoryMaster(state.token, categoryForm.id, {
        name: categoryForm.name,
        slug: categoryForm.slug || undefined,
        description: categoryForm.description || undefined,
      });
      push('success', 'Category updated');
    } else {
      await createCategoryMaster(state.token, {
        name: categoryForm.name,
        slug: categoryForm.slug || undefined,
        description: categoryForm.description || undefined,
      });
      push('success', 'Category created');
    }

    resetCategoryForm();
    await loadAll();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to save category';
    push('error', message);
  }
}

async function removeCategory(id: string) {
  if (!state.token) return;

  if (!window.confirm('Delete this category?')) return;

  try {
    await deleteCategoryMaster(state.token, id);
    push('success', 'Category deleted');
    await loadAll();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to delete category';
    push('error', message);
  }
}

async function saveTag() {
  if (!state.token) return;

  try {
    if (tagForm.id) {
      await updateTagMaster(state.token, tagForm.id, {
        name: tagForm.name,
        slug: tagForm.slug || undefined,
      });
      push('success', 'Tag updated');
    } else {
      await createTagMaster(state.token, {
        name: tagForm.name,
        slug: tagForm.slug || undefined,
      });
      push('success', 'Tag created');
    }

    resetTagForm();
    await loadAll();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to save tag';
    push('error', message);
  }
}

async function removeTag(id: string) {
  if (!state.token) return;

  if (!window.confirm('Delete this tag?')) return;

  try {
    await deleteTagMaster(state.token, id);
    push('success', 'Tag deleted');
    await loadAll();
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to delete tag';
    push('error', message);
  }
}

onMounted(() => {
  void loadAll();
});
</script>

<template>
  <AppContainer>
    <AppTopbar />

    <main class="mx-auto max-w-6xl space-y-4 px-4 py-6">
      <h1 class="text-2xl font-semibold text-slate-900">Master Data</h1>

      <section class="flex gap-2">
        <BaseButton :variant="activeTab === 'statuses' ? 'primary' : 'secondary'" @click="activeTab = 'statuses'">Status</BaseButton>
        <BaseButton :variant="activeTab === 'categories' ? 'primary' : 'secondary'" @click="activeTab = 'categories'">Category</BaseButton>
        <BaseButton :variant="activeTab === 'tags' ? 'primary' : 'secondary'" @click="activeTab = 'tags'">Tag</BaseButton>
      </section>

      <section v-if="activeTab === 'statuses'" class="space-y-4">
        <div class="grid gap-4 lg:grid-cols-2">
          <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ statusForm.id ? 'Edit Status' : 'Create Status' }}</h2>
            <div class="mt-3 grid gap-3">
              <BaseInput v-model="statusForm.code" label="Code" placeholder="e.g. draft_review" />
              <BaseInput v-model="statusForm.name" label="Name" />
              <BaseInput v-model="statusForm.description" label="Description" />
              <BaseInput v-model="statusForm.sort_order" label="Sort Order" type="number" />
              <BaseSelect
                v-model="statusForm.is_active"
                label="Active"
                :options="[
                  { label: 'Active', value: '1' },
                  { label: 'Inactive', value: '0' },
                ]"
              />
              <div class="flex gap-2">
                <BaseButton @click="saveStatus">Save</BaseButton>
                <BaseButton variant="secondary" @click="resetStatusForm">Reset</BaseButton>
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Status List</h2>
            <div class="mt-3 space-y-2">
              <div v-for="item in statuses" :key="item.id" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
                <div>
                  <p class="font-medium text-slate-900">{{ item.name }} ({{ item.code }})</p>
                  <p class="text-xs text-slate-600">{{ item.description || '-' }} | active: {{ item.is_active ? 'yes' : 'no' }}</p>
                </div>
                <div class="flex gap-3 text-sm">
                  <button class="text-indigo-700 hover:underline" @click="Object.assign(statusForm, { ...item, is_active: item.is_active ? '1' : '0', sort_order: String(item.sort_order ?? 0) })">Edit</button>
                  <button class="text-red-700 hover:underline" @click="removeStatus(item.id)">Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-900">Lifecycle Transition Matrix</h2>
          <p class="mt-1 text-sm text-slate-600">Set which target statuses are allowed from each source status.</p>

          <div class="mt-3 space-y-3">
            <div v-for="fromStatus in activeStatuses" :key="fromStatus.id" class="rounded-lg border border-slate-200 p-3">
              <p class="font-medium text-slate-900">From: {{ fromStatus.name }} ({{ fromStatus.code }})</p>

              <div class="mt-2 flex flex-wrap gap-3">
                <label
                  v-for="toStatus in activeStatuses.filter((status) => status.id !== fromStatus.id)"
                  :key="toStatus.id"
                  class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-2 py-1 text-sm"
                >
                  <input
                    type="checkbox"
                    :checked="(transitionMap[fromStatus.id] ?? []).includes(toStatus.id)"
                    @change="onTransitionToggle(fromStatus.id, toStatus.id, $event)"
                  />
                  {{ toStatus.name }}
                </label>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section v-if="activeTab === 'categories'" class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-900">{{ categoryForm.id ? 'Edit Category' : 'Create Category' }}</h2>
          <div class="mt-3 grid gap-3">
            <BaseInput v-model="categoryForm.name" label="Name" />
            <BaseInput v-model="categoryForm.slug" label="Slug" />
            <BaseInput v-model="categoryForm.description" label="Description" />
            <div class="flex gap-2">
              <BaseButton @click="saveCategory">Save</BaseButton>
              <BaseButton variant="secondary" @click="resetCategoryForm">Reset</BaseButton>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-900">Category List</h2>
          <div class="mt-3 space-y-2">
            <div v-for="item in categories" :key="item.id" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
              <div>
                <p class="font-medium text-slate-900">{{ item.name }}</p>
                <p class="text-xs text-slate-600">{{ item.slug }}</p>
              </div>
              <div class="flex gap-3 text-sm">
                <button class="text-indigo-700 hover:underline" @click="Object.assign(categoryForm, item)">Edit</button>
                <button class="text-red-700 hover:underline" @click="removeCategory(item.id)">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section v-if="activeTab === 'tags'" class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-900">{{ tagForm.id ? 'Edit Tag' : 'Create Tag' }}</h2>
          <div class="mt-3 grid gap-3">
            <BaseInput v-model="tagForm.name" label="Name" />
            <BaseInput v-model="tagForm.slug" label="Slug" />
            <div class="flex gap-2">
              <BaseButton @click="saveTag">Save</BaseButton>
              <BaseButton variant="secondary" @click="resetTagForm">Reset</BaseButton>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-900">Tag List</h2>
          <div class="mt-3 space-y-2">
            <div v-for="item in tags" :key="item.id" class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
              <div>
                <p class="font-medium text-slate-900">{{ item.name }}</p>
                <p class="text-xs text-slate-600">{{ item.slug }}</p>
              </div>
              <div class="flex gap-3 text-sm">
                <button class="text-indigo-700 hover:underline" @click="Object.assign(tagForm, item)">Edit</button>
                <button class="text-red-700 hover:underline" @click="removeTag(item.id)">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </AppContainer>
</template>
