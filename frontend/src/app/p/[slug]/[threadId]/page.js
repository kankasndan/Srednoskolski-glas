"use client";

import { notFound, useParams } from "next/navigation";
import AppShell from "@/components/AppShell";
import BackButton from "@/components/BackButton";
import CommentComposer from "@/components/CommentComposer";
import CommentList from "@/components/CommentList";
import CommentsHeader from "@/components/CommentsHeader";
import ThreadPost from "@/components/ThreadPost";
import { useThread } from "@/hooks/useThread";

function StatusMessage({ children }) {
  return (
    <p className="font-(family-name:--font-manrope) text-[16px] text-[#595959]">
      {children}
    </p>
  );
}

export default function ThreadPage() {
  const { slug, threadId } = useParams();
  const { forum, thread, comments, loading, error, missing } = useThread(slug, threadId);

  if (loading) {
    return (
      <AppShell>
        <StatusMessage>Се вчитува…</StatusMessage>
      </AppShell>
    );
  }

  if (error) {
    return (
      <AppShell>
        <StatusMessage>Не успеа вчитувањето на дискусијата.</StatusMessage>
      </AppShell>
    );
  }

  if (missing) {
    notFound();
  }

  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-12 font-(family-name:--font-manrope)">
        <div className="self-start">
          <BackButton label={`Назад кон ${forum.name}`} tone="muted" />
        </div>
        <ThreadPost forum={forum} thread={thread} />
        <CommentComposer forumSlug={forum.slug} />
        <CommentsHeader count={thread.comments_count} />
        <CommentList comments={comments} />
      </div>
    </AppShell>
  );
}
