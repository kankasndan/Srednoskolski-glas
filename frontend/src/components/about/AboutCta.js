"use client";

import Link from "next/link";
import { useState } from "react";
import { submitFeedback } from "@/api/feedback";
import AboutSectionTitle from "@/components/about/AboutSectionTitle";
import FeedbackDialog from "@/components/about/FeedbackDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import { useCreateThreadAction } from "@/hooks/useCreateThreadAction";
import { useProfile } from "@/hooks/useProfile";

const filledButton =
  "order-1 flex h-10 w-full cursor-pointer items-center justify-center gap-4 rounded-xl border border-[var(--color-primary-200)] bg-[var(--color-primary-200)] px-4 py-2 text-[16px] font-bold leading-none text-white transition-colors hover:border-[#3300F5] hover:bg-[#3300F5] lg:order-2 lg:flex-1 lg:text-[14px]";

export default function AboutCta({ className = "" }) {
  const { user } = useProfile();
  const { onClick: onStartDiscussion } = useCreateThreadAction();
  const [feedbackOpen, setFeedbackOpen] = useState(false);
  const [sent, setSent] = useState(false);

  async function handleFeedback({ rating, message }) {
    await submitFeedback({ rating, message });
    setFeedbackOpen(false);
    setSent(true);
  }

  return (
    <section className={`flex flex-col items-center gap-12 ${className}`}>
      <div className="flex flex-col items-center gap-6 lg:gap-8">
        <AboutSectionTitle>Сега е твој ред!</AboutSectionTitle>
        <p className="max-w-[361px] text-center text-[16px] leading-[1.5] text-[var(--color-grays-700)]">
          Постави прашање, сподели искуство, поврзи се со некој кој те разбира.
        </p>
      </div>

      <div className="flex w-full max-w-[552px] flex-col gap-3 lg:flex-row lg:gap-4">
        <button
          type="button"
          onClick={() => setFeedbackOpen(true)}
          className="order-2 flex h-10 w-full cursor-pointer items-center justify-center gap-4 rounded-xl border border-[var(--color-primary-200)] px-4 py-2 text-[16px] font-bold leading-none text-[var(--color-grays-900)] transition-colors hover:bg-[#E5E5E5] lg:order-1 lg:flex-1 lg:text-[14px]"
        >
          Кажи ни што мислиш
        </button>

        {user ? (
          <Link href="/new" onClick={onStartDiscussion} className={filledButton}>
            Започни нова дискусија
          </Link>
        ) : (
          <Link href="/register" className={filledButton}>
            Регистрирај се
          </Link>
        )}
      </div>

      <FeedbackDialog
        open={feedbackOpen}
        onClose={() => setFeedbackOpen(false)}
        onSubmit={handleFeedback}
      />

      <InfoDialog
        open={sent}
        title="Ти благодариме! Твоето мислење е испратено."
        message="Секој предлог ни помага да ја подобриме платформата."
        onClose={() => setSent(false)}
      />
    </section>
  );
}
