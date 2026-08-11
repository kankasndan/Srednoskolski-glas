"use client";

import { useEffect, useState } from "react";
import { getMyCounts } from "@/api/profile";

export function useProfileCounts() {
  const [counts, setCounts] = useState(null);

  useEffect(() => {
    let active = true;

    getMyCounts()
      .then((data) => {
        if (!active) return;

        setCounts({
          threads: data.threads ?? 0,
          comments: data.comments ?? 0,
          follows: data.followed_forums ?? 0,
          followingUsers: data.following_users ?? 0,
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
