"use client";

import { useCallback, useEffect, useState } from "react";
import { getThread } from "@/api/threads";

export function useThread(forumSlug, threadId, sort = "best") {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const reload = useCallback(() => {
    if (!forumSlug || !threadId) return Promise.resolve();

    return getThread(forumSlug, threadId, { sort })
      .then((payload) => {
        setData(payload);
        setError(null);
      })
      .catch((err) => {
        setError(err);
      });
  }, [forumSlug, threadId, sort]);

  useEffect(() => {
    let active = true;

    getThread(forumSlug, threadId, { sort })
      .then((payload) => {
        if (!active) return;
        setData(payload);
        setError(null);
      })
      .catch((err) => {
        if (!active) return;
        setError(err);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [forumSlug, threadId, sort]);

  const patchThread = useCallback((updated) => {
    setData((prev) => {
      if (!prev?.thread) return prev;

      return {
        ...prev,
        thread: {
          ...prev.thread,
          ...updated,
          forum: updated.forum ?? prev.thread.forum,
          attachments: updated.attachments ?? prev.thread.attachments,
          poll: updated.poll ?? prev.thread.poll,
        },
      };
    });
  }, []);

  return {
    forum: data?.thread?.forum ?? null,
    thread: data?.thread ?? null,
    comments: data?.comments ?? [],
    loading,
    error,
    missing: !loading && !error && data === null,
    reload,
    patchThread,
  };
}
