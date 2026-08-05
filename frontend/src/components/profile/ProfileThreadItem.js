"use client";

import Link from "next/link";
import { useState } from "react";
import { formatDistanceToNow } from "date-fns";
import { mk } from "date-fns/locale";
import ConfirmDialog from "@/components/ui/ConfirmDialog";
import EditThreadDialog from "@/components/thread/EditThreadDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import ProfileForumTag from "@/components/profile/ProfileForumTag";
import ThreadStatButtons from "@/components/thread/ThreadStatButtons";
import { stripHtml } from "@/lib/html";

export default function ProfileThreadItem({ thread }) {
  const [editing, setEditing] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);
  const [deleted, setDeleted] = useState(false);

  const href = `/p/${thread.forum.slug}/${thread.id}`;

  return (
    <article className="relative flex cursor-pointer items-center justify-between gap-8 rounded-3xl border-b border-b-[#CFE9ED] p-6 transition-colors hover:bg-[#DCEBED]">
      <Link href={href} aria-label={thread.title} className="absolute inset-0 rounded-3xl" />

      <div className="flex min-w-0 flex-col gap-4">
        <div className="flex items-center gap-2">
          <ProfileForumTag forum={thread.forum} />
          <span className="font-(family-name:--font-roboto) text-[12px] leading-4 text-[#595959]">
            {formatDistanceToNow(new Date(thread.created_at), {
              addSuffix: true,
              locale: mk,
            })}
          </span>
        </div>

        <div className="flex flex-col gap-2">
          <h3 className="w-fit max-w-full overflow-hidden text-ellipsis whitespace-nowrap font-(family-name:--font-manrope) text-[20px] font-bold leading-6.75 text-black">
            {thread.title}
          </h3>
          <p className="font-(family-name:--font-manrope) text-[16px] leading-5.5 text-[#595959]">
            {stripHtml(thread.description)}
          </p>
        </div>

        <div className="relative z-10 mt-2 flex items-center gap-4 font-(family-name:--font-manrope) text-[12px] font-medium leading-4.5 text-(--color-grays-400)">
          <button
            type="button"
            onClick={() => setEditing(true)}
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

      <ThreadStatButtons thread={thread} href={href} />

      {editing && (
        <EditThreadDialog
          open
          thread={thread}
          onClose={() => setEditing(false)}
          onSave={() => setEditing(false)}
        />
      )}

      <ConfirmDialog
        open={confirmingDelete}
        title="Дали си сигурен дека сакаш да ја избришеш оваа дискусија?"
        confirmLabel="Избриши"
        onCancel={() => setConfirmingDelete(false)}
        onConfirm={() => {
          setConfirmingDelete(false);
          setDeleted(true);
        }}
      />

      <InfoDialog
        open={deleted}
        title="Дискусијата беше успешно избришана."
        message="Корисниците сè уште може да ги гледаат коментарите."
        onClose={() => setDeleted(false)}
      />
    </article>
  );
}
