"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { followForum, getExplore, unfollowForum } from "@/api/forums";
import AppShell from "@/components/shell/AppShell";
import Threads from "@/components/thread/Threads";
import { useProfile } from "@/hooks/useProfile";
import { needsOnboarding, ONBOARDING_REQUIRED_MESSAGE } from "@/lib/capabilities";

export default function ExploreDiscussions() {
  const [forums, setForums] = useState(null);
  const [threads, setThreads] = useState(null);
  const [error, setError] = useState("");

  useEffect(() => {
    let active = true;

    getExplore()
      .then((data) => {
        if (!active) return;
        setForums(Array.isArray(data?.forums) ? data.forums : []);
        setThreads(Array.isArray(data?.threads) ? data.threads : []);
      })
      .catch(() => {
        if (!active) return;
        setError("Не успеавме да ги вчитаме податоците.");
        setForums([]);
        setThreads([]);
      });

    return () => {
      active = false;
    };
  }, []);

  return (
    <AppShell>
      <div className="flex w-[1100px] max-w-full flex-col gap-16">
        <div className="flex flex-col">
          <h1 className="font-[family-name:var(--font-manrope)] text-[24px] font-bold tracking-normal text-[#582FF5]">
            Истражи
          </h1>
          <p className="max-w-[720px] font-[family-name:var(--font-manrope)] text-[16px] tracking-normal text-[#595959]">
            Откриј популарни заедници и дискусии што ги движат средношколците низ целата земја.
          </p>
        </div>

        <ExploreSection title="Најпосетувани форуми">
          {forums === null ? (
            <p className="text-[16px] text-[#595959]">Се вчитува…</p>
          ) : forums.length === 0 ? (
            <p className="text-[16px] text-[#595959]">
              {error || "Сè уште нема форуми за прикажување."}
            </p>
          ) : (
            <div className="grid gap-6 md:grid-cols-2">
              {forums.map((forum) => (
                <FeaturedForumCard
                  key={forum.id ?? forum.slug}
                  forum={forum}
                  onForumChange={(next) => {
                    setForums((current) =>
                      (current ?? []).map((item) =>
                        item.id === next.id || item.slug === next.slug
                          ? { ...item, ...next }
                          : item,
                      ),
                    );
                  }}
                />
              ))}
            </div>
          )}
        </ExploreSection>

        <ExploreSection title="Најпопуларни дискусии оваа недела">
          {threads === null ? (
            <p className="text-[16px] text-[#595959]">Се вчитува…</p>
          ) : (
            <div className="explore-hide-filters">
              <Threads defaultSort="top" staticThreads={threads} showSort={false} />
            </div>
          )}
        </ExploreSection>
      </div>
    </AppShell>
  );
}

function ExploreSection({ title, children }) {
  return (
    <section className="flex flex-col gap-6">
      <h2 className="font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-[27px] text-black">
        {title}
      </h2>
      {children}
    </section>
  );
}

function FeaturedForumCard({ forum, onForumChange }) {
  const { user, loading: profileLoading } = useProfile();
  const [following, setFollowing] = useState(Boolean(forum.is_following));
  const [pending, setPending] = useState(false);
  const [error, setError] = useState("");

  async function toggleFollow(event) {
    event.preventDefault();
    event.stopPropagation();
    if (!forum.slug || pending || profileLoading || user == null) return;

    setError("");

    if (needsOnboarding(user)) {
      setError(ONBOARDING_REQUIRED_MESSAGE);
      return;
    }

    const nextFollowing = !following;
    setFollowing(nextFollowing);
    setPending(true);

    try {
      const data = nextFollowing
        ? await followForum(forum.slug)
        : await unfollowForum(forum.slug);

      const resolved =
        typeof data?.is_following === "boolean" ? data.is_following : nextFollowing;
      setFollowing(resolved);
      onForumChange?.({
        ...forum,
        is_following: resolved,
        members_count:
          typeof data?.members_count === "number"
            ? data.members_count
            : forum.members_count,
      });
    } catch (err) {
      setFollowing(!nextFollowing);
      if (err?.status === 403) {
        setError(err.message || ONBOARDING_REQUIRED_MESSAGE);
      }
    } finally {
      setPending(false);
    }
  }

  const icon = forum.imageUrl || `/icons/${forum.slug}.svg`;

  return (
    <article className="flex min-h-[141px] flex-col justify-between rounded-3xl border border-[#CFE9ED] bg-white p-6">
      <div className="flex items-start justify-between gap-4">
        <Link
          href={`/p/${forum.slug}`}
          className="flex min-w-0 items-center gap-4"
        >
          <div className="flex size-14 shrink-0 items-center justify-center rounded-xl bg-[#DCEBED]">
            <Image
              src={icon}
              alt=""
              width={32}
              height={32}
              className="size-[32px] object-contain"
            />
          </div>

          <div className="min-w-0">
            <h3 className="truncate text-[14px] font-extrabold uppercase leading-[19px] text-black font-[family-name:var(--font-oswald)]">
              {forum.name}
            </h3>
            <p className="mt-1 flex items-center gap-1 text-[12px] leading-none text-black font-[family-name:var(--font-manrope)]">
              <Image src="/user-heart-line.svg" alt="" width={16} height={16} className="size-4" />
              {forum.members_count ?? 0} членови
            </p>
          </div>
        </Link>

        <button
          type="button"
          disabled={pending || profileLoading || user == null}
          aria-pressed={following}
          aria-label={following ? `Отследи го форумот ${forum.name}` : `Следи го форумот ${forum.name}`}
          onClick={toggleFollow}
          className={`flex h-10 w-24 cursor-pointer items-center justify-center rounded-xl px-4 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none transition-colors disabled:cursor-not-allowed disabled:opacity-70 ${
            following
              ? "bg-[var(--color-primary-200)] text-white"
              : "bg-[var(--color-primary-300)] text-white hover:bg-[var(--color-primary-200)]"
          }`}
        >
          {following ? "Следиш" : "Следи"}
        </button>
      </div>

      <p className="font-[family-name:var(--font-manrope)] text-[14px] leading-[20px] text-[#808080]">
        {forum.description}
      </p>
      {error ? (
        <p className="font-[family-name:var(--font-manrope)] text-[12px] leading-4 text-[#DC2626]">
          {error}
        </p>
      ) : null}
    </article>
  );
}
