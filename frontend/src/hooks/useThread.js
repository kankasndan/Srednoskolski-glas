"use client";

import { useCallback, useEffect, useState } from "react";
import { getThread } from "@/api/threads";

export function useThread(forumSlug, threadId, sort = "best") {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const reload = useCallback(() => {
    if (!forumSlug || !threadId) return Promise.resolve();

    setLoading(true);
    setError(null);

    return getThread(forumSlug, threadId, { sort })
      .then((payload) => {
        setData(payload);
      })
      .catch((err) => {
        setError(err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, [forumSlug, threadId, sort]);

  useEffect(() => {
    let active = true;

    setLoading(true);
    setError(null);

    getThread(forumSlug, threadId, { sort })
      .then((payload) => {
        if (active) setData(payload);
      })
      .catch((err) => {
        if (active) setError(err);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [forumSlug, threadId, sort]);

  return {
    forum: data?.thread?.forum ?? null,
    thread: data?.thread ?? null,
    comments: data?.comments ?? [],
    loading,
    error,
    missing: !loading && !error && data === null,
    reload,
  };
}
