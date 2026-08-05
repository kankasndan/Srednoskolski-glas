"use client";

import AppShell from "@/components/AppShell";
import Threads from "@/components/Threads";
import ForumBanner from "@/components/ForumBanner";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";
import { API_BASE_URL } from "@/lib/api";

export default function ForumPage() {
  const { slug } = useParams();
  const [forum, setForum] = useState(null);

  async function fetchForum() {
    const response = await fetch(API_BASE_URL + "/api/p/" + slug);
    const responseData = await response.json();

    const forumData = responseData.data.forum;
    setForum(forumData);
  }

  useEffect(() => {
    fetchForum();
  }, []);

  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-8">
        {forum ? (
          <ForumBanner
            title={forum.name}
            description={forum.description}
            icon={forum.imageUrl}
            slug={forum.slug}
            type={forum.type}
            membersCount={forum.members_count}
          />
        ) : null}
        <Threads forum={slug} />
      </div>
    </AppShell>
  );
}
