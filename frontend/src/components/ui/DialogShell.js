"use client";

import { createPortal } from "react-dom";
import { useModalDismiss } from "@/hooks/useModalDismiss";

// Zaednichka obvivka za site pop-up prozorci.
export default function DialogShell({
  open,
  label,
  onClose,
  widthClassName = "max-w-md",
  children,
}) {
  useModalDismiss(open, onClose);

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
          className={`relative flex w-full flex-col items-center gap-6 overflow-hidden rounded-xl bg-white p-5 shadow-[0_12px_24px_rgba(0,0,0,0.12)] sm:p-6 md:gap-8 md:p-10 ${widthClassName}`}
        >
          {/* Grayscale go vadi violetovoto od sharata za da ostane siva. */}
          <div
            aria-hidden="true"
            className="pointer-events-none absolute inset-0 bg-[url('/pop-up%20backround.png')] bg-[length:95%] bg-center bg-no-repeat opacity-[0.06] grayscale"
          />

          <button
            type="button"
            aria-label="Затвори"
            onClick={onClose}
            className="absolute right-4 top-4 cursor-pointer p-2 text-[#595959]"
          >
            <CrossIcon />
          </button>

          {/* relative za da stoi nad sharata */}
          <div className="relative flex w-full flex-col items-center gap-6 md:gap-8">
            {children}
          </div>
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
