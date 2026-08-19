"use client";

import { useEffect, useState } from "react";
import { getMyThreads, getUserThreads } from "@/api/profile";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";
import ProfileThreadItem from "@/components/profile/ProfileThreadItem";

export default function ProfileThreadList({
  username = null,
  isOwnProfile = true,
}) {
  const [threads, setThreads] = useState(null);

  useEffect(() => {
    let active = true;

    const loader = username ? getUserThreads(username) : getMyThreads();

    loader
      .then((data) => {
        if (active) setThreads(data);
      })
      .catch(() => {
        if (active) setThreads([]);
      });

    return () => {
      active = false;
    };
  }, [username]);

  function handleDeleted(threadId) {
    setThreads((prev) => (prev ?? []).filter((thread) => thread.id !== threadId));
  }

  let content;
  if (threads === null) {
    content = <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  } else if (threads.length === 0) {
    content = (
      <p className="text-[16px] text-[#595959]">
        {isOwnProfile
          ? "Сè уште немаш започнато дискусии."
          : "Овој корисник сè уште нема започнато дискусии."}
      </p>
    );
  } else {
    content = (
      <div className="flex flex-col gap-10 md:gap-0">
        {threads.map((thread) => (
          <ProfileThreadItem
            key={thread.id}
            thread={thread}
            onDeleted={handleDeleted}
            canManage={isOwnProfile}
          />
        ))}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-10 md:gap-6">
      {isOwnProfile ? <StartDiscussionButton full className="md:max-w-[268px]" /> : null}
      {content}
    </div>
  );
}
