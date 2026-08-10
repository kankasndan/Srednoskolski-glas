"use client";

import { useState } from "react";
import { followForum, unfollowForum } from "@/api/forums";

/**
 * Follow / unfollow a general forum.
 * School forums never render this button — membership is fixed at onboarding.
 */
export default function FollowForumButton({
  slug,
  initialFollowing = false,
  onMembersCountChange,
  className = "",
}) {
  const [following, setFollowing] = useState(Boolean(initialFollowing));
  const [pending, setPending] = useState(false);

  async function toggleFollow() {
    if (!slug || pending) return;

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
    } catch {
      setFollowing(!nextFollowing);
    } finally {
      setPending(false);
    }
  }

  const stateClasses = following
    ? "bg-[var(--color-primary-300)] text-white"
    : "bg-[#582FF5] text-white hover:bg-[#DCEBED] hover:text-[#0A0A0A]";

  return (
    <button
      type="button"
      aria-pressed={following}
      disabled={pending}
      onClick={toggleFollow}
      className={`flex h-10 w-[268px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none transition-colors disabled:cursor-wait disabled:opacity-80 ${stateClasses} ${className}`}
    >
      {following && <CheckIcon />}
      <span className="flex h-[19px] items-center leading-none">
        {following ? "Следиш" : "Следи го форумот"}
      </span>
    </button>
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
