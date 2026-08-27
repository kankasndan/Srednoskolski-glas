"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faStar as faStarFilled } from "@fortawesome/free-solid-svg-icons";
import { faStar as faStarEmpty } from "@fortawesome/free-regular-svg-icons";
import { useId, useState } from "react";
import DialogShell from "@/components/ui/DialogShell";
import FieldLabel from "@/components/ui/FieldLabel";
import PrimaryButton from "@/components/ui/PrimaryButton";
import { userFacingError } from "@/lib/api";

config.autoAddCss = false;

const RATINGS = [1, 2, 3, 4, 5];

export default function FeedbackDialog({ open, onClose, onSubmit }) {
  const [rating, setRating] = useState(0);
  const [hovered, setHovered] = useState(0);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const messageId = useId();

  async function handleSubmit(event) {
    event.preventDefault();

    if (submitting) return;

    if (!rating) {
      setError("Оцени ја страната од 1 до 5.");
      return;
    }

    setError("");
    setSubmitting(true);

    try {
      await onSubmit?.({ rating, message: message.trim() });
    } catch (err) {
      setError(userFacingError(err, "Неуспешно праќање. Обиди се повторно."));
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <DialogShell
      open={open}
      label="Оцени ја страната"
      onClose={submitting ? undefined : onClose}
      widthClassName="max-w-[560px]"
      fullScreenOnMobile
    >
      <form onSubmit={handleSubmit} noValidate className="flex w-full flex-col gap-8">
        <div className="relative flex flex-col gap-4">
          <FieldLabel required className="mb-0">
            Оцени ја страната
          </FieldLabel>
          {/* Dodeka se lebdi nad edna dzvezda, se palat site pred nea. */}
          <div
            className="flex items-center justify-center gap-3"
            onMouseLeave={() => setHovered(0)}
          >
            {RATINGS.map((value) => (
              <StarButton
                key={value}
                value={value}
                filled={value <= (hovered || rating)}
                selected={value <= rating}
                disabled={submitting}
                onMouseEnter={() => setHovered(value)}
                onClick={() => {
                  setRating(value);
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
          <FieldLabel htmlFor={messageId} className="mb-0">
            Што би сакал/а да се подобри или да видиш ново?
          </FieldLabel>
          <div className="h-[88px] overflow-hidden rounded-xl border border-[#CCCCCC] bg-white">
            <textarea
              id={messageId}
              value={message}
              disabled={submitting}
              onChange={(event) => setMessage(event.target.value)}
              placeholder="Напиши ни што мислиш..."
              className="h-full w-full resize-none p-4 font-[family-name:var(--font-manrope)] text-[14px] leading-6 text-black outline-none placeholder:text-[#595959] disabled:opacity-60"
            />
          </div>

          <div className="flex items-center justify-end">
            <PrimaryButton
              type="submit"
              disabled={submitting}
              className="h-10 w-36 font-[family-name:var(--font-manrope)] text-[14px] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-200)] disabled:opacity-60"
            >
              {submitting ? "Се праќа…" : "Прати"}
            </PrimaryButton>
          </div>
        </div>
      </form>
    </DialogShell>
  );
}

function StarButton({ value, filled, selected, disabled, onClick, onMouseEnter }) {
  return (
    <button
      type="button"
      onClick={onClick}
      onMouseEnter={onMouseEnter}
      onFocus={onMouseEnter}
      disabled={disabled}
      aria-label={`${value} од 5`}
      aria-pressed={selected}
      className={`cursor-pointer p-1 transition-colors disabled:cursor-not-allowed disabled:opacity-60 ${
        filled
          ? "text-[var(--color-primary-200)]"
          : "text-[var(--color-grays-300)]"
      }`}
    >
      {/* FontAwesome ja vrzuva goleminata za font-size, ne za h-*. */}
      <FontAwesomeIcon
        icon={filled ? faStarFilled : faStarEmpty}
        className="text-[28px]"
      />
    </button>
  );
}
