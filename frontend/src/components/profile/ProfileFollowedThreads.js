"use client";

import { useEffect, useState } from "react";
import { unfollowThread } from "@/api/threads";
import { getMyFollowedThreads } from "@/api/profile";
import ProfileThreadItem from "@/components/profile/ProfileThreadItem";

function UnfollowButton({ busy, onClick, className = "" }) {
  return (
    <button
      type="button"
      disabled={busy}
      onClick={onClick}
      className={`relative z-10 flex h-10 w-36 shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl bg-(--color-primary-200) px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-grays-100) transition-colors active:bg-[#3300F5] hover:bg-[#3300F5] disabled:opacity-60 ${className}`}
    >
      {busy ? "…" : "Отследи"}
    </button>
  );
}

export default function ProfileFollowedThreads() {
  const [threads, setThreads] = useState(null);
  const [busyId, setBusyId] = useState(null);

  useEffect(() => {
    let active = true;

    getMyFollowedThreads()
      .then((data) => {
        if (active) setThreads(data);
      })
      .catch(() => {
        if (active) setThreads([]);
      });

    return () => {
      active = false;
    };
  }, []);

  async function handleUnfollow(threadId) {
    if (busyId != null) return;

    setBusyId(threadId);

    try {
      await unfollowThread(threadId);
      setThreads((prev) => (prev ?? []).filter((thread) => thread.id !== threadId));
    } catch {
      // Keep the row on failure.
    } finally {
      setBusyId(null);
    }
  }

  if (threads === null) {
    return <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  }

  if (threads.length === 0) {
    return (
      <p className="text-[16px] text-[#595959]">
        Сè уште не следиш дискусии.
      </p>
    );
  }

  return (
    <div className="flex flex-col gap-8 md:gap-4">
      {threads.map((thread) => (
        <div key={thread.id} className="flex flex-col gap-2">
          {/* Na telefon kopcheto stoi vo kartichkata nad glasovite, inaku pod nea. */}
          <ProfileThreadItem
            thread={thread}
            canManage={false}
            action={
              <UnfollowButton
                busy={busyId === thread.id}
                onClick={() => handleUnfollow(thread.id)}
                className="order-last min-[420px]:order-none md:hidden"
              />
            }
          />
          {/* pr-6 go poramnuva so kopcheto vo kartichkata na forumite. */}
          <div className="hidden justify-end md:flex md:pr-6">
            <UnfollowButton
              busy={busyId === thread.id}
              onClick={() => handleUnfollow(thread.id)}
            />
          </div>
        </div>
      ))}
    </div>
  );
}
