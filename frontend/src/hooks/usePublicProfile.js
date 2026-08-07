"use client";

import { useCallback, useEffect, useState } from "react";
import { getPublicProfile } from "@/api/profile";

export function usePublicProfile(username) {
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const reload = useCallback(() => {
    if (!username) return Promise.resolve();

    return getPublicProfile(username)
      .then((data) => {
        setProfile(data);
        setError(null);
      })
      .catch((err) => {
        setError(err);
        setProfile(null);
      });
  }, [username]);

  useEffect(() => {
    let active = true;

    if (!username) {
      setLoading(false);
      setProfile(null);
      return undefined;
    }

    getPublicProfile(username)
      .then((data) => {
        if (!active) return;
        setProfile(data);
        setError(null);
      })
      .catch((err) => {
        if (!active) return;
        setError(err);
        setProfile(null);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [username]);

  const patchFollow = useCallback((patch) => {
    setProfile((prev) => {
      if (!prev) return prev;

      return {
        ...prev,
        is_following:
          typeof patch.is_following === "boolean"
            ? patch.is_following
            : prev.is_following,
        counts: {
          ...prev.counts,
          followers:
            typeof patch.followers === "number"
              ? patch.followers
              : prev.counts?.followers,
        },
      };
    });
  }, []);

  return {
    user: profile?.user ?? null,
    counts: profile?.counts
      ? {
          threads: profile.counts.threads ?? 0,
          comments: profile.counts.comments ?? 0,
          follows: profile.counts.followed_forums ?? 0,
          followers: profile.counts.followers ?? 0,
        }
      : null,
    isFollowing: Boolean(profile?.is_following),
    isOwnProfile: Boolean(profile?.is_own_profile),
    loading,
    error,
    missing: !loading && !error && profile === null,
    reload,
    patchFollow,
  };
}
