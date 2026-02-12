import { apiRequest } from './http';
import type { Category, StatusItem, Tag } from '../types';

export function getStatuses(token: string) {
  return apiRequest<StatusItem[]>('/document-statuses', { token });
}

export function getStatusTransitions(token: string) {
  return apiRequest<
    Array<{
      from_status_id: string;
      from_status_code: string;
      to_status_ids: string[];
      to_status_codes: string[];
    }>
  >('/master/status-transitions', { token });
}

export function updateStatusTransitions(token: string, statusId: string, toStatusIds: string[]) {
  return apiRequest<{ success: boolean }>(`/master/statuses/${statusId}/transitions`, {
    method: 'PATCH',
    token,
    body: {
      to_status_ids: toStatusIds,
    },
  });
}

export interface StatusPayload {
  code: string;
  name: string;
  description?: string;
  is_active?: boolean;
  sort_order?: number;
}

export function createStatus(token: string, payload: StatusPayload) {
  return apiRequest<StatusItem>('/master/statuses', {
    method: 'POST',
    token,
    body: payload,
  });
}

export function updateStatusMaster(token: string, id: string, payload: Partial<StatusPayload>) {
  return apiRequest<StatusItem>(`/master/statuses/${id}`, {
    method: 'PATCH',
    token,
    body: payload,
  });
}

export function deleteStatusMaster(token: string, id: string) {
  return apiRequest<{ success: boolean }>(`/master/statuses/${id}`, {
    method: 'DELETE',
    token,
  });
}

export function getMasterCategories(token: string) {
  return apiRequest<Category[]>('/master/categories', { token });
}

export function createCategoryMaster(token: string, payload: { name: string; slug?: string; description?: string }) {
  return apiRequest<Category>('/master/categories', {
    method: 'POST',
    token,
    body: payload,
  });
}

export function updateCategoryMaster(
  token: string,
  id: string,
  payload: { name?: string; slug?: string; description?: string },
) {
  return apiRequest<Category>(`/master/categories/${id}`, {
    method: 'PATCH',
    token,
    body: payload,
  });
}

export function deleteCategoryMaster(token: string, id: string) {
  return apiRequest<{ success: boolean }>(`/master/categories/${id}`, {
    method: 'DELETE',
    token,
  });
}

export function getMasterTags(token: string) {
  return apiRequest<Tag[]>('/master/tags', { token });
}

export function createTagMaster(token: string, payload: { name: string; slug?: string }) {
  return apiRequest<Tag>('/master/tags', {
    method: 'POST',
    token,
    body: payload,
  });
}

export function updateTagMaster(token: string, id: string, payload: { name?: string; slug?: string }) {
  return apiRequest<Tag>(`/master/tags/${id}`, {
    method: 'PATCH',
    token,
    body: payload,
  });
}

export function deleteTagMaster(token: string, id: string) {
  return apiRequest<{ success: boolean }>(`/master/tags/${id}`, {
    method: 'DELETE',
    token,
  });
}
