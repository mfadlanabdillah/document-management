import { apiRequest } from './http';
import type { Category, Tag } from '../types';

export function getCategories(token: string) {
  return apiRequest<{ data?: Category[] } | Category[]>('/categories', {
    token,
  });
}

export function getTags(token: string) {
  return apiRequest<{ data?: Tag[] } | Tag[]>('/tags', {
    token,
  });
}
