"use client";

import { useState } from "react";
import { followForum, unfollowForum } from "@/api/forums";
import CheckIcon from "@/components/ui/CheckIcon";
import { useProfile } from "@/hooks/useProfile";
import { needsOnboarding, ONBOARDING_REQUIRED_MESSAGE } from "@/lib/capabilities";

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

    if (!profileLoading && needsOnboarding(user)) {
      setError(ONBOARDING_REQUIRED_MESSAGE);
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
      } else if (err?.status === 403) {
        setError(err.message || ONBOARDING_REQUIRED_MESSAGE);
      }
    } finally {
      setPending(false);
    }
  }

  const stateClasses = following
    ? "bg-[var(--color-primary-300)] text-white"
    : "bg-[var(--color-primary-200)] text-white hover:bg-[var(--color-primary-300)]";

  return (
    <div className="flex min-w-0 flex-1 flex-col gap-1 @[680px]:w-[268px] @[680px]:flex-none">
      <button
        type="button"
        aria-pressed={following}
        disabled={pending || locked}
        onClick={toggleFollow}
        title={locked ? "Форумот на твоето училиште е секогаш следен" : undefined}
        className={`flex h-10 w-full cursor-pointer items-center justify-center gap-3 rounded-xl px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none transition-colors disabled:cursor-not-allowed disabled:opacity-80 ${stateClasses} ${className}`}
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

