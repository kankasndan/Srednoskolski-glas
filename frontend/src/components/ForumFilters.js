"use client";

import Image from "next/image";
import { useId, useState } from "react";

const SORT_OPTIONS = [{ value: "trending", label: "Трендинг" }];

const TIME_FILTER_OPTIONS = [
  { value: "week", label: "Оваа недела" },
  { value: "month", label: "Овој месец" },
  { value: "six-months", label: "6 месеци" },
  { value: "year", label: "1 година" },
];

function ForumSelect({
  name,
  label,
  options,
  selected,
  isOpen,
  listboxId,
  textWidthClassName,
  onToggle,
  onSelect,
}) {
  return (
    <div className="relative h-10 w-36 shrink-0">
      <input type="hidden" name={name} value={selected.value} />

      <button
        type="button"
        aria-haspopup="listbox"
        aria-expanded={isOpen}
        aria-controls={listboxId}
        onClick={onToggle}
        className="flex h-10 w-36 cursor-pointer items-center justify-center gap-2 rounded-[12px] bg-white py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black"
      >
        <span className={`flex h-[19px] items-center ${textWidthClassName}`}>
          {selected.label}
        </span>
        <Image
          src="/chevron-down.svg"
          alt=""
          width={16}
          height={16}
          className={`size-4 shrink-0 transition-transform ${isOpen ? "rotate-180" : ""}`}
        />
      </button>

      {isOpen && (
        <div
          id={listboxId}
          role="listbox"
          aria-label={label}
          className="absolute left-0 top-12 z-20 flex w-36 flex-col overflow-hidden rounded-[12px] bg-white py-2 shadow-[0_12px_24px_rgba(88,47,245,0.14)]"
        >
          {options.map((option) => (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={selected.value === option.value}
              onClick={() => onSelect(option)}
              className="flex h-10 w-full items-center px-4 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#E5E5E5]"
            >
              {option.label}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

export default function ForumFilters() {
  const sortListboxId = useId();
  const timeListboxId = useId();
  const [openSelect, setOpenSelect] = useState(null);
  const [selectedSort, setSelectedSort] = useState(SORT_OPTIONS[0]);
  const [selectedTimeFilter, setSelectedTimeFilter] = useState(TIME_FILTER_OPTIONS[0]);

  return (
    <div className="flex h-10 w-[288px] self-end">
      <ForumSelect
        name="sort"
        label="Сортирај дискусии"
        options={SORT_OPTIONS}
        selected={selectedSort}
        isOpen={openSelect === "sort"}
        listboxId={sortListboxId}
        textWidthClassName="w-[67px]"
        onToggle={() => setOpenSelect((current) => (current === "sort" ? null : "sort"))}
        onSelect={(option) => {
          setSelectedSort(option);
          setOpenSelect(null);
        }}
      />
      <ForumSelect
        name="timeFilter"
        label="Филтрирај по време"
        options={TIME_FILTER_OPTIONS}
        selected={selectedTimeFilter}
        isOpen={openSelect === "time"}
        listboxId={timeListboxId}
        textWidthClassName="w-[89px]"
        onToggle={() => setOpenSelect((current) => (current === "time" ? null : "time"))}
        onSelect={(option) => {
          setSelectedTimeFilter(option);
          setOpenSelect(null);
        }}
      />
    </div>
  );
}
