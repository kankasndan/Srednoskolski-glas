"use client";

import { useState } from "react";

export default function FollowThreadButton() {
  const [following, setFollowing] = useState(false);

  const stateClasses = following
    ? "bg-[#A6E4ED] text-[#0A0A0A]"
    : "bg-[#582FF5] text-white hover:bg-[#4B25E0]";

  return (
    <button
      type="button"
      aria-pressed={following}
      onClick={() => setFollowing((current) => !current)}
      className={`flex h-10 w-[268px] shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl text-[14px] font-bold leading-none transition-colors ${stateClasses}`}
    >
      {following && <CheckIcon />}
      {following ? "Следиш" : "Следи ја дискусијата"}
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
