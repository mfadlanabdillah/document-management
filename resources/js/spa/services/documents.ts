import { apiRequest } from './http';
import type {
  DocumentActivity,
  DocumentDetail,
  DocumentFilters,
  DocumentItem,
  DocumentStatus,
  DocumentVersion,
  PaginatedResponse,
} from '../types';

interface DocumentListApiResponse {
  data: DocumentItem[];
  links?: PaginatedResponse<DocumentItem>['links'];
  meta?: PaginatedResponse<DocumentItem>['meta'];
}

export interface DocumentUpsertInput {
  title: string;
  category_id: string;
  tag_ids?: string[];
  notes?: string;
  file?: File | null;
}

export interface VersionUploadInput {
  file: File;
  notes?: string;
}

function toListQuery(filters: Partial<DocumentFilters>) {
  return {
    status: filters.status,
    category_id: filters.category_id,
    search: filters.search,
    sort: filters.sort,
    direction: filters.direction,
    page: filters.page,
  };
}

export function getDocuments(token: string, filters: Partial<DocumentFilters>) {
  return apiRequest<DocumentListApiResponse>('/documents', {
    token,
    query: toListQuery(filters),
  });
}

export function getTrashedDocuments(token: string, filters: Partial<DocumentFilters>) {
  return apiRequest<DocumentListApiResponse>('/documents/trash', {
    token,
    query: toListQuery(filters),
  });
}

export function getDocumentById(token: string, id: string) {
  return apiRequest<{ data?: DocumentDetail } | DocumentDetail>(`/documents/${id}`, {
    token,
  });
}

export async function createDocument(token: string, input: DocumentUpsertInput) {
  const formData = new FormData();
  formData.append('title', input.title);
  formData.append('category_id', input.category_id);

  if (input.notes) {
    formData.append('notes', input.notes);
  }

  input.tag_ids?.forEach((tagId) => {
    formData.append('tag_ids[]', tagId);
  });

  if (input.file) {
    formData.append('file', input.file);
  }

  return apiRequest<{ data?: DocumentDetail } | DocumentDetail>('/documents', {
    method: 'POST',
    token,
    formData,
  });
}

export async function updateDocument(token: string, id: string, input: Omit<DocumentUpsertInput, 'file'>) {
  const payload = {
    title: input.title,
    category_id: input.category_id,
    tag_ids: input.tag_ids ?? [],
    notes: input.notes,
  };

  // API spec says PATCH /documents/{id}, current backend may use PUT.
  try {
    return await apiRequest<{ data?: DocumentDetail } | DocumentDetail>(`/documents/${id}`, {
      method: 'PATCH',
      token,
      body: payload,
    });
  } catch {
    return apiRequest<{ data?: DocumentDetail } | DocumentDetail>(`/documents/${id}`, {
      method: 'PUT',
      token,
      body: payload,
    });
  }
}

export async function updateDocumentStatus(token: string, id: string, status: DocumentStatus) {
  // API spec says /documents/{id}/status, current backend may use PATCH /documents/{id}
  try {
    return await apiRequest<{ data?: DocumentDetail } | DocumentDetail>(`/documents/${id}/status`, {
      method: 'PATCH',
      token,
      body: { status },
    });
  } catch {
    return apiRequest<{ data?: DocumentDetail } | DocumentDetail>(`/documents/${id}`, {
      method: 'PATCH',
      token,
      body: { status },
    });
  }
}

export function deleteDocument(token: string, id: string) {
  return apiRequest<unknown>(`/documents/${id}`, {
    method: 'DELETE',
    token,
  });
}

export function restoreDocument(token: string, id: string) {
  return apiRequest<{ data?: DocumentDetail } | DocumentDetail>(`/documents/${id}/restore`, {
    method: 'POST',
    token,
  });
}

export function getDocumentVersions(token: string, documentId: string) {
  return apiRequest<{ data?: DocumentVersion[] } | DocumentVersion[]>(
    `/documents/${documentId}/versions`,
    { token },
  );
}

export function uploadDocumentVersion(token: string, documentId: string, input: VersionUploadInput) {
  const formData = new FormData();
  formData.append('file', input.file);

  if (input.notes) {
    formData.append('notes', input.notes);
  }

  return apiRequest<{ data?: DocumentVersion } | DocumentVersion>(`/documents/${documentId}/versions`, {
    method: 'POST',
    token,
    formData,
  });
}

export function getDocumentActivities(token: string, documentId: string) {
  return apiRequest<{ data?: DocumentActivity[] } | DocumentActivity[]>(
    `/documents/${documentId}/activities`,
    { token },
  );
}

export async function downloadDocumentVersion(
  token: string,
  documentId: string,
  versionId: string,
  fileName: string,
) {
  const apiBase = import.meta.env.VITE_API_BASE_URL ?? 'http://127.0.0.1:8000/api';
  const url = `${apiBase}/documents/${documentId}/versions/${versionId}/download`;

  const response = await fetch(url, {
    method: 'GET',
    headers: {
      Accept: 'application/octet-stream',
      Authorization: `Bearer ${token}`,
    },
  });

  if (!response.ok) {
    throw new Error(`Download failed with status ${response.status}`);
  }

  const blob = await response.blob();
  const blobUrl = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = blobUrl;
  anchor.download = fileName;
  document.body.appendChild(anchor);
  anchor.click();
  anchor.remove();
  URL.revokeObjectURL(blobUrl);
}
