"use client";

import AppShell from "@/components/shell/AppShell";
import Threads from "@/components/thread/Threads";
import ForumBanner from "@/components/forum/ForumBanner";
import { useParams } from "next/navigation";
import { useEffect, useState } from "react";
import { API_BASE_URL } from "@/lib/api";

export default function ForumPage() {
  const { slug } = useParams();
  const [forum, setForum] = useState(null);
  const [forumError, setForumError] = useState(null);

  useEffect(() => {
    let cancelled = false;

    async function fetchForum() {
      setForum(null);
      setForumError(null);

      try {
        const response = await fetch(`${API_BASE_URL}/api/p/${slug}`, {
          credentials: "include",
        });

        if (!response.ok) {
          throw new Error(`Failed to load forum: ${response.status}`);
        }

        const responseData = await response.json();
        if (!cancelled) {
          setForum(responseData.data.forum);
        }
      } catch (error) {
        if (!cancelled) {
          setForumError(error.message || "Failed to load forum");
        }
      }
    }

    if (slug) {
      fetchForum();
    }

    return () => {
      cancelled = true;
    };
  }, [slug]);

  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-8">
        {forumError ? (
          <p className="font-(family-name:--font-manrope) text-[16px] text-[#595959]">
            Форумот не може да се вчита.
          </p>
        ) : null}

        {forum ? (
          <ForumBanner
            key={forum.slug}
            title={forum.name}
            description={forum.description}
            icon={forum.imageUrl}
            banner={forum.bannerUrl}
            slug={forum.slug}
            type={forum.type}
            membersCount={forum.members_count}
            isFollowing={forum.is_following}
          />
        ) : !forumError ? (
          <div
            className="h-[180px] w-full animate-pulse rounded-3xl bg-[#E8F4F6]"
            aria-busy="true"
            aria-label="Се вчитува форумот"
          />
        ) : null}

        {/* Threads wait for forum metadata so /api/p/{slug} finishes before /threads. */}
        {forum ? <Threads forum={forum.slug} /> : null}
      </div>
    </AppShell>
  );
}
