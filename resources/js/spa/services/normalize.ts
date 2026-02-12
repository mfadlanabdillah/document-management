import type { Category, DocumentDetail, DocumentItem, Tag } from '../types';

type Envelope<T> = {
  success?: boolean;
  message?: string;
  data?: T | { data?: T };
};

function isObject(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}

export function extractList<T>(payload: Envelope<T[]> | { data?: T[] } | T[]): T[] {
  if (Array.isArray(payload)) {
    return payload;
  }

  const maybeData = payload.data;

  if (Array.isArray(maybeData)) {
    return maybeData;
  }

  if (isObject(maybeData) && Array.isArray(maybeData.data)) {
    return maybeData.data as T[];
  }

  return [];
}

export function extractDocument(payload: Envelope<DocumentDetail> | { data?: DocumentDetail } | DocumentDetail): DocumentDetail {
  if ('id' in payload) {
    return payload;
  }

  if (isObject(payload.data) && 'id' in payload.data) {
    return payload.data as DocumentDetail;
  }

  throw new Error('Invalid document response payload.');
}

export function normalizeCategories(payload: { data?: Category[] } | Category[]) {
  return extractList(payload);
}

export function normalizeTags(payload: { data?: Tag[] } | Tag[]) {
  return extractList(payload);
}

export function normalizeDocuments(payload: {
  data: DocumentItem[];
  links?: {
    first?: string | null;
    last?: string | null;
    prev?: string | null;
    next?: string | null;
  };
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}) {
  return {
    data: payload.data,
    links: payload.links,
    meta: payload.meta,
  };
}
