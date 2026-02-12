import { reactive } from 'vue';

const TOKEN_KEY = 'document_app_token';
const EMAIL_KEY = 'document_app_email';
const NAME_KEY = 'document_app_name';
const ROLE_KEY = 'document_app_role';
const ID_KEY = 'document_app_user_id';

interface AuthState {
  token: string | null;
  id: string | null;
  name: string | null;
  email: string | null;
  role: 'admin' | 'user' | null;
}

const state = reactive<AuthState>({
  token: localStorage.getItem(TOKEN_KEY),
  id: localStorage.getItem(ID_KEY),
  name: localStorage.getItem(NAME_KEY),
  email: localStorage.getItem(EMAIL_KEY),
  role: (localStorage.getItem(ROLE_KEY) as 'admin' | 'user' | null) ?? null,
});

export function useAuthStore() {
  function setAuth(token: string) {
    state.token = token;
    localStorage.setItem(TOKEN_KEY, token);
  }

  function setProfile(profile: { id: string; name: string; email: string; role: 'admin' | 'user' }) {
    state.id = profile.id;
    state.name = profile.name;
    state.email = profile.email;
    state.role = profile.role;

    localStorage.setItem(ID_KEY, profile.id);
    localStorage.setItem(NAME_KEY, profile.name);
    localStorage.setItem(EMAIL_KEY, profile.email);
    localStorage.setItem(ROLE_KEY, profile.role);
  }

  function clearAuth() {
    state.token = null;
    state.id = null;
    state.name = null;
    state.email = null;
    state.role = null;

    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(ID_KEY);
    localStorage.removeItem(NAME_KEY);
    localStorage.removeItem(EMAIL_KEY);
    localStorage.removeItem(ROLE_KEY);
  }

  return {
    state,
    setAuth,
    setProfile,
    clearAuth,
  };
}
