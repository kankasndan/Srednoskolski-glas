"use client";

import { useCallback, useRef, useState } from "react";
import { labelClass, fieldClass } from "@/lib/fieldStyles";
import { useClickOutside } from "@/hooks/useClickOutside";

const rowClass =
  "flex h-10 w-full cursor-pointer items-center justify-between gap-3 px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-normal leading-none transition-colors duration-300 ease-out hover:bg-[#E5E5E5]";

const summaryClass = "cursor-pointer list-none [&::-webkit-details-marker]:hidden";

function Chevron({ className = "" }) {
  return (
    <img
      src="/chevron-down.svg"
      alt=""
      aria-hidden="true"
      className={`size-4 shrink-0 transition-transform duration-300 ease-out ${className}`}
    />
  );
}

function CityGroup({ groupName, city, schools, value, onSelect }) {
  return (
    <details name={groupName} className="group/city shrink-0">
      <summary
        className={`${rowClass} ${summaryClass} text-black group-open/city:bg-[#CFE9ED] group-open/city:hover:bg-[#CFE9ED]`}
      >
        <span className="truncate">{city}</span>
        <Chevron className="group-open/city:rotate-180" />
      </summary>

      <ul className="divide-y divide-[#CCCCCC] border-t border-[#CCCCCC]">
        {schools.map((school) => (
          <li key={school}>
            <button
              type="button"
              onClick={() => onSelect(`${school}|${city}`)}
              className={`${rowClass} cursor-pointer text-black ${
                value === `${school}|${city}` ? "bg-[#E5E5E5]" : ""
              }`}
            >
              <span className="truncate">{school}</span>
            </button>
          </li>
        ))}
      </ul>
    </details>
  );
}

export default function SelectField({
  id,
  label,
  required = false,
  value,
  onChange,
  placeholder,
  options,
  groups,
  disabled = false,
}) {
  const [open, setOpen] = useState(false);
  const wrapperRef = useRef(null);

  useClickOutside(wrapperRef, useCallback(() => setOpen(false), []), open);

  function select(nextValue) {
    onChange(nextValue);
    setOpen(false);
  }

  // Grupiranite vrednosti se čuvaat kako "učilište|grad".
  const selectedLabel = groups && value ? value.split("|")[0] : value;

  let triggerTone = "bg-white text-[#595959]";
  if (disabled) triggerTone = "cursor-not-allowed bg-[#F5F5F5] text-[#B3B3B3]";
  else if (value) triggerTone = "bg-white text-black";

  return (
    <div className="flex flex-col gap-2">
      <span className={labelClass}>
        {required && <span className="text-red-500">*</span>}
        {label}
      </span>

      {/* Otvorenata lista mora da e nad polinjata pod nea. */}
      <div
        ref={wrapperRef}
        className={`group relative h-14 ${open ? "z-30" : ""}`}
      >
        <input
          type="text"
          name={id}
          value={value}
          onChange={() => {}}
          required={required && !disabled}
          tabIndex={-1}
          aria-hidden="true"
          className="absolute h-0 w-0 opacity-0"
        />

        <details
          open={open}
          onToggle={(event) => setOpen(event.currentTarget.open)}
          className="group/field absolute inset-x-0 top-0 z-10"
        >
          <summary
            id={id}
            aria-expanded={open}
            onClick={(event) => disabled && event.preventDefault()}
            className={`${fieldClass} ${summaryClass} ${triggerTone} flex items-center justify-between gap-3 transition-colors duration-300 ease-out group-open/field:rounded-b-none group-open/field:bg-[#CFE9ED]`}
          >
            <span className="truncate">{selectedLabel || placeholder}</span>
            <Chevron className="group-open/field:rotate-180" />
          </summary>

          <div className="flex max-h-70 flex-col divide-y divide-[#CCCCCC] overflow-y-auto rounded-b-2xl border-x border-b border-[#CCCCCC] bg-white">
            {groups
              ? groups.map(({ city, schools }) => (
                  <CityGroup
                    key={city}
                    groupName={`${id}-group`}
                    city={city}
                    schools={schools}
                    value={value}
                    onSelect={select}
                  />
                ))
              : options?.map((name) => (
                  <button
                    key={name}
                    type="button"
                    onClick={() => select(name)}
                    className={`${rowClass} shrink-0 cursor-pointer text-black ${
                      value === name ? "bg-[#CFE9ED] hover:bg-[#CFE9ED]" : ""
                    }`}
                  >
                    <span className="truncate">{name}</span>
                  </button>
                ))}
          </div>
        </details>
      </div>
    </div>
  );
}
