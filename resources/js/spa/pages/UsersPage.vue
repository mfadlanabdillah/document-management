<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import AppContainer from '../components/layout/AppContainer.vue';
import AppTopbar from '../components/layout/AppTopbar.vue';
import BaseButton from '../components/ui/BaseButton.vue';
import BaseInput from '../components/ui/BaseInput.vue';
import BaseSelect from '../components/ui/BaseSelect.vue';
import PaginationBar from '../components/documents/PaginationBar.vue';
import { createUser, deleteUser, getUserAuditLogs, getUsers, updateUser, updateUserRole } from '../services/users';
import { ApiError } from '../services/http';
import { useAuthStore } from '../stores/authStore';
import { useNotificationStore } from '../stores/notificationStore';
import type { UserAuditLogItem, UserItem } from '../types/users';

const { state } = useAuthStore();
const { push } = useNotificationStore();

const loading = ref(false);
const users = ref<UserItem[]>([]);
const logs = ref<UserAuditLogItem[]>([]);
const editingId = ref<string | null>(null);

const filters = reactive({
  search: '',
  role: '',
  page: 1,
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
});

const logPagination = reactive({
  current_page: 1,
  last_page: 1,
});

const form = reactive({
  name: '',
  email: '',
  role: 'user' as 'admin' | 'user',
  password: '',
  password_confirmation: '',
});

async function loadUsers() {
  if (!state.token) return;

  loading.value = true;
  try {
    const response = await getUsers(state.token, filters);
    users.value = response.data;
    pagination.current_page = response.meta?.current_page ?? 1;
    pagination.last_page = response.meta?.last_page ?? 1;
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load users';
    push('error', message);
  } finally {
    loading.value = false;
  }
}

async function loadAuditLogs(page = 1) {
  if (!state.token) return;

  try {
    const response = await getUserAuditLogs(state.token, page);
    logs.value = response.data;
    logPagination.current_page = response.meta?.current_page ?? 1;
    logPagination.last_page = response.meta?.last_page ?? 1;
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to load audit logs';
    push('error', message);
  }
}

function startCreate() {
  editingId.value = null;
  form.name = '';
  form.email = '';
  form.role = 'user';
  form.password = '';
  form.password_confirmation = '';
}

function startEdit(user: UserItem) {
  editingId.value = user.id;
  form.name = user.name;
  form.email = user.email;
  form.role = user.role;
  form.password = '';
  form.password_confirmation = '';
}

async function submitForm() {
  if (!state.token) return;

  try {
    if (editingId.value) {
      const target = users.value.find((user) => user.id === editingId.value);
      const oldRole = target?.role;

      await updateUser(state.token, editingId.value, {
        name: form.name,
        email: form.email,
        password: form.password || undefined,
        password_confirmation: form.password_confirmation || undefined,
      });

      if (oldRole && oldRole !== form.role) {
        await updateUserRole(state.token, editingId.value, form.role);
      }

      push('success', 'User updated');
    } else {
      await createUser(state.token, {
        name: form.name,
        email: form.email,
        role: form.role,
        password: form.password,
        password_confirmation: form.password_confirmation,
      });
      push('success', 'User created');
    }

    startCreate();
    await Promise.all([loadUsers(), loadAuditLogs(logPagination.current_page)]);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to save user';
    push('error', message);
  }
}

async function quickChangeRole(user: UserItem, role: 'admin' | 'user') {
  if (!state.token || user.role === role) return;

  try {
    await updateUserRole(state.token, user.id, role);
    push('success', 'Role updated');
    await Promise.all([loadUsers(), loadAuditLogs(logPagination.current_page)]);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to update role';
    push('error', message);
  }
}

function onRoleSelectChange(user: UserItem, event: Event) {
  const target = event.target as HTMLSelectElement;
  const role = target.value === 'admin' ? 'admin' : 'user';
  void quickChangeRole(user, role);
}

async function removeUser(user: UserItem) {
  if (!state.token) return;

  const confirmed = window.confirm(`Delete user ${user.email}?`);
  if (!confirmed) return;

  try {
    await deleteUser(state.token, user.id);
    push('success', 'User deleted');
    await Promise.all([loadUsers(), loadAuditLogs(logPagination.current_page)]);
  } catch (error) {
    const message = error instanceof ApiError ? error.message : 'Failed to delete user';
    push('error', message);
  }
}

function applyFilter() {
  filters.page = 1;
  void loadUsers();
}

function onPageChange(page: number) {
  filters.page = page;
  void loadUsers();
}

function onLogPageChange(page: number) {
  void loadAuditLogs(page);
}

onMounted(async () => {
  startCreate();
  await Promise.all([loadUsers(), loadAuditLogs()]);
});
</script>

<template>
  <AppContainer>
    <AppTopbar />

    <main class="mx-auto max-w-6xl space-y-4 px-4 py-6">
      <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">User Management</h1>

      <section class="rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-4 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
          {{ editingId ? 'Edit User' : 'Create User' }}
        </h2>

        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
          <BaseInput v-model="form.name" label="Name" required />
          <BaseInput v-model="form.email" label="Email" type="email" required />
          <BaseSelect
            v-model="form.role"
            label="Role"
            :options="[
              { label: 'User', value: 'user' },
              { label: 'Admin', value: 'admin' },
            ]"
          />
          <BaseInput
            v-model="form.password"
            label="Password"
            type="password"
            :required="!editingId"
            placeholder="Min 8 chars"
          />
          <BaseInput
            v-model="form.password_confirmation"
            label="Confirm Password"
            type="password"
            :required="!editingId"
          />
        </div>

        <div class="mt-3 flex gap-2">
          <BaseButton @click="submitForm">{{ editingId ? 'Update User' : 'Create User' }}</BaseButton>
          <BaseButton variant="secondary" @click="startCreate">Reset</BaseButton>
        </div>
      </section>

      <section class="rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
          <BaseInput v-model="filters.search" label="Search" placeholder="Name or email" />
          <BaseSelect
            v-model="filters.role"
            label="Role"
            :options="[
              { label: 'All Role', value: '' },
              { label: 'Admin', value: 'admin' },
              { label: 'User', value: 'user' },
            ]"
          />
          <div class="flex items-end gap-2">
            <BaseButton @click="applyFilter">Apply</BaseButton>
          </div>
        </div>
      </section>

      <section class="overflow-x-auto rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-100 dark:bg-slate-800/60 text-left text-slate-600 dark:text-slate-400">
            <tr>
              <th class="px-4 py-3">Name</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Role</th>
              <th class="px-4 py-3">Created</th>
              <th class="px-4 py-3 text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Loading users...</td>
            </tr>
            <tr v-else-if="users.length === 0">
              <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">No users found.</td>
            </tr>
            <tr v-for="user in users" :key="user.id" class="border-t border-slate-100 dark:border-slate-800">
              <td class="px-4 py-3">{{ user.name }}</td>
              <td class="px-4 py-3">{{ user.email }}</td>
              <td class="px-4 py-3">
                <select
                  class="rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-900 shadow-sm outline-none focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/20"
                  :value="user.role"
                  @change="onRoleSelectChange(user, $event)"
                >
                  <option value="user">user</option>
                  <option value="admin">admin</option>
                </select>
              </td>
              <td class="px-4 py-3">{{ new Date(user.created_at).toLocaleString() }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end gap-3">
                  <button class="text-indigo-700 hover:underline" @click="startEdit(user)">Edit</button>
                  <button class="text-red-700 hover:underline" @click="removeUser(user)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <PaginationBar
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        @change="onPageChange"
      />

      <section class="rounded-2xl border border-white/60 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:backdrop-brightness-75 p-4 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">User Management Audit Logs</h2>

        <div class="mt-3 space-y-2">
          <div v-if="logs.length === 0" class="text-sm text-slate-500 dark:text-slate-400">No audit logs.</div>
          <div v-for="log in logs" :key="log.id" class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 px-3 py-2 text-sm">
            <p class="font-medium text-slate-800 dark:text-slate-200">{{ log.action }}</p>
            <p class="text-slate-600 dark:text-slate-400">
              {{ log.actor?.email || 'unknown actor' }} -> {{ log.target?.email || 'unknown target' }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ new Date(log.created_at).toLocaleString() }}</p>
          </div>
        </div>

        <div class="mt-3">
          <PaginationBar
            :current-page="logPagination.current_page"
            :last-page="logPagination.last_page"
            @change="onLogPageChange"
          />
        </div>
      </section>
    </main>
  </AppContainer>
</template>
