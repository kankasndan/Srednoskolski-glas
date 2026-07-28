"use client";

import { useState } from "react";
import DialogShell from "@/components/DialogShell";
import FieldLabel from "@/components/FieldLabel";

const REASONS = [
  "Спам",
  "Навредлива содржина",
  "Дезинформација",
  "Несоодветна содржина",
  "Друго",
];

export default function ReportDialog({ open, onClose, onSubmit }) {
  const [reason, setReason] = useState("");
  const [details, setDetails] = useState("");
  const [error, setError] = useState("");

  function handleSubmit(event) {
    event.preventDefault();

    if (!reason) {
      setError("Избери причина за пријавата.");
      return;
    }

    onSubmit?.({ reason, details });
  }

  return (
    <DialogShell
      open={open}
      label="Пријави ја дискусијата"
      onClose={onClose}
      widthClassName="max-w-4xl"
    >
      <form onSubmit={handleSubmit} noValidate className="flex w-full flex-col gap-8">
        <div className="relative flex flex-col gap-4">
          <FieldLabel required>Избери причина</FieldLabel>
          <div className="flex flex-wrap items-center gap-6">
            {REASONS.map((option) => (
              <ReasonRadio
                key={option}
                label={option}
                checked={reason === option}
                onChange={() => {
                  setReason(option);
                  setError("");
                }}
              />
            ))}
          </div>
          {/* Apsolutna za da ne go razmakne rastojanieto do poleto so detali. */}
          <p
            className={`absolute left-0 top-full mt-1 font-[family-name:var(--font-manrope)] text-[11px] leading-4 text-[var(--color-error)] ${
              error ? "" : "invisible"
            }`}
          >
            {error || "Нема грешка"}
          </p>
        </div>

        <div className="overflow-hidden rounded-xl border border-[#CCCCCC] bg-white">
          <textarea
            rows={8}
            value={details}
            onChange={(event) => setDetails(event.target.value)}
            placeholder="Внеси дополнителни детали доколку сакаш..."
            aria-label="Дополнителни детали"
            className="w-full resize-none p-4 font-[family-name:var(--font-manrope)] text-[14px] leading-6 text-black outline-none placeholder:text-[#595959]"
          />
          <div className="flex items-center justify-end border-t border-[#CCCCCC] p-4">
            <button
              type="submit"
              className="cursor-pointer rounded-xl bg-[var(--color-primary-200)] px-6 py-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:bg-[#4B25E0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-200)]"
            >
              Пријави
            </button>
          </div>
        </div>
      </form>
    </DialogShell>
  );
}

// Nativniot input e skrien za da ostanat grupata i strelkite od tastaturata.
function ReasonRadio({ label, checked, onChange }) {
  return (
    <label className="flex cursor-pointer items-center gap-2">
      <input
        type="radio"
        name="reason"
        value={label}
        checked={checked}
        onChange={onChange}
        className="peer sr-only"
      />
      <span
        className={`grid size-4 shrink-0 place-items-center rounded-full border transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[var(--color-primary-200)] ${
          checked ? "border-[var(--color-primary-200)]" : "border-[#595959]"
        }`}
      >
        {checked && <span className="size-2 rounded-full bg-[var(--color-primary-200)]" />}
      </span>
      <span className="font-[family-name:var(--font-manrope)] text-[14px] text-black">
        {label}
      </span>
    </label>
  );
}
