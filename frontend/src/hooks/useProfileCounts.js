"use client";

import { useEffect, useState } from "react";
import {
  getMyThreads,
  getMyComments,
  getMyFollowedForums,
  getMyFollowedThreads,
} from "@/api/profile";

export function useProfileCounts() {
  const [counts, setCounts] = useState(null);

  useEffect(() => {
    let active = true;

    Promise.all([
      getMyThreads(),
      getMyComments(),
      getMyFollowedForums(),
      getMyFollowedThreads(),
    ])
      .then(([threads, comments, followedForums, followedThreads]) => {
        if (!active) return;

        setCounts({
          threads: threads.length,
          comments: comments.length,
          follows: followedForums.length + followedThreads.length,
        });
      })
      .catch(() => {
        if (active) setCounts(null);
      });

    return () => {
      active = false;
    };
  }, []);

  return counts;
}
