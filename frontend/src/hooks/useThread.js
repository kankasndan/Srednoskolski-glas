"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { getThread } from "@/api/threads";

export function useThread(forumSlug, threadId, sort = "best") {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const viewTrackedKey = useRef("");

  const reload = useCallback(() => {
    if (!forumSlug || !threadId) return Promise.resolve();

    return getThread(forumSlug, threadId, { sort, trackView: false })
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
    const key = `${forumSlug}:${threadId}`;
    const trackView = viewTrackedKey.current !== key;

    getThread(forumSlug, threadId, { sort, trackView })
      .then((payload) => {
        if (!active) return;
        if (trackView) viewTrackedKey.current = key;
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
          poll: Object.hasOwn(updated, "poll") ? updated.poll : prev.thread.poll,
        },
      };
    });
  }, []);

  const addComment = useCallback(
    (created) => {
      if (!created) return;

      setData((prev) => {
        if (!prev?.thread) return prev;

        const comments = prev.comments ?? [];
        const nextComments = created.parent_id
          ? comments
          : sort === "oldest"
            ? [...comments, created]
            : [created, ...comments];

        return {
          ...prev,
          comments: nextComments,
          thread: {
            ...prev.thread,
            comments_count: (prev.thread.comments_count ?? 0) + 1,
          },
        };
      });
    },
    [sort],
  );

  // Komentarot ostanuva vo listata; brojachot sledi shto vrakja backendot.
  const markCommentDeleted = useCallback((commentId) => {
    if (!commentId) return;

    setData((prev) => {
      if (!prev?.thread) return prev;

      return {
        ...prev,
        thread: {
          ...prev.thread,
          comments_count: Math.max(0, (prev.thread.comments_count ?? 0) - 1),
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
    addComment,
    markCommentDeleted,
  };
}
