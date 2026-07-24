"use client";

import { useState } from "react";

export default function FollowForumButton({ className = "" }) {
  const [following, setFollowing] = useState(false);

  const toggleFollow = () => setFollowing((current) => !current);

  const stateClasses = following
    ? "bg-[#A6E4ED] text-[#0A0A0A]"
    : "bg-[#582FF5] text-white hover:bg-[#DCEBED] hover:text-[#0A0A0A]";

  return (
    <button
      type="button"
      aria-pressed={following}
      onClick={toggleFollow}
      className={`flex h-10 w-[268px] shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none transition-colors ${stateClasses} ${className}`}
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
