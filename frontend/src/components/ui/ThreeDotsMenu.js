"use client";

import { useEffect, useId, useRef, useState } from "react";

// items: [{ label, onSelect }]
export default function ThreeDotsMenu({ items }) {
  const [open, setOpen] = useState(false);
  const menuId = useId();
  const wrapperRef = useRef(null);

  // Zatvori na klik nadvor ili na Escape.
  useEffect(() => {
    if (!open) return;

    const handlePointerDown = (event) => {
      if (!wrapperRef.current?.contains(event.target)) setOpen(false);
    };
    const handleKeyDown = (event) => {
      if (event.key === "Escape") setOpen(false);
    };

    document.addEventListener("mousedown", handlePointerDown);
    document.addEventListener("keydown", handleKeyDown);
    return () => {
      document.removeEventListener("mousedown", handlePointerDown);
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [open]);

  return (
    <div ref={wrapperRef} className="relative">
      <button
        type="button"
        aria-label="Повеќе опции"
        aria-haspopup="menu"
        aria-expanded={open}
        aria-controls={menuId}
        onClick={() => setOpen((current) => !current)}
        className={`grid size-9 cursor-pointer place-items-center rounded-lg transition-colors hover:bg-[#E5E5E5] ${
          open ? "text-[var(--color-primary-200)]" : "text-[#333333]"
        }`}
      >
        <MoreIcon />
      </button>

      {open && (
        <div
          id={menuId}
          role="menu"
          className="absolute right-0 top-full z-20 mt-1 flex flex-col"
        >
          {items.map((item, index) => (
            <button
              key={item.label}
              type="button"
              role="menuitem"
              onClick={() => {
                setOpen(false);
                item.onSelect();
              }}
              className={`flex w-full cursor-pointer items-center justify-center whitespace-nowrap border border-[#CCCCCC] bg-white px-4 py-3 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black transition-colors hover:bg-[#E5E5E5] ${cornerClassName(
                index,
                items.length,
              )}`}
            >
              {item.label}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

function cornerClassName(index, total) {
  if (total === 1) return "rounded-[6px]";
  if (index === 0) return "rounded-t-[6px]";
  if (index === total - 1) return "-mt-px rounded-b-[6px]";
  return "-mt-px";
}


function MoreIcon() {
  return (
    <svg
      width="20"
      height="20"
      viewBox="0 0 20 20"
      fill="none"
      aria-hidden="true"
      className="size-5"
    >
      <path
        d="M10.0007 2.5C9.08398 2.5 8.33398 3.25 8.33398 4.16667C8.33398 5.08333 9.08398 5.83333 10.0007 5.83333C10.9173 5.83333 11.6673 5.08333 11.6673 4.16667C11.6673 3.25 10.9173 2.5 10.0007 2.5ZM10.0007 14.1667C9.08398 14.1667 8.33398 14.9167 8.33398 15.8333C8.33398 16.75 9.08398 17.5 10.0007 17.5C10.9173 17.5 11.6673 16.75 11.6673 15.8333C11.6673 14.9167 10.9173 14.1667 10.0007 14.1667ZM10.0007 8.33333C9.08398 8.33333 8.33398 9.08333 8.33398 10C8.33398 10.9167 9.08398 11.6667 10.0007 11.6667C10.9173 11.6667 11.6673 10.9167 11.6673 10C11.6673 9.08333 10.9173 8.33333 10.0007 8.33333Z"
        fill="currentColor"
      />
    </svg>
  );
}
