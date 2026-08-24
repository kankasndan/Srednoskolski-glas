"use client";

import { useState } from "react";
import DialogShell from "@/components/ui/DialogShell";
import FieldLabel from "@/components/ui/FieldLabel";
import PrimaryButton from "@/components/ui/PrimaryButton";
import { userFacingError } from "@/lib/api";

const MAX_LENGTH = 2000;

export default function SanctionAppealDialog({
  open,
  canAppeal = true,
  pending = false,
  onSubmit,
  onClose,
}) {
  const [explanation, setExplanation] = useState("");
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [sent, setSent] = useState(false);

  async function handleSubmit(event) {
    event.preventDefault();
    if (submitting || pending || sent || !canAppeal) return;

    const text = explanation.trim();
    if (!text) {
      setError("Напиши зошто поднесуваш жалба.");
      return;
    }

    setError("");
    setSubmitting(true);

    try {
      await onSubmit?.(text);
      setSent(true);
      setExplanation("");
    } catch (err) {
      setError(userFacingError(err, "Неуспешно испраќање. Обиди се повторно."));
    } finally {
      setSubmitting(false);
    }
  }

  function handleClose() {
    if (submitting) return;
    setError("");
    setExplanation("");
    setSent(false);
    onClose?.();
  }

  return (
    <DialogShell
      open={open}
      label="Поднеси жалба"
      onClose={handleClose}
      widthClassName="max-w-[400px]"
      autoHeight
    >
      <form onSubmit={handleSubmit} noValidate className="flex w-full flex-col items-center gap-6">
        <div className="flex w-full flex-col items-center gap-3 text-center">
          <p className="max-w-[288px] font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-snug text-[var(--color-primary-200)]">
            Поднеси жалба
          </p>
          <p className="max-w-[324px] font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-snug text-black">
            {sent || pending
              ? "Жалбата е поднесена и чека одлука од модераторскиот тим."
              : canAppeal
                ? "Опиши зошто сметаш дека санкцијата е несправна. Жалбата ќе ја разгледа модераторскиот тим."
                : "Веќе имаш поднесено жалба за оваа санкција."}
          </p>
        </div>

        {sent || pending || !canAppeal ? null : (
          <div className="flex w-full flex-col gap-3 text-left">
            <FieldLabel htmlFor="appeal-explanation" required className="mb-0">
              Образложение
            </FieldLabel>
            <div className="h-[120px] overflow-hidden rounded-xl border border-[#CCCCCC] bg-white">
              <textarea
                id="appeal-explanation"
                value={explanation}
                maxLength={MAX_LENGTH}
                disabled={submitting}
                onChange={(event) => {
                  setExplanation(event.target.value);
                  if (error) setError("");
                }}
                placeholder="Напиши ја жалбата..."
                className="h-full w-full resize-none p-4 font-[family-name:var(--font-manrope)] text-[14px] leading-6 text-black outline-none placeholder:text-[#595959] disabled:opacity-60"
              />
            </div>
            {error ? (
              <p className="font-[family-name:var(--font-manrope)] text-[11px] leading-4 text-[var(--color-error)]">
                {error}
              </p>
            ) : null}
            <PrimaryButton
              type="submit"
              disabled={submitting}
              className="h-10 self-end px-6 font-[family-name:var(--font-manrope)] text-[14px] leading-none disabled:opacity-60"
            >
              {submitting ? "Се испраќа…" : "Испрати жалба"}
            </PrimaryButton>
          </div>
        )}
      </form>
    </DialogShell>
  );
}
