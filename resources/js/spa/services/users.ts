import { apiRequest } from './http';
import type { UserAuditLogListResponse, UserItem, UserListResponse } from '../types/users';

export interface UserQuery {
  search?: string;
  role?: string;
  page?: number;
}

export interface UserUpsertInput {
  name: string;
  email: string;
  role?: 'admin' | 'user';
  password?: string;
  password_confirmation?: string;
}

export function getUsers(token: string, query: UserQuery = {}) {
  return apiRequest<UserListResponse>('/users', {
    token,
    query,
  });
}

export function getUserAuditLogs(token: string, page = 1) {
  return apiRequest<UserAuditLogListResponse>('/users/audit-logs', {
    token,
    query: { page },
  });
}

export function createUser(token: string, payload: UserUpsertInput) {
  return apiRequest<{ data?: UserItem } | UserItem>('/users', {
    method: 'POST',
    token,
    body: payload,
  });
}

export function updateUser(token: string, id: string, payload: UserUpsertInput) {
  return apiRequest<{ data?: UserItem } | UserItem>(`/users/${id}`, {
    method: 'PATCH',
    token,
    body: payload,
  });
}

export function updateUserRole(token: string, id: string, role: 'admin' | 'user') {
  return apiRequest<{ data?: UserItem } | UserItem>(`/users/${id}/role`, {
    method: 'PATCH',
    token,
    body: { role },
  });
}

export function deleteUser(token: string, id: string) {
  return apiRequest<{ success?: boolean; message?: string }>(`/users/${id}`, {
    method: 'DELETE',
    token,
  });
}
