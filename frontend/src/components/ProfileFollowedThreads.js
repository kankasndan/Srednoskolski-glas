"use client";

import { useEffect, useState } from "react";
import ProfileFollowedThreadItem from "@/components/ProfileFollowedThreadItem";
import { getMyFollowedThreads } from "@/api/profile";

export default function ProfileFollowedThreads() {
  const [threads, setThreads] = useState(null);

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

  if (threads === null) {
    return <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  }

  if (threads.length === 0) {
    return <p className="text-[16px] text-[#595959]">Сè уште не следиш дискусии.</p>;
  }

  return (
    <div className="flex flex-col gap-6">
      {threads.map((thread) => (
        <ProfileFollowedThreadItem key={thread.id} thread={thread} />
      ))}
    </div>
  );
}
