"use client";

import { useState } from "react";
import { followThread, unfollowThread } from "@/api/threads";
import { useProfile } from "@/hooks/useProfile";

const GUEST_ERROR = "Мора да си најавен за да следиш дискусија.";

/**
 * Follow / unfollow a thread (visual only in MVP).
 * Colours match FollowForumButton.
 */
export default function FollowThreadButton({
  threadId,
  initialFollowing = false,
  onFollowingChange,
  className = "",
  wrapperClassName = "",
}) {
  const { user, loading: profileLoading } = useProfile();
  const [following, setFollowing] = useState(Boolean(initialFollowing));
  const [pending, setPending] = useState(false);
  const [error, setError] = useState("");

  async function toggleFollow() {
    if (!threadId || pending) return;

    setError("");

    if (!profileLoading && user == null) {
      setError(GUEST_ERROR);
      return;
    }

    const nextFollowing = !following;
    setFollowing(nextFollowing);
    setPending(true);
    onFollowingChange?.(nextFollowing);

    try {
      const data = nextFollowing
        ? await followThread(threadId)
        : await unfollowThread(threadId);

      if (typeof data?.is_following === "boolean") {
        setFollowing(data.is_following);
        onFollowingChange?.(data.is_following);
      }
    } catch (err) {
      setFollowing(!nextFollowing);
      onFollowingChange?.(!nextFollowing);
      if (err?.status === 401) {
        setError(GUEST_ERROR);
      }
    } finally {
      setPending(false);
    }
  }

  const stateClasses = following
    ? "bg-[var(--color-primary-200)] text-white hover:bg-[var(--color-primary-300)]"
    : "bg-[var(--color-primary-200)] text-white hover:bg-[var(--color-primary-300)]";

  return (
    <div className={`flex shrink-0 flex-col gap-1 ${wrapperClassName}`}>
      <button
        type="button"
        aria-pressed={following}
        disabled={pending}
        onClick={toggleFollow}
        className={`flex h-10 cursor-pointer items-center justify-center gap-3 rounded-xl px-4 py-2 font-[family-name:var(--font-manrope)] text-[12px] font-bold leading-none whitespace-nowrap transition-colors disabled:cursor-not-allowed disabled:opacity-80 md:text-[14px] ${stateClasses} ${className}`}
      >
        {following && <CheckIcon />}
        <span className="flex h-[19px] items-center leading-none">
          {following ? "Следиш" : "Следи ја дискусијата"}
        </span>
      </button>
      {error ? (
        <p className="font-[family-name:var(--font-manrope)] text-[12px] leading-4 text-[#DC2626]">
          {error}
        </p>
      ) : null}
    </div>
  );
}

function CheckIcon() {
  return (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
      <path
        d="M13.5 4.5L6.5 11.5L3 8"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}
