"use client";

import { useModalDismiss } from "@/hooks/useModalDismiss";

function FilterGroup({ title, options, selected, onSelect }) {
  return (
    <div className="flex flex-col gap-4">
      <p className="text-center font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-none text-black">
        {title}
      </p>

      <div className="grid grid-cols-2 gap-4">
        {options.map((option) => (
          <button
            key={option.value}
            type="button"
            onClick={() => onSelect(option)}
            className={`flex h-10 cursor-pointer items-center justify-center rounded-xl p-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black transition-colors ${
              selected.value === option.value ? "bg-[#CFE9ED]" : "bg-[#E5E5E5]"
            }`}
          >
            {option.label}
          </button>
        ))}
      </div>
    </div>
  );
}

export default function FeedFilterSheet({
  open,
  onClose,
  sortOptions,
  selectedSort,
  onSelectSort,
  timeOptions,
  selectedTime,
  onSelectTime,
}) {
  useModalDismiss(open, onClose);

  return (
    <div
      aria-hidden={!open}
      className={`fixed inset-0 z-[70] lg:hidden ${open ? "" : "pointer-events-none"}`}
    >
      <div
        onClick={onClose}
        className={`absolute inset-0 bg-black/40 transition-opacity duration-300 ${
          open ? "opacity-100" : "opacity-0"
        }`}
      />

      <div
        role="dialog"
        aria-modal="true"
        aria-label="Филтри"
        className={`absolute inset-x-0 bottom-0 mx-auto flex flex-col gap-8 rounded-t-[40px] bg-white px-10 pb-10 pt-11 transition-transform duration-300 ease-out md:max-w-[600px] ${
          open ? "translate-y-0" : "translate-y-full"
        }`}
      >
        <FilterGroup
          title="Филтрирај според"
          options={sortOptions}
          selected={selectedSort}
          onSelect={onSelectSort}
        />
        <FilterGroup
          title="Временски период"
          options={timeOptions}
          selected={selectedTime}
          onSelect={onSelectTime}
        />
      </div>
    </div>
  );
}
