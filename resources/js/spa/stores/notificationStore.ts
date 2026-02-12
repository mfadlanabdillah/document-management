import { reactive } from 'vue';

export type NotificationType = 'success' | 'error' | 'info';

export interface NotificationItem {
  id: number;
  type: NotificationType;
  message: string;
}

const state = reactive({
  items: [] as NotificationItem[],
});

let nextId = 1;

export function useNotificationStore() {
  function push(type: NotificationType, message: string, timeoutMs = 3500) {
    const id = nextId;
    nextId += 1;

    state.items.push({ id, type, message });

    window.setTimeout(() => {
      remove(id);
    }, timeoutMs);
  }

  function remove(id: number) {
    const index = state.items.findIndex((item) => item.id === id);
    if (index >= 0) {
      state.items.splice(index, 1);
    }
  }

  return {
    state,
    push,
    remove,
  };
}
