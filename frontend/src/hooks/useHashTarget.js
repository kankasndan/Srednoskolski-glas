"use client";

import { useEffect, useState } from "react";
import { usePathname, useSearchParams } from "next/navigation";
import { parseCommentTarget } from "@/lib/commentLink";

function commentIdFromLocation() {
  if (typeof window === "undefined") return null;

  return parseCommentTarget(window.location.hash, window.location.search);
}

// Id-to na komentarot od ?comment=123 ili #comment-123 (i duplirani hash-ovi).
export function useCommentHashId() {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [commentId, setCommentId] = useState(null);

  useEffect(() => {
    setCommentId(commentIdFromLocation());
  }, [pathname, searchParams]);

  useEffect(() => {
    function sync() {
      setCommentId(commentIdFromLocation());
    }

    window.addEventListener("hashchange", sync);
    window.addEventListener("popstate", sync);

    return () => {
      window.removeEventListener("hashchange", sync);
      window.removeEventListener("popstate", sync);
    };
  }, []);

  const fromQuery = searchParams.get("comment");
  if (fromQuery && /^\d+$/.test(fromQuery)) {
    return Number(fromQuery);
  }

  return commentId;
}
