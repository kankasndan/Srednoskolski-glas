"use client";

import { useEffect, useState } from "react";

// Komentarite se vchituvaat po prviot render, pa brauzerot ne skoka sam do niv.
export function useHashTarget(elementId) {
  const [isTarget, setIsTarget] = useState(false);

  useEffect(() => {
    if (!elementId) return undefined;

    function sync() {
      setIsTarget(window.location.hash === `#${elementId}`);
    }

    sync();
    window.addEventListener("hashchange", sync);

    return () => window.removeEventListener("hashchange", sync);
  }, [elementId]);

  return isTarget;
}

// Id-to na komentarot od hash-ot (#comment-123), ili null ako go nema.
export function useCommentHashId() {
  const [commentId, setCommentId] = useState(null);

  useEffect(() => {
    function sync() {
      const match = /^#comment-(\d+)$/.exec(window.location.hash);
      setCommentId(match ? Number(match[1]) : null);
    }

    sync();
    window.addEventListener("hashchange", sync);

    return () => window.removeEventListener("hashchange", sync);
  }, []);

  return commentId;
}
