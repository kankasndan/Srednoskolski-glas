"use client";

import { notFound, useParams } from "next/navigation";
import AppShell from "@/components/AppShell";
import ForumBanner from "@/components/ForumBanner";
import ForumEmptyState from "@/components/ForumEmptyState";
import ForumFilters from "@/components/ForumFilters";
import ForumThreadList from "@/components/ForumThreadList";
import { useForums } from "@/hooks/useForums";
import forumPageMock from "../../../../public/forum-page-mock.json";

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

  const mockForum = forumPageMock.forum.slug === slug ? forumPageMock.forum : null;
  const forum = mockForum ?? forums.find((item) => item.slug === slug);
  const threads = mockForum ? forumPageMock.threads : [];

  if (!forum) {
    notFound();
  }

  if (threads.length === 0) {
    return (
      <AppShell>
        <ForumEmptyState />
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
        />
        <ForumFilters />
        <ForumThreadList forumName={forum.name} threads={threads} />
      </div>
    </AppShell>
  );
}
