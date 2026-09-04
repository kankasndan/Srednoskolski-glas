"use client";

import { useEffect, useState } from "react";
import {
  getNotificationState,
  markAllNotificationsRead,
  markNotificationRead,
  subscribeNotifications,
} from "@/lib/notificationsStore";

export function useNotifications(enabled) {
  const [state, setState] = useState(getNotificationState);

  useEffect(() => {
    if (!enabled) return undefined;

    return subscribeNotifications(setState);
  }, [enabled]);

  return {
    items: enabled ? state.items : [],
    unreadCount: enabled ? state.unreadCount : 0,
    loading: enabled ? state.loading : false,
    markRead: markNotificationRead,
    markAllRead: markAllNotificationsRead,
  };
}
