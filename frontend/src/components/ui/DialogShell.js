"use client";

import Image from "next/image";
import { createPortal } from "react-dom";
import { useModalDismiss } from "@/hooks/useModalDismiss";

// Zaednichka obvivka za site pop-up prozorci.
export default function DialogShell({
  open,
  label,
  onClose,
  widthClassName = "max-w-md",
  fullScreenOnMobile = false,
  children,
}) {
  useModalDismiss(open, onClose);

  // `open` se vklucuva samo po klik, pa document sekogas postoi tuka.
  if (!open || typeof document === "undefined") return null;

  return createPortal(
    // Skrola backdrop-ot, ne kartichkata, za pop-upot da nema svoja lenta.
    // Nad header (50), mobilnoto meni (60) i filter sheet-ot (70).
    <div
      className="fixed inset-0 z-[80] overflow-y-auto bg-black/40 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      onClick={onClose}
    >
      <div
        className={`flex min-h-full justify-center ${
          fullScreenOnMobile ? "items-stretch md:items-center md:p-4" : "items-center p-4"
        }`}
      >
        <div
          role="dialog"
          aria-modal="true"
          aria-label={label}
          onClick={(event) => event.stopPropagation()}
          className={`relative flex w-full flex-col items-center overflow-hidden bg-white shadow-[0_12px_24px_rgba(0,0,0,0.12)] ${
            fullScreenOnMobile
              ? "gap-6 rounded-none px-6 pb-10 md:gap-8 md:rounded-xl md:p-10"
              : "gap-8 rounded-xl p-10"
          } ${widthClassName}`}
        >
          {/* Sharata e vekje pecatena siva vo samiot PNG, bez CSS filter. */}
          <div
            aria-hidden="true"
            className={`pointer-events-none absolute inset-0 bg-[url('/pop-up-background-grey.png')] opacity-[0.08] ${
              fullScreenOnMobile
                ? "bg-[length:100%] bg-top bg-repeat md:bg-[length:95%] md:bg-center md:bg-no-repeat"
                : "bg-[length:95%] bg-center bg-no-repeat"
            }`}
          />

          {/* Samo na telefon dijalogot e cela strana, pa namesto krsce ima „Назад“. */}
          {fullScreenOnMobile ? (
            <button
              type="button"
              onClick={onClose}
              className="relative flex w-full shrink-0 cursor-pointer items-end pb-3 pt-12 md:hidden"
            >
              <span className="flex h-8 items-center gap-2">
                <ChevronLeftIcon />
                <span className="font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-[#595959]">
                  Назад
                </span>
              </span>
            </button>
          ) : null}

          <button
            type="button"
            aria-label="Затвори"
            onClick={onClose}
            className={`absolute right-8 top-4 cursor-pointer p-2 text-[#595959] ${
              fullScreenOnMobile ? "hidden md:block" : ""
            }`}
          >
            <CrossIcon />
          </button>

          {/* relative za da stoi nad sharata */}
          <div className="relative flex w-full flex-col items-center gap-8">
            {children}
          </div>
        </div>
      </div>
    </div>,
    document.body,
  );
}

// Feathericons chevron-down zavrten na levo, isto kako vo dizajnot.
function ChevronLeftIcon() {
  return (
    <Image
      src="/chevron-down.svg"
      alt=""
      width={24}
      height={24}
      className="size-6 shrink-0 rotate-90"
    />
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
