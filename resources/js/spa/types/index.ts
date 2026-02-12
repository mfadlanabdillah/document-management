export type DocumentStatus = string;

export interface AuthResponse {
  token: string;
}

export interface Category {
  id: string;
  name: string;
  slug?: string;
  description?: string;
}

export interface Tag {
  id: string;
  name: string;
  slug?: string;
}

export interface DocumentVersionSummary {
  id: string;
  version_number: number;
  file_name: string;
}

export interface DocumentItem {
  id: string;
  title: string;
  status: DocumentStatus;
  deleted_at?: string | null;
  category: {
    id: string | null;
    name: string | null;
  };
  current_version: DocumentVersionSummary | null;
  created_at: string;
  updated_at: string;
}

export interface DocumentDetail extends DocumentItem {
  tags?: Array<{ id: string; name: string }>;
}

export interface DocumentVersion {
  id: string;
  version_number: number;
  file_name: string;
  file_size: number;
  mime_type: string;
  notes: string | null;
  uploaded_at: string;
}

export interface DocumentActivity {
  id: string;
  action: string;
  meta: Record<string, unknown> | null;
  performed_by: {
    id: string | null;
    name: string | null;
  };
  created_at: string;
}

export interface StatusItem {
  id: string;
  code: string;
  name: string;
  description: string | null;
  is_active: boolean;
  sort_order: number;
  allowed_next_codes?: string[];
  created_at?: string;
  updated_at?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
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
}

export interface DocumentFilters {
  status: string;
  category_id: string;
  search: string;
  sort: string;
  direction: 'asc' | 'desc';
  page: number;
}

export interface ApiErrorPayload {
  message?: string;
  errors?: Record<string, string[]>;
}
