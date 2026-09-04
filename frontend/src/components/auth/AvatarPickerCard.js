"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { updateProfile } from "@/api/profile";
import { userFacingError } from "@/lib/api";
import { SELECTABLE_AVATARS } from "@/lib/avatars";
import { finishOnboarding } from "@/lib/onboardingFlow";
import { loadSessionUser, setSessionUser } from "@/lib/sessionUser";

export default function AvatarPickerCard() {
  const router = useRouter();
  const [selected, setSelected] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    let cancelled = false;

    loadSessionUser()
      .then((user) => {
        if (cancelled) return;
        const current = user?.imageUrl;
        if (SELECTABLE_AVATARS.includes(current)) {
          setSelected(current);
        }
      })
      .catch(() => {});

    return () => {
      cancelled = true;
    };
  }, []);

  async function handleContinue() {
    if (!selected) return;

    setSubmitting(true);
    setError("");

    try {
      const user = await updateProfile({ image_url: selected });
      setSessionUser(user);
      finishOnboarding(router);
    } catch (err) {
      setError(userFacingError(err, "Не успеавме да го зачуваме аватарот. Обиди се повторно."));
      setSubmitting(false);
    }
  }

  return (
    <div className="flex w-full max-w-[342px] flex-col items-center gap-10 sm:max-w-[clamp(342px,67.4vw,690px)] lg:max-w-[850px] lg:gap-8 lg:rounded-2xl lg:bg-[#E5E5E5] lg:px-20 lg:pt-10 lg:pb-5 lg:shadow-[7px_7px_4.7px_0px_rgba(0,0,0,0.15)]">
      <div className="flex w-full flex-col items-center gap-6 rounded-2xl bg-[#E5E5E5] p-5 shadow-[7px_7px_9.4px_0px_rgba(0,0,0,0.15)] sm:p-8 lg:bg-transparent lg:p-0 lg:shadow-none">
        <p className="max-w-[320px] text-center font-(family-name:--font-manrope) text-[14px] font-normal leading-snug text-black sm:max-w-[502px] sm:text-[18px] lg:text-[20px]">
          Избери аватар за твојот профил.
        </p>

        <div className="grid grid-cols-4 gap-3 sm:gap-4">
          {SELECTABLE_AVATARS.map((src) => {
            const isSelected = selected === src;

            return (
              <button
                key={src}
                type="button"
                onClick={() => setSelected(src)}
                disabled={submitting}
                aria-label="Избери аватар"
                aria-pressed={isSelected}
                className={`relative size-16 cursor-pointer overflow-hidden rounded-full border-2 transition-colors sm:size-20 lg:size-24 ${
                  isSelected
                    ? "border-[#582FF5]"
                    : "border-transparent hover:border-[#582FF5]/40"
                } disabled:cursor-wait`}
              >
                <img src={src} alt="" className="size-full object-cover" />
              </button>
            );
          })}
        </div>
      </div>

      <div className="flex w-full flex-col items-center gap-[21px] lg:gap-6">
        {error ? (
          <p className="max-w-[400px] text-center font-(family-name:--font-manrope) text-[13px] text-[#DC2626]">
            {error}
          </p>
        ) : null}

        <button
          type="button"
          onClick={handleContinue}
          disabled={!selected || submitting}
          className="h-10 w-full cursor-pointer rounded-2xl bg-[#582FF5] font-(family-name:--font-manrope) text-[16px] font-bold text-white transition-colors hover:bg-[#3300F5] disabled:cursor-not-allowed disabled:bg-[var(--color-grays-300)] disabled:hover:bg-[var(--color-grays-300)] lg:h-14 lg:max-w-[400px]"
        >
          {submitting ? "Се зачувува…" : "Продолжи"}
        </button>

        <button
          type="button"
          onClick={() => finishOnboarding(router)}
          disabled={submitting}
          className="cursor-pointer text-center font-(family-name:--font-manrope) text-[14px] font-normal leading-none text-[#595959] transition-colors hover:text-[#333333] disabled:cursor-wait disabled:opacity-50 lg:text-[16px]"
        >
          Можеби подоцна
        </button>
      </div>
    </div>
  );
}
