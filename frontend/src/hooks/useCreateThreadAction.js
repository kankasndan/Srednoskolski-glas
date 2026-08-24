"use client";

import { useRef, useState } from "react";
import { createPortal } from "react-dom";
import { useProfile } from "@/hooks/useProfile";
import { banDialogType, banRemainingMessage, getActiveBan } from "@/lib/ban";
import { canCreateThreads } from "@/lib/capabilities";
import { requestSanctionDialog } from "@/lib/sanctionDialog";

export function useCreateThreadAction() {
  const { user, loading } = useProfile();
  const ban = getActiveBan(user);

  function onClick(event) {
    const type = banDialogType(ban);
    if (!type) return;

    event.preventDefault();
    requestSanctionDialog(ban);
  }

  return {
    loading,
    allowed: canCreateThreads(user),
    banned: Boolean(ban),
    remaining: banRemainingMessage(ban),
    onClick,
  };
}

export function BanRemainingTip({ remaining, children, className = "" }) {
  const wrapRef = useRef(null);
  const [coords, setCoords] = useState(null);

  function show() {
    if (!remaining || !wrapRef.current) return;
    const rect = wrapRef.current.getBoundingClientRect();
    setCoords({
      top: rect.top,
      left: rect.left + rect.width / 2,
    });
  }

  function hide() {
    setCoords(null);
  }

  if (!remaining) return children;

  return (
    <div
      ref={wrapRef}
      className={`relative ${className}`}
      onMouseEnter={show}
      onMouseLeave={hide}
      onFocus={show}
      onBlur={hide}
    >
      {children}
      {remaining && coords && typeof document !== "undefined"
        ? createPortal(
            <div
              role="tooltip"
              style={{
                top: coords.top,
                left: coords.left,
              }}
              className="pointer-events-none fixed z-[90] -translate-x-1/2 -translate-y-[calc(100%+10px)] w-max max-w-[260px] rounded-lg bg-[#0A0A0A] px-3 py-2 text-center font-[family-name:var(--font-manrope)] text-[12px] leading-snug text-white shadow-lg"
            >
              {remaining}
              <div className="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#0A0A0A]" />
            </div>,
            document.body,
          )
        : null}
    </div>
  );
}
