"use client";

import { useEffect, useRef, useState } from "react";
import { labelClass, fieldClass } from "@/components/fieldStyles";

const rowClass =
  "flex h-10 w-full items-center justify-between gap-3 px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-normal leading-none transition-colors duration-300 ease-out hover:bg-[#E5E5E5]";

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

function Tooltip({ text }) {
  return (
    <div className="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 w-max max-w-60 -translate-x-1/2 rounded-lg bg-[#0A0A0A] px-3 py-2 text-center font-(family-name:--font-manrope) text-[12px] leading-snug text-white opacity-0 transition-opacity group-hover:opacity-100">
      {text}
      <div className="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#0A0A0A]" />
    </div>
  );
}

function CityGroup({ city, schools, value, onSelect }) {
  return (
    <details name="school-city" className="group/city shrink-0">
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
              onClick={() => onSelect(school, city)}
              className={`${rowClass} text-black ${
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

export default function SchoolSelect({
  id,
  label,
  required = false,
  value,
  onChange,
  placeholder,
  groups,
  notStudent,
  onNotStudentChange,
  disabled = false,
  tooltip,
}) {
  const [open, setOpen] = useState(false);
  const wrapperRef = useRef(null);
  const selectedSchool = value ? value.split("|")[0] : "";

  useEffect(() => {
    function handleClickOutside(event) {
      if (!wrapperRef.current.contains(event.target)) setOpen(false);
    }

    function handleEscape(event) {
      if (event.key === "Escape") setOpen(false);
    }

    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("keydown", handleEscape);

    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
      document.removeEventListener("keydown", handleEscape);
    };
  }, []);

  function selectSchool(school, city) {
    onChange(`${school}|${city}`);
    setOpen(false);
  }

  function toggleNotStudent() {
    onNotStudentChange(!notStudent);
    setOpen(false);
  }

  let triggerTone = "bg-white text-[#595959]";
  if (disabled) triggerTone = "cursor-not-allowed bg-[#F5F5F5] text-[#B3B3B3]";
  else if (selectedSchool) triggerTone = "bg-white text-black";

  return (
    <div className="flex flex-col gap-2">
      <span className={labelClass}>
        {required && <span className="text-red-500">*</span>}
        {label}
      </span>

      <div ref={wrapperRef} className="group relative h-14">
        {tooltip && <Tooltip text={tooltip} />}

        <input
          type="text"
          name={id}
          value={value}
          onChange={() => {}}
          required={required}
          tabIndex={-1}
          aria-hidden="true"
          className="absolute h-0 w-0 opacity-0"
        />

        <details
          open={open}
          onToggle={(e) => setOpen(e.currentTarget.open)}
          className="group/field absolute inset-x-0 top-0 z-10"
        >
          <summary
            id={id}
            aria-expanded={open}
            onClick={(e) => disabled && e.preventDefault()}
            className={`${fieldClass} ${summaryClass} ${triggerTone} flex items-center justify-between gap-3 transition-colors duration-300 ease-out group-open/field:rounded-b-none group-open/field:bg-[#CFE9ED]`}
          >
            <span className="truncate">{selectedSchool || placeholder}</span>
            <Chevron className="group-open/field:rotate-180" />
          </summary>

          <div className="flex max-h-70 flex-col divide-y divide-[#CCCCCC] overflow-y-auto rounded-b-2xl border-x border-b border-[#CCCCCC] bg-white">
            <button
              type="button"
              onClick={toggleNotStudent}
              className={`${rowClass} shrink-0 text-[#595959] ${
                notStudent ? "bg-[#E5E5E5]" : ""
              }`}
            >
              Не сум средношколец
            </button>

            {groups.map(({ city, schools }) => (
              <CityGroup
                key={city}
                city={city}
                schools={schools}
                value={value}
                onSelect={selectSchool}
              />
            ))}
          </div>
        </details>
      </div>
    </div>
  );
}
