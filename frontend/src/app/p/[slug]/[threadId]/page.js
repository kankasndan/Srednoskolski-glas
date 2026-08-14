"use client";

import { useState } from "react";
import { notFound, useParams } from "next/navigation";
import AppShell from "@/components/shell/AppShell";
import BackButton from "@/components/shell/BackButton";
import CommentComposer from "@/components/thread/CommentComposer";
import CommentList from "@/components/thread/CommentList";
import CommentsHeader from "@/components/thread/CommentsHeader";
import MobileFooter from "@/components/shell/MobileFooter";
import ThreadPost from "@/components/thread/ThreadPost";
import { useThread } from "@/hooks/useThread";

function StatusMessage({ children }) {
  return (
    <p className="font-(family-name:--font-manrope) text-[16px] text-[#999999]">
      {children}
    </p>
  );
}

export default function ThreadPage() {
  const { slug, threadId } = useParams();
  const [sort, setSort] = useState("best");
  const { forum, thread, comments, loading, error, missing, patchThread, addComment } =
    useThread(slug, threadId, sort);

  if (loading && !thread) {
    return (
      <AppShell>
        <StatusMessage>Се вчитува…</StatusMessage>
      </AppShell>
    );
  }

  if (error && !thread) {
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
      <div className="flex w-[990px] max-w-full flex-col gap-8 font-(family-name:--font-manrope) md:max-w-[680px] lg:max-w-full">
        <div className="self-start">
          <BackButton
            href={`/p/${forum.slug}`}
            label={`Назад кон ${forum.name}`}
            tone="muted"
          />
        </div>
        <ThreadPost forum={forum} thread={thread} onThreadUpdated={patchThread} />
        <CommentComposer
          threadId={thread.id}
          isAnonymousThread={thread.is_anonymous}
          isThreadOwner={thread.is_owner}
          onCreated={addComment}
        />
        <CommentsHeader
          count={thread.comments_count}
          sort={sort}
          onSortChange={setSort}
        />
        <CommentList
          comments={comments}
          threadId={thread.id}
          isAnonymousThread={thread.is_anonymous}
          isThreadOwner={thread.is_owner}
          onCommentCreated={addComment}
        />

        {/* Kontejnerot ovde ima gap-8, pa treba pomala margina od feed-ot. */}
        <MobileFooter className="mt-3" />
      </div>
    </AppShell>
  );
}
