"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { unfollowForum } from "@/api/forums";
import { getMyFollowedForums, getUserFollowedForums } from "@/api/profile";

function FollowCard({ forum, canUnfollow, onUnfollowed }) {
  const [busy, setBusy] = useState(false);

  async function handleUnfollow(event) {
    event.preventDefault();
    event.stopPropagation();
    if (!canUnfollow || busy) return;

    setBusy(true);

    try {
      await unfollowForum(forum.slug);
      onUnfollowed?.(forum.id);
    } catch {
      setBusy(false);
    }
  }

  return (
    <article className="relative flex flex-col gap-4 rounded-3xl border border-[#CFE9ED] px-2 py-6 transition-colors active:bg-gray-50 hover:bg-gray-50 md:flex-row md:items-center md:justify-between md:gap-6 md:rounded-2xl md:p-6">
      <Link
        href={`/p/${forum.slug}`}
        aria-label={forum.name}
        className="absolute inset-0 cursor-pointer rounded-3xl md:rounded-2xl"
      />

      {/* Na telefon ikonata stoi vo red so imeto, a opisot pagja pod niv. */}
      <div className="grid min-w-0 grid-cols-[auto_1fr] items-center gap-x-3 gap-y-4 md:flex md:items-center md:gap-5">
        <span className="flex size-6 shrink-0 items-center justify-center md:size-16">
          <img
            src={forum.imageUrl || "/avatars/default-1.svg"}
            alt=""
            width={64}
            height={64}
            className="size-full object-contain"
          />
        </span>

        <div className="contents md:flex md:min-w-0 md:flex-col md:gap-2">
          <div className="flex min-w-0 items-center gap-3">
            <h3 className="truncate font-(family-name:--font-oswald) text-[14px] font-bold uppercase leading-none text-(--color-grays-900)">
              {forum.name}
            </h3>
            <span className="flex shrink-0 items-center gap-1 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-grays-900) md:font-(family-name:--font-oswald)">
              <Image
                src="/user-heart-line.svg"
                alt=""
                width={16}
                height={16}
                className="size-4"
              />
              {forum.members_count}
            </span>
          </div>

          <p className="col-span-2 font-(family-name:--font-manrope) text-[14px] leading-snug text-(--color-grays-700) md:col-span-1 md:text-[16px] md:leading-none">
            {forum.description}
          </p>
        </div>
      </div>

      {canUnfollow ? (
        <button
          type="button"
          disabled={busy}
          onClick={handleUnfollow}
          className="relative z-10 flex h-10 w-36 shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl bg-(--color-primary-200) px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-grays-100) transition-colors active:bg-[#3300F5] hover:bg-[#3300F5] disabled:opacity-60"
        >
          {busy ? "…" : "Отследи"}
        </button>
      ) : null}
    </article>
  );
}

export default function ProfileFollowedForums({
  username = null,
  isOwnProfile = true,
}) {
  const [forums, setForums] = useState(null);

  useEffect(() => {
    let active = true;

    const loader = username
      ? getUserFollowedForums(username)
      : getMyFollowedForums();

    loader
      .then((data) => {
        if (active) setForums(data);
      })
      .catch(() => {
        if (active) setForums([]);
      });

    return () => {
      active = false;
    };
  }, [username]);

  if (forums === null) {
    return <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  }

  if (forums.length === 0) {
    return (
      <p className="text-[16px] text-[#595959]">
        {isOwnProfile
          ? "Сè уште не следиш форуми."
          : "Овој корисник сè уште не следи форуми."}
      </p>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      {forums.map((forum) => (
        <FollowCard
          key={forum.id}
          forum={forum}
          canUnfollow={isOwnProfile}
          onUnfollowed={(id) => {
            setForums((prev) => prev.filter((item) => item.id !== id));
          }}
        />
      ))}
    </div>
  );
}
