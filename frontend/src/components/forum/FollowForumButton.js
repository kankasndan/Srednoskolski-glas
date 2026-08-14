"use client";

import { useState } from "react";
import { followForum, unfollowForum } from "@/api/forums";
import { useProfile } from "@/hooks/useProfile";

const GUEST_ERROR = "Мора да си најавен за да следиш форум.";

/**
 * Follow / unfollow a forum (general or school).
 * Own-school membership can be locked (unfollow disabled).
 */
export default function FollowForumButton({
  slug,
  initialFollowing = false,
  onMembersCountChange,
  locked = false,
  className = "",
}) {
  const { user, loading: profileLoading } = useProfile();
  const [following, setFollowing] = useState(Boolean(initialFollowing));
  const [pending, setPending] = useState(false);
  const [error, setError] = useState("");

  async function toggleFollow() {
    if (!slug || pending || locked) return;

    setError("");

    if (!profileLoading && user == null) {
      setError(GUEST_ERROR);
      return;
    }

    const nextFollowing = !following;
    setFollowing(nextFollowing);
    setPending(true);

    try {
      const data = nextFollowing
        ? await followForum(slug)
        : await unfollowForum(slug);

      if (typeof data?.is_following === "boolean") {
        setFollowing(data.is_following);
      }

      if (
        typeof data?.members_count === "number" &&
        typeof onMembersCountChange === "function"
      ) {
        onMembersCountChange(data.members_count);
      }
    } catch (err) {
      setFollowing(!nextFollowing);
      if (err?.status === 401) {
        setError(GUEST_ERROR);
      }
    } finally {
      setPending(false);
    }
  }

  const stateClasses = following
    ? "bg-[var(--color-primary-300)] text-white"
    : "bg-[#582FF5] text-white hover:bg-[#3300F5]";

  return (
    <div className="flex w-[268px] shrink-0 flex-col gap-1">
      <button
        type="button"
        aria-pressed={following}
        disabled={pending || locked}
        onClick={toggleFollow}
        title={locked ? "Форумот на твоето училиште е секогаш следен" : undefined}
        className={`flex h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-xl px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none transition-colors disabled:cursor-not-allowed disabled:opacity-80 ${stateClasses} ${className}`}
      >
        {following && <CheckIcon />}
        <span className="flex h-[19px] items-center leading-none">
          {following ? "Следиш" : "Следи го форумот"}
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
