"use client";

import { useEffect, useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faPlus, faXmark } from "@fortawesome/free-solid-svg-icons";
import SelectField from "@/components/ui/SelectField";
import { compactFieldClass } from "@/lib/fieldStyles";

config.autoAddCss = false;

const MIN_OPTIONS = 2;
const MAX_OPTIONS = 4;

const DURATION_OPTIONS = [
  { value: 1, label: "1 ден" },
  { value: 3, label: "3 дена" },
  { value: 7, label: "1 недела" },
  { value: 14, label: "2 недели" },
  { value: 30, label: "1 месец" },
];

const inputClass = `${compactFieldClass} min-w-0 text-[#000000] placeholder:text-[#595959]`;

function seedOptions(initialPoll) {
  const fromPoll = (initialPoll?.options ?? [])
    .slice()
    .sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
    .map((option) => ({
      id: option.id ?? null,
      label: option.label ?? "",
    }));

  if (fromPoll.length >= MIN_OPTIONS) return fromPoll;

  const padded = [...fromPoll];
  while (padded.length < MIN_OPTIONS) {
    padded.push({ id: null, label: "" });
  }
  return padded;
}

export function durationDaysFromEndsAt(endsAt) {
  if (!endsAt) return 3;

  const ms = new Date(endsAt).getTime() - Date.now();
  const days = Math.max(1, Math.min(30, Math.ceil(ms / (1000 * 60 * 60 * 24))));
  const allowed = DURATION_OPTIONS.map((option) => option.value);

  return allowed.reduce((best, current) =>
    Math.abs(current - days) < Math.abs(best - days) ? current : best,
  );
}

export default function PollAttachment({ onClose, onChange, initialPoll = null }) {
  const [title, setTitle] = useState(initialPoll?.question ?? "");
  const [options, setOptions] = useState(() => seedOptions(initialPoll));
  const [durationDays, setDurationDays] = useState(() =>
    durationDaysFromEndsAt(initialPoll?.ends_at),
  );

  useEffect(() => {
    const trimmedOptions = options
      .map((option) => ({
        id: option.id ?? null,
        label: option.label.trim(),
      }))
      .filter((option) => option.label);

    onChange?.({
      question: title.trim(),
      options: trimmedOptions.map((option) => option.label),
      option_ids: trimmedOptions.map((option) => option.id),
      duration_days: durationDays,
    });
  }, [title, options, durationDays, onChange]);

  function updateOption(index, value) {
    setOptions((prev) =>
      prev.map((option, i) => (i === index ? { ...option, label: value } : option)),
    );
  }

  function addOption() {
    setOptions((prev) =>
      prev.length < MAX_OPTIONS ? [...prev, { id: null, label: "" }] : prev,
    );
  }

  function removeOption(index) {
    setOptions((prev) => prev.filter((_, i) => i !== index));
  }

  // Traenjeto se prikažuva kako tekst, pa go mapirame nazad kon denovi.
  const selectedDuration = DURATION_OPTIONS.find(
    (option) => option.value === durationDays,
  );

  function selectDuration(label) {
    const match = DURATION_OPTIONS.find((option) => option.label === label);
    if (match) setDurationDays(match.value);
  }

  return (
    <div className="flex max-w-full min-w-0 flex-col gap-5 rounded-2xl border border-[#CCCCCC] bg-white p-4">
      <div className="flex min-w-0 items-center justify-between gap-3">
        <span className="min-w-0 truncate font-[family-name:var(--font-manrope)] text-[14px] font-bold text-black">
          Анкета
        </span>
        <button
          type="button"
          aria-label="Отстрани ја анкетата"
          onClick={onClose}
          className="flex size-6 cursor-pointer items-center justify-center text-[#595959] transition-colors hover:text-black"
        >
          <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
        </button>
      </div>

      <input
        type="text"
        value={title}
        onChange={(event) => setTitle(event.target.value)}
        placeholder="Прашање за анкетата"
        className={inputClass}
      />

      <SelectField
        compact
        id="poll-duration"
        label="Колку долго ќе трае анкетата?"
        value={selectedDuration?.label ?? ""}
        onChange={selectDuration}
        options={DURATION_OPTIONS.map((option) => option.label)}
      />

      <div className="flex flex-col gap-3 border-t border-[#CCCCCC] pt-5">
        {options.map((option, index) => (
          <div key={option.id ?? `new-${index}`} className="flex min-w-0 items-center gap-2">
            <input
              type="text"
              value={option.label}
              onChange={(event) => updateOption(index, event.target.value)}
              placeholder={`Опција ${index + 1}`}
              className={inputClass}
            />
            {index >= MIN_OPTIONS && (
              <button
                type="button"
                aria-label={`Отстрани ја опцијата ${index + 1}`}
                onClick={() => removeOption(index)}
                className="flex size-6 shrink-0 cursor-pointer items-center justify-center text-[#595959] transition-colors hover:text-black"
              >
                <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
              </button>
            )}
          </div>
        ))}
      </div>

      {options.length < MAX_OPTIONS && (
        <button
          type="button"
          onClick={addOption}
          className="flex h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-[#CCCCCC] font-[family-name:var(--font-manrope)] text-[14px] text-[#595959] transition-colors hover:bg-[#DCEBED] hover:text-black md:h-12"
        >
          <FontAwesomeIcon icon={faPlus} className="h-4 w-4" />
          Додади опција
        </button>
      )}
    </div>
  );
}
