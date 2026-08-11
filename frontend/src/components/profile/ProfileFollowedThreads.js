"use client";

import { useEffect, useState } from "react";
import { unfollowThread } from "@/api/threads";
import { getMyFollowedThreads } from "@/api/profile";
import ProfileThreadItem from "@/components/profile/ProfileThreadItem";

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
    <div className="flex flex-col gap-4">
      {threads.map((thread) => (
        <div key={thread.id} className="flex flex-col gap-2">
          <ProfileThreadItem thread={thread} canManage={false} />
          <div className="flex justify-end">
            <button
              type="button"
              disabled={busyId === thread.id}
              onClick={() => handleUnfollow(thread.id)}
              className="flex h-10 w-36 cursor-pointer items-center justify-center gap-3 rounded-xl bg-(--color-primary-200) px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-grays-100) transition-colors hover:bg-[#4B25E0] disabled:opacity-60"
            >
              {busyId === thread.id ? "…" : "Отследи"}
            </button>
          </div>
        </div>
      ))}
    </div>
  );
}
