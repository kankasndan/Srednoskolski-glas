"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { formatDistanceToNow } from "date-fns";
import { mk } from "date-fns/locale";
import ConfirmDialog from "@/components/ui/ConfirmDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import ProfileForumTag from "@/components/profile/ProfileForumTag";
import ThreadActionButton from "@/components/thread/ThreadActionButton";
import { getMyComments } from "@/api/profile";

function ProfileCommentItem({ comment }) {
  const [confirmingDelete, setConfirmingDelete] = useState(false);
  const [deleted, setDeleted] = useState(false);

  const thread = comment.thread;
  const threadHref = `/p/${thread.forum.slug}/${thread.id}`;

  return (
    <article className="relative flex cursor-pointer items-center justify-between gap-8 rounded-3xl border-b border-b-[#CFE9ED] p-6 transition-colors hover:bg-[#DCEBED]">
      <Link
        href={threadHref}
        aria-label={thread.title}
        className="absolute inset-0 rounded-3xl"
      />

      <div className="flex min-w-0 flex-col gap-4">
        <div className="flex items-center gap-2">
          <ProfileForumTag forum={thread.forum} />
          <span className="font-(family-name:--font-roboto) text-[12px] leading-4 text-[#595959]">
            {formatDistanceToNow(new Date(comment.created_at), {
              addSuffix: true,
              locale: mk,
            })}
          </span>
        </div>

        <p className="font-(family-name:--font-manrope) text-[16px] leading-none text-black">
          {comment.content}
        </p>

        <div className="flex items-center gap-1.5 font-(family-name:--font-manrope) text-[12px] leading-none text-(--color-grays-700)">
          <span aria-hidden>↳</span>
          <span className="truncate">{thread.title}</span>
        </div>

        <div className="relative z-10 flex items-center gap-4 font-(family-name:--font-manrope) text-[12px] font-medium leading-4.5 text-(--color-grays-400)">
          <button
            type="button"
            className="cursor-pointer transition-colors hover:text-[#582FF5]"
          >
            Измени
          </button>
          <button
            type="button"
            onClick={() => setConfirmingDelete(true)}
            className="cursor-pointer transition-colors hover:text-[#DC2626]"
          >
            Избриши
          </button>
        </div>
      </div>

      <div className="relative z-10 flex shrink-0">
        <ThreadActionButton
          icon="/Chevrons up.svg"
          label="Гласови"
          count={comment.upvotes ?? 0}
          href={threadHref}
        />
      </div>

      <ConfirmDialog
        open={confirmingDelete}
        title="Дали си сигурен дека сакаш да го избришеш овој коментар?"
        confirmLabel="Избриши"
        onCancel={() => setConfirmingDelete(false)}
        onConfirm={() => {
          setConfirmingDelete(false);
          setDeleted(true);
        }}
      />

      <InfoDialog
        open={deleted}
        title="Коментарот беше успешно избришан."
        onClose={() => setDeleted(false)}
      />
    </article>
  );
}

export default function ProfileCommentList() {
  const [comments, setComments] = useState(null);

  useEffect(() => {
    let active = true;

    getMyComments()
      .then((data) => {
        if (active) setComments(data);
      })
      .catch(() => {
        if (active) setComments([]);
      });

    return () => {
      active = false;
    };
  }, []);

  if (comments === null) {
    return <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  }

  if (comments.length === 0) {
    return (
      <p className="text-[16px] text-[#595959]">Сè уште немаш напишано коментари.</p>
    );
  }

  return (
    <div className="flex flex-col gap-6">
      {comments.map((comment) => (
        <ProfileCommentItem key={comment.id} comment={comment} />
      ))}
    </div>
  );
}
