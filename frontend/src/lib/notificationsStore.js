import { getNotifications, markAllNotificationsRead as postMarkAllRead, markNotificationRead as postMarkRead } from "@/api/notifications";

const POLL_MS = 30000;

/** @typedef {{ items: object[], unreadCount: number, loading: boolean }} NotificationState */

/** @type {NotificationState} */
let state = {
  items: [],
  unreadCount: 0,
  loading: false,
};

/** @type {Promise<NotificationState> | null} */
let inflight = null;
const listeners = new Set();
let pollTimer = null;
let subscriberCount = 0;
let listeningToWindow = false;

/** Ids we marked read locally until a fetch from the server confirms `read_at`. */
const pendingReadIds = new Set();
let pendingReadAll = false;

function emit() {
  for (const listener of listeners) listener(state);
}

function nowIso() {
  return new Date().toISOString();
}

function applyLocalRead(id) {
  const current = state.items.find((item) => item.id === id);
  const wasUnread = current != null && current.read_at == null;

  state = {
    ...state,
    items: state.items.map((item) =>
      item.id === id ? { ...item, read_at: item.read_at ?? nowIso() } : item,
    ),
    unreadCount: wasUnread ? Math.max(0, state.unreadCount - 1) : state.unreadCount,
  };
  emit();
}

function applyLocalReadAll() {
  state = {
    ...state,
    items: state.items.map((item) =>
      item.read_at ? item : { ...item, read_at: nowIso() },
    ),
    unreadCount: 0,
  };
  emit();
}

function mergeFetched(payload) {
  let { items, unreadCount } = payload;

  if (pendingReadAll) {
    items = items.map((item) => ({
      ...item,
      read_at: item.read_at ?? nowIso(),
    }));
    unreadCount = 0;
    if (payload.unreadCount === 0) {
      pendingReadAll = false;
      pendingReadIds.clear();
    }

    return { items, unreadCount };
  }

  items = items.map((item) => {
    if (!pendingReadIds.has(item.id)) {
      return item;
    }

    if (item.read_at) {
      pendingReadIds.delete(item.id);
      return item;
    }

    unreadCount = Math.max(0, unreadCount - 1);
    return { ...item, read_at: nowIso() };
  });

  return { items, unreadCount };
}

function onFocus() {
  refreshNotifications();
}

function onVisibility() {
  if (typeof document !== "undefined" && document.visibilityState === "visible") {
    refreshNotifications();
  }
}

function startPolling() {
  refreshNotifications();

  if (pollTimer == null) {
    pollTimer = setInterval(() => {
      if (typeof document !== "undefined" && document.visibilityState === "hidden") {
        return;
      }
      refreshNotifications();
    }, POLL_MS);
  }

  if (typeof window !== "undefined" && !listeningToWindow) {
    listeningToWindow = true;
    window.addEventListener("focus", onFocus);
    document.addEventListener("visibilitychange", onVisibility);
  }
}

function stopPolling() {
  if (pollTimer != null) {
    clearInterval(pollTimer);
    pollTimer = null;
  }

  if (typeof window !== "undefined" && listeningToWindow) {
    listeningToWindow = false;
    window.removeEventListener("focus", onFocus);
    document.removeEventListener("visibilitychange", onVisibility);
  }
}

export function getNotificationState() {
  return state;
}

export function subscribeNotifications(listener) {
  listeners.add(listener);
  subscriberCount += 1;

  if (subscriberCount === 1) {
    startPolling();
  }

  listener(state);

  return () => {
    listeners.delete(listener);
    subscriberCount -= 1;
    if (subscriberCount === 0) {
      stopPolling();
    }
  };
}

export async function refreshNotifications() {
  if (inflight) return inflight;

  inflight = (async () => {
    if (state.items.length === 0) {
      state = { ...state, loading: true };
      emit();
    }

    try {
      const payload = await getNotifications();
      const merged = mergeFetched(payload);
      state = {
        items: merged.items,
        unreadCount: merged.unreadCount,
        loading: false,
      };
    } catch {
      state = { ...state, loading: false };
    } finally {
      inflight = null;
      emit();
    }

    return state;
  })();

  return inflight;
}

export async function markNotificationRead(id) {
  pendingReadIds.add(id);
  applyLocalRead(id);

  try {
    await postMarkRead(id);
  } catch {
    pendingReadIds.delete(id);
    await refreshNotifications();
  }
}

export async function markAllNotificationsRead() {
  if (state.unreadCount === 0 && !pendingReadAll) return;

  pendingReadAll = true;
  applyLocalReadAll();

  try {
    await postMarkAllRead();
  } catch {
    pendingReadAll = false;
    pendingReadIds.clear();
    await refreshNotifications();
  }
}
