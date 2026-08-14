"use client";

import { useState } from "react";
import DialogShell from "@/components/ui/DialogShell";
import FieldLabel from "@/components/ui/FieldLabel";
import PrimaryButton from "@/components/ui/PrimaryButton";

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
      widthClassName="max-w-[857px]"
      fullScreenOnMobile
    >
      <form onSubmit={handleSubmit} noValidate className="flex w-full flex-col gap-8">
        <div className="relative flex flex-col gap-4">
          <FieldLabel required className="mb-0">
            Избери причина
          </FieldLabel>
          {/* Na telefon pricinite se edna pod druga, od tablet nagore vo red. */}
          <div className="flex flex-col items-start gap-4 md:flex-row md:flex-wrap md:items-center md:gap-6">
            {REASONS.map((option) => (
              <ReasonRadio
                key={option}
                label={option}
                checked={reason === option}
                disabled={submitting}
                onChange={() => {
                  setReason(option);
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

        <div className="flex w-full flex-col gap-6 md:gap-3">
          <div className="h-[88px] overflow-hidden rounded-xl border border-[#CCCCCC] bg-white">
            <textarea
              value={details}
              disabled={submitting}
              onChange={(event) => {
                setDetails(event.target.value);
                if (error) setError("");
              }}
              placeholder={
                reason === "Друго"
                  ? "Внеси дополнителни детали..."
                  : "Внеси дополнителни детали доколку сакаш..."
              }
              aria-label="Дополнителни детали"
              className="h-full w-full resize-none p-4 font-[family-name:var(--font-manrope)] text-[14px] leading-6 text-black outline-none placeholder:text-[#595959] disabled:opacity-60"
            />
          </div>

          <div className="flex items-center justify-end">
            <PrimaryButton
              type="submit"
              disabled={submitting}
              className="h-10 w-36 font-[family-name:var(--font-manrope)] text-[14px] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-200)] disabled:opacity-60"
            >
              {submitting ? "Се пријавува…" : "Пријави"}
            </PrimaryButton>
          </div>
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
          checked ? "border-[var(--color-primary-200)]" : "border-[#CCCCCC]"
        }`}
      >
        {checked && <span className="size-2 rounded-full bg-[var(--color-primary-200)]" />}
      </span>
      <span className="font-[family-name:var(--font-manrope)] text-[16px] leading-none text-[#595959] md:text-[14px]">
        {label}
      </span>
    </label>
  );
}
