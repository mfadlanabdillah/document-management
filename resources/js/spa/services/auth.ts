import { apiRequest } from './http';
import type { AuthResponse } from '../types';

interface LoginInput {
  email: string;
  password: string;
}

interface RegisterInput {
  email: string;
  password: string;
  password_confirmation: string;
}

export interface MeResponse {
  id: string;
  name: string;
  email: string;
  role: 'admin' | 'user';
}

export function login(input: LoginInput) {
  return apiRequest<AuthResponse>('/auth/login', {
    method: 'POST',
    body: input,
  });
}

export function register(input: RegisterInput) {
  return apiRequest<AuthResponse>('/auth/register', {
    method: 'POST',
    body: input,
  });
}

export function me(token: string) {
  return apiRequest<MeResponse>('/auth/me', {
    token,
  });
}
