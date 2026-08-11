"use client";

import { useState } from "react";
import DialogShell from "@/components/ui/DialogShell";
import FieldLabel from "@/components/ui/FieldLabel";

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
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(event) {
    event.preventDefault();

    if (submitting) return;

    if (!reason) {
      setError("Избери причина за пријавата.");
      return;
    }

    if (reason === "Друго" && !details.trim()) {
      setError("За „Друго“ внеси дополнителни детали.");
      return;
    }

    setError("");
    setSubmitting(true);

    try {
      await onSubmit?.({ reason, details: details.trim() });
    } catch (err) {
      setError(err?.message || "Неуспешна пријава. Обиди се повторно.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <DialogShell
      open={open}
      label="Пријави"
      onClose={submitting ? undefined : onClose}
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
                disabled={submitting}
                onChange={() => {
                  setReason(option);
                  if (option !== "Друго") setDetails("");
                  setError("");
                }}
              />
            ))}
          </div>
          <p
            className={`absolute left-0 top-full mt-1 font-[family-name:var(--font-manrope)] text-[11px] leading-4 text-[var(--color-error)] ${
              error ? "" : "invisible"
            }`}
          >
            {error || "Нема грешка"}
          </p>
        </div>

        {reason === "Друго" ? (
          <div className="overflow-hidden rounded-xl border border-[#CCCCCC] bg-white">
            <textarea
              rows={8}
              value={details}
              disabled={submitting}
              onChange={(event) => {
                setDetails(event.target.value);
                if (error) setError("");
              }}
              placeholder="Внеси дополнителни детали..."
              aria-label="Дополнителни детали"
              className="w-full resize-none p-4 font-[family-name:var(--font-manrope)] text-[14px] leading-6 text-black outline-none placeholder:text-[#595959] disabled:opacity-60"
            />
          </div>
        ) : null}

        <div className="flex items-center justify-end">
          <button
            type="submit"
            disabled={submitting}
            className="cursor-pointer rounded-xl bg-[var(--color-primary-200)] px-6 py-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:bg-[#4B25E0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-200)] disabled:cursor-not-allowed disabled:opacity-60"
          >
            {submitting ? "Се пријавува…" : "Пријави"}
          </button>
        </div>
      </form>
    </DialogShell>
  );
}

function ReasonRadio({ label, checked, disabled, onChange }) {
  return (
    <label className={`flex items-center gap-2 ${disabled ? "cursor-not-allowed opacity-60" : "cursor-pointer"}`}>
      <input
        type="radio"
        name="reason"
        value={label}
        checked={checked}
        disabled={disabled}
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
