"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { unfollowForum } from "@/api/forums";
import { getMyFollowedForums } from "@/api/profile";

function FollowCard({ forum, onUnfollowed }) {
  const [busy, setBusy] = useState(false);

  async function handleUnfollow(event) {
    event.preventDefault();
    event.stopPropagation();
    if (busy) return;

    setBusy(true);

    try {
      await unfollowForum(forum.slug);
      onUnfollowed?.(forum.id);
    } catch {
      setBusy(false);
    }
  }

  return (
    <article className="relative flex items-center justify-between gap-6 rounded-2xl border border-[#CFE9ED] p-6 transition-colors hover:bg-gray-50">
      <Link
        href={`/p/${forum.slug}`}
        aria-label={forum.name}
        className="absolute inset-0 rounded-2xl"
      />

      <div className="flex min-w-0 items-center gap-5">
        <span className="flex size-16 shrink-0 items-center justify-center overflow-hidden">
          <img
            src={forum.imageUrl || "/avatars/default-1.svg"}
            alt=""
            width={120}
            height={120}
            className="size-30 max-w-none object-cover"
          />
        </span>

        <div className="flex min-w-0 flex-col gap-2">
          <div className="flex items-center gap-3">
            <h3 className="font-(family-name:--font-oswald) text-[14px] font-bold uppercase leading-none text-(--color-grays-900)">
              {forum.name}
            </h3>
            <span className="flex items-center gap-1 font-(family-name:--font-oswald) text-[14px] font-bold leading-none text-(--color-grays-900)">
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

          <p className="font-(family-name:--font-manrope) text-[16px] leading-none text-(--color-grays-700)">
            {forum.description}
          </p>
        </div>
      </div>

      <button
        type="button"
        disabled={busy}
        onClick={handleUnfollow}
        className="relative z-10 flex h-10 w-36 shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl bg-(--color-primary-200) px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-grays-100) transition-colors hover:bg-[#4B25E0] disabled:opacity-60"
      >
        {busy ? "…" : "Отследи"}
      </button>
    </article>
  );
}

export default function ProfileFollowedForums() {
  const [forums, setForums] = useState(null);

  useEffect(() => {
    let active = true;

    getMyFollowedForums()
      .then((data) => {
        if (active) setForums(data);
      })
      .catch(() => {
        if (active) setForums([]);
      });

    return () => {
      active = false;
    };
  }, []);

  if (forums === null) {
    return <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  }

  if (forums.length === 0) {
    return <p className="text-[16px] text-[#595959]">Сè уште не следиш форуми.</p>;
  }

  return (
    <div className="flex flex-col gap-4">
      {forums.map((forum) => (
        <FollowCard
          key={forum.id}
          forum={forum}
          onUnfollowed={(id) => {
            setForums((prev) => prev.filter((item) => item.id !== id));
          }}
        />
      ))}
    </div>
  );
}
