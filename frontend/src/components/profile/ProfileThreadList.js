"use client";

import { useEffect, useState } from "react";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";
import ProfileThreadItem from "@/components/profile/ProfileThreadItem";
import { getMyThreads } from "@/api/profile";

export default function ProfileThreadList() {
  const [threads, setThreads] = useState(null);

  useEffect(() => {
    let active = true;

    getMyThreads()
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

  function handleDeleted(threadId) {
    setThreads((prev) => (prev ?? []).filter((thread) => thread.id !== threadId));
  }

  let content;
  if (threads === null) {
    content = <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  } else if (threads.length === 0) {
    content = (
      <p className="text-[16px] text-[#595959]">Сè уште немаш започнато дискусии.</p>
    );
  } else {
    content = (
      <div className="flex flex-col gap-6">
        {threads.map((thread) => (
          <ProfileThreadItem
            key={thread.id}
            thread={thread}
            onDeleted={handleDeleted}
          />
        ))}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-6">
      <StartDiscussionButton />
      {content}
    </div>
  );
}
