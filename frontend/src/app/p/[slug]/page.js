"use client";

import { notFound, useParams } from "next/navigation";
import AppShell from "@/components/AppShell";
import ForumBanner from "@/components/ForumBanner";
import ForumEmptyState from "@/components/ForumEmptyState";
import ForumFilters from "@/components/ForumFilters";
import ForumThreadList from "@/components/ForumThreadList";
import { useForums } from "@/hooks/useForums";
import drzhavnaMaturaPageMock from "../../../../public/forum-page-mock.json";
import opshtiDiskusiiPageMock from "../../../../public/forum-page-opshti-diskusii-mock.json";

// Static page mocks keyed by their forum slug. Add a mock here to give a forum
// its own banner + threads until the real forum page endpoint is wired up.
const FORUM_PAGE_MOCKS = [drzhavnaMaturaPageMock, opshtiDiskusiiPageMock];

export default function TopicForumPage() {
  const { slug } = useParams();
  const { general, schoolsByCity, loading, error } = useForums();

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

  const mockPage = FORUM_PAGE_MOCKS.find((entry) => entry.forum.slug === slug) ?? null;
  const schoolForums = schoolsByCity.flatMap((entry) => entry.forums);
  const forum =
    mockPage?.forum ?? [...general, ...schoolForums].find((item) => item.slug === slug);
  const threads = mockPage ? mockPage.threads : [];

  if (!forum) {
    notFound();
  }

  if (threads.length === 0) {
    return (
      <AppShell>
        <div className="flex w-[990px] max-w-full flex-col gap-6">
          <ForumBanner
            title={forum.name}
            description={forum.description}
            icon={forum.imageUrl}
            slug={forum.slug}
            type={forum.type}
            membersCount={forum.members_count}
          />
          <ForumEmptyState />
        </div>
      </AppShell>
    );
  }

  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-6">
        <ForumBanner
          title={forum.name}
          description={forum.description}
          icon={forum.imageUrl}
          slug={forum.slug}
          type={forum.type}
          membersCount={forum.members_count}
        />
        <ForumFilters />
        <ForumThreadList forumName={forum.name} forumSlug={forum.slug} threads={threads} />
      </div>
    </AppShell>
  );
}
