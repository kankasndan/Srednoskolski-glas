"use client";

import { useEffect, useState } from "react";
import { notFound, useParams } from "next/navigation";
import AppShell from "@/components/shell/AppShell";
import BackButton from "@/components/shell/BackButton";
import CommentComposer from "@/components/thread/CommentComposer";
import CommentList from "@/components/thread/CommentList";
import CommentsHeader from "@/components/thread/CommentsHeader";
import MobileFooter from "@/components/shell/MobileFooter";
import ThreadPost from "@/components/thread/ThreadPost";
import { useCommentHashId } from "@/hooks/useHashTarget";
import { useThread } from "@/hooks/useThread";
import { findCommentPath } from "@/lib/commentPath";

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
  const {
    forum,
    thread,
    comments,
    loading,
    error,
    missing,
    patchThread,
    addComment,
    markCommentDeleted,
  } = useThread(slug, threadId, sort);
  const linkedCommentId = useCommentHashId();
  const [linkedBranch, setLinkedBranch] = useState(null);

  // Spodelen link kon odgovor: grankata do nego se otvora sama.
  useEffect(() => {
    if (!linkedCommentId || comments.length === 0) return undefined;
    if (comments.some((comment) => comment.id === linkedCommentId)) return undefined;

    let active = true;

    findCommentPath(comments, linkedCommentId).then((found) => {
      if (active) setLinkedBranch(found?.path?.length ? found : null);
    });

    return () => {
      active = false;
    };
  }, [linkedCommentId, comments]);

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
    <AppShell contentClassName="!px-2 sm:!px-6 lg:!px-8 xl:!px-12">
      <div className="flex w-full max-w-[1100px] flex-col gap-6 font-(family-name:--font-manrope) lg:gap-8">
        <div className="self-start">
          <BackButton
            href={`/p/${forum.slug}`}
            label={`Назад кон ${forum.name}`}
            tone="muted"
          />
        </div>
        <ThreadPost forum={forum} thread={thread} onThreadUpdated={patchThread} />
        <div id="comments" className="flex scroll-mt-24 flex-col gap-6">
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
        </div>
        <CommentList
          comments={comments}
          threadId={thread.id}
          isAnonymousThread={thread.is_anonymous}
          isThreadOwner={thread.is_owner}
          onCommentCreated={addComment}
          onCommentDeleted={markCommentDeleted}
          expandPath={linkedBranch?.path ?? null}
          preloadedReplies={linkedBranch?.replies ?? null}
        />

        {/* Kontejnerot ovde ima gap-8, pa treba pomala margina od feed-ot. */}
        <MobileFooter className="mt-3" />
      </div>
    </AppShell>
  );
}
