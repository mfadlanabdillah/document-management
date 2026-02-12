import { createRouter, createWebHistory } from 'vue-router';
import DashboardPage from './pages/DashboardPage.vue';
import DocumentDetailPage from './pages/DocumentDetailPage.vue';
import DocumentFormPage from './pages/DocumentFormPage.vue';
import LoginPage from './pages/LoginPage.vue';
import MasterDataPage from './pages/MasterDataPage.vue';
import NotFoundPage from './pages/NotFoundPage.vue';
import RegisterPage from './pages/RegisterPage.vue';
import UsersPage from './pages/UsersPage.vue';
import { useAuthStore } from './stores/authStore';

const router = createRouter({
  history: createWebHistory('/app'),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: LoginPage,
      meta: { guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterPage,
      meta: { guestOnly: true },
    },
    {
      path: '/',
      name: 'dashboard',
      component: DashboardPage,
      meta: { requiresAuth: true },
    },
    {
      path: '/users',
      name: 'users',
      component: UsersPage,
      meta: { requiresAuth: true, adminOnly: true },
    },
    {
      path: '/master-data',
      name: 'master-data',
      component: MasterDataPage,
      meta: { requiresAuth: true, adminOnly: true },
    },
    {
      path: '/documents/create',
      name: 'document-create',
      component: DocumentFormPage,
      meta: { requiresAuth: true },
    },
    {
      path: '/documents/:id',
      name: 'document-detail',
      component: DocumentDetailPage,
      meta: { requiresAuth: true },
    },
    {
      path: '/documents/:id/edit',
      name: 'document-edit',
      component: DocumentFormPage,
      meta: { requiresAuth: true },
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: NotFoundPage,
    },
  ],
});

router.beforeEach((to) => {
  const { state } = useAuthStore();
  const isAuthenticated = Boolean(state.token);

  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'login' };
  }

  if (to.meta.guestOnly && isAuthenticated) {
    return { name: 'dashboard' };
  }

  if (to.meta.adminOnly && state.role !== 'admin') {
    return { name: 'dashboard' };
  }

  return true;
});

export default router;
