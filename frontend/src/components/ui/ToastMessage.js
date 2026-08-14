"use client";

export default function ToastMessage({ message, tone = "success", onClose }) {
  if (!message) return null;

  const toneClassName =
    tone === "error"
      ? "border-[#FCA5A5] bg-[#FEF2F2] text-[#991B1B]"
      : "border-[#CFE9ED] bg-white text-black";

  return (
    <div className="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4">
      <div
        role="status"
        className={`pointer-events-auto flex min-h-10 w-full max-w-[390px] items-center justify-between gap-3 rounded-xl border px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] shadow-[0_12px_24px_rgba(0,0,0,0.12)] ${toneClassName}`}
      >
        <span>{message}</span>
        <button
          type="button"
          aria-label="Затвори"
          onClick={onClose}
          className="cursor-pointer text-[18px] leading-none text-current opacity-60 transition-opacity hover:opacity-100"
        >
          ×
        </button>
      </div>
    </div>
  );
}
