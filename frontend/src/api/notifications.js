import { apiFetch } from "@/lib/api";

export async function getNotifications() {
  const res = await apiFetch("/api/me/notifications");

  if (!res.ok) {
    throw new Error(`Failed to load notifications: ${res.status}`);
  }

  const payload = await res.json();

  return {
    items: Array.isArray(payload.data) ? payload.data : [],
    unreadCount: Number(payload.unread_count) || 0,
  };
}

export async function markNotificationRead(id) {
  const res = await apiFetch(`/api/me/notifications/${encodeURIComponent(id)}/read`, {
    method: "POST",
  });

  const body = await res.json().catch(() => ({}));

  if (!res.ok) {
    const error = new Error(body.message || `Failed to mark notification read (${res.status})`);
    error.status = res.status;
    throw error;
  }

  return body.data;
}

export async function markAllNotificationsRead() {
  const res = await apiFetch("/api/me/notifications/read-all", {
    method: "POST",
  });

  const body = await res.json().catch(() => ({}));

  if (!res.ok) {
    const error = new Error(body.message || `Failed to mark notifications read (${res.status})`);
    error.status = res.status;
    throw error;
  }

  return body.data;
}
