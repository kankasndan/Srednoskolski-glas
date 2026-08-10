"use client";

import { useEffect } from "react";
import { createPortal } from "react-dom";

// Zaednichka obvivka za site pop-up prozorci.
export default function DialogShell({
  open,
  label,
  onClose,
  widthClassName = "max-w-md",
  children,
}) {
  useEffect(() => {
    if (!open) return;

    const handleKeyDown = (event) => {
      if (event.key === "Escape") onClose?.();
    };

    document.addEventListener("keydown", handleKeyDown);
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    return () => {
      document.removeEventListener("keydown", handleKeyDown);
      document.body.style.overflow = previousOverflow;
    };
  }, [open, onClose]);

  // `open` se vklucuva samo po klik, pa document sekogas postoi tuka.
  if (!open || typeof document === "undefined") return null;

  return createPortal(
    // Skrola backdrop-ot, ne kartichkata, za pop-upot da nema svoja lenta.
    <div
      className="fixed inset-0 z-50 overflow-y-auto bg-black/40 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      onClick={onClose}
    >
      <div className="flex min-h-full items-center justify-center p-4">
        <div
          role="dialog"
          aria-modal="true"
          aria-label={label}
          onClick={(event) => event.stopPropagation()}
          className={`relative flex w-full flex-col items-center gap-6 rounded-xl bg-white p-6 shadow-[0_12px_24px_rgba(88,47,245,0.14)] ${widthClassName}`}
        >
          <button
            type="button"
            aria-label="Затвори"
            onClick={onClose}
            className="absolute right-4 top-4 cursor-pointer p-2 text-[#595959]"
          >
            <CrossIcon />
          </button>

          {children}
        </div>
      </div>
    </div>,
    document.body,
  );
}

function CrossIcon() {
  return (
    <svg
      width="16"
      height="16"
      viewBox="0 0 16 16"
      fill="none"
      aria-hidden="true"
      className="size-4"
    >
      <path
        d="M13.3333 13.3333L2.66667 2.66667M13.3333 2.66667L2.66667 13.3333"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
      />
    </svg>
  );
}
