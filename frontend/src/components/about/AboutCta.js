"use client";

import Link from "next/link";
import { useState } from "react";
import AboutSectionTitle from "@/components/about/AboutSectionTitle";
import FeedbackDialog from "@/components/about/FeedbackDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import { useCreateThreadAction } from "@/hooks/useCreateThreadAction";
import { useProfile } from "@/hooks/useProfile";

const filledButton =
  "flex h-10 flex-1 cursor-pointer items-center justify-center rounded-xl border border-[var(--color-primary-200)] bg-[var(--color-primary-200)] px-4 py-2 text-[14px] font-bold leading-none text-white transition-colors hover:border-[#3300F5] hover:bg-[#3300F5]";

export default function AboutCta({ className = "" }) {
  const { user } = useProfile();
  const { onClick: onStartDiscussion } = useCreateThreadAction();
  const [feedbackOpen, setFeedbackOpen] = useState(false);
  const [sent, setSent] = useState(false);

  async function handleFeedback() {
    // TODO: prakjanje na backend - endpointot ushte ne postoi.
    setFeedbackOpen(false);
    setSent(true);
  }

  return (
    <section className={`flex flex-col items-center gap-12 ${className}`}>
      <div className="flex flex-col items-center gap-8">
        <AboutSectionTitle>Сега е твој ред!</AboutSectionTitle>
        <p className="max-w-[361px] text-center text-[16px] text-[var(--color-grays-700)]">
          Постави прашање, сподели искуство, поврзи се со некој кој те разбира.
        </p>
      </div>

      <div className="flex w-full max-w-[552px] flex-col gap-4 sm:flex-row">
        <button
          type="button"
          onClick={() => setFeedbackOpen(true)}
          className="flex h-10 flex-1 cursor-pointer items-center justify-center rounded-xl border border-[var(--color-primary-200)] px-4 py-2 text-[14px] font-bold leading-none text-[var(--color-grays-900)] transition-colors hover:bg-[#E5E5E5]"
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
