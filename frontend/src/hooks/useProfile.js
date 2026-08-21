"use client";

import { useEffect, useState } from "react";
import {
  getCachedSessionUser,
  loadSessionUser,
  subscribeSessionUser,
} from "@/lib/sessionUser";

export function useProfile() {
  const cached = getCachedSessionUser();
  const [user, setUser] = useState(() => (cached === undefined ? null : cached));
  const [loading, setLoading] = useState(() => cached === undefined);
  const [error, setError] = useState(null);

  useEffect(() => {
    const unsubscribe = subscribeSessionUser((next) => {
      if (next === undefined) return;
      setUser(next);
      setLoading(false);
    });

    let active = true;
    const currentUser = getCachedSessionUser();

    // Refetch when the cached user is missing capability flags (stale session).
    const needsCapabilities =
      currentUser != null &&
      (currentUser.capabilities == null ||
        typeof currentUser.capabilities?.can_create_threads !== "boolean" ||
        typeof currentUser.capabilities?.can_change_school !== "boolean");

    loadSessionUser({ force: Boolean(needsCapabilities) })
      .then((data) => {
        if (!active) return;
        setUser(data);
        setError(null);
      })
      .catch((err) => {
        if (active) setError(err);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
      unsubscribe();
    };
  }, []);

  return { user, loading, error };
}
