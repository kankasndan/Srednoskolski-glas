"use client";

import { useEffect, useState } from "react";
import { getThread } from "@/api/threads";

export function useThread(forumSlug, threadId) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let active = true;

    getThread(forumSlug, threadId)
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
  }, [forumSlug, threadId]);

  return {
    forum: data?.thread?.forum ?? null,
    thread: data?.thread ?? null,
    comments: data?.comments ?? [],
    loading,
    error,
    missing: !loading && !error && data === null,
  };
}
