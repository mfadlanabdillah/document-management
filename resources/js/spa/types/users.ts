export interface UserItem {
  id: string;
  name: string;
  email: string;
  role: 'admin' | 'user';
  created_at: string;
  updated_at: string;
}

export interface UserListResponse {
  data: UserItem[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface UserAuditLogItem {
  id: string;
  action: string;
  meta: Record<string, unknown> | null;
  actor: {
    id: string | null;
    name: string | null;
    email: string | null;
  };
  target: {
    id: string | null;
    name: string | null;
    email: string | null;
  };
  created_at: string;
}

export interface UserAuditLogListResponse {
  data: UserAuditLogItem[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}
