"use client";

import { notFound, useParams } from "next/navigation";
import AppShell from "@/components/AppShell";
import ForumEmptyState from "@/components/ForumEmptyState";
import { useForums } from "@/hooks/useForums";

const DRZHAVNA_MATURA_SLUG = "drzhavna_matura";

export default function TopicForumPage() {
  const { slug } = useParams();
  const { forums, loading, error } = useForums();

  if (loading) {
    return (
      <AppShell>
        <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal text-[#595959]">
          Се вчитува…
        </p>
      </AppShell>
    );
  }

  if (error) {
    return (
      <AppShell>
        <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal text-[#595959]">
          Не успеа вчитувањето на форумот.
        </p>
      </AppShell>
    );
  }

  const forum = forums.find((item) => item.slug === slug);

  if (!forum || slug === DRZHAVNA_MATURA_SLUG) {
    notFound();
  }

  return (
    <AppShell>
      <ForumEmptyState />
    </AppShell>
  );
}
