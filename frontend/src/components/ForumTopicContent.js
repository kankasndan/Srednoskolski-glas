"use client";

import { useEffect, useState } from "react";
import ForumEmptyState from "@/components/ForumEmptyState";
import ForumThreadList from "@/components/ForumThreadList";
import { getUserThreadsForForum } from "@/lib/userThreads";

export default function ForumTopicContent({ forumSlug, forumName }) {
  const [hasUserThreads, setHasUserThreads] = useState(false);

  useEffect(() => {
    function refresh() {
      setHasUserThreads(getUserThreadsForForum(forumSlug).length > 0);
    }

    refresh();
    window.addEventListener("focus", refresh);
    document.addEventListener("visibilitychange", refresh);

    return () => {
      window.removeEventListener("focus", refresh);
      document.removeEventListener("visibilitychange", refresh);
    };
  }, [forumSlug]);

  if (!hasUserThreads) {
    return <ForumEmptyState />;
  }

  return (
    <ForumThreadList forumSlug={forumSlug} forumName={forumName} includeMockThreads={false} />
  );
}
