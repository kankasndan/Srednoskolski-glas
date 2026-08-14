"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { getMyFollowingUsers, unfollowUser } from "@/api/profile";
import { authorProfileHref } from "@/lib/profileLinks";

function UserCard({ user, onUnfollowed }) {
  const [busy, setBusy] = useState(false);
  const href = authorProfileHref(user);
  const school = user.student_data?.school ?? user.studentData?.school ?? null;
  const city = school?.city?.name ?? null;
  const schoolLabel = [school?.name, city].filter(Boolean).join(", ");

  async function handleUnfollow(event) {
    event.preventDefault();
    event.stopPropagation();
    if (busy || !user.username) return;

    setBusy(true);

    try {
      await unfollowUser(user.username);
      onUnfollowed?.(user.id);
    } catch {
      setBusy(false);
    }
  }

  return (
    <article className="relative flex items-center justify-between gap-6 rounded-2xl border border-[#CFE9ED] p-6 transition-colors hover:bg-gray-50">
      {href ? (
        <Link href={href} aria-label={user.username} className="absolute inset-0 rounded-2xl" />
      ) : null}

      <div className="flex min-w-0 items-center gap-5">
        <span className="relative size-16 shrink-0 overflow-hidden rounded-full">
          {/^https?:\/\//i.test(user.imageUrl || "") ? (
            <img
              src={user.imageUrl}
              alt=""
              width={64}
              height={64}
              className="size-16 object-cover"
            />
          ) : (
            <Image
              src={user.imageUrl || "/Generic-avatar-profile.svg"}
              alt=""
              width={64}
              height={64}
              className="size-16 object-cover"
            />
          )}
        </span>

        <div className="flex min-w-0 flex-col gap-2">
          <h3 className="font-(family-name:--font-oswald) text-[14px] font-bold uppercase leading-none text-(--color-grays-900)">
            {user.username}
          </h3>
          {schoolLabel ? (
            <p className="truncate font-(family-name:--font-manrope) text-[16px] leading-none text-(--color-grays-700)">
              {schoolLabel}
            </p>
          ) : null}
        </div>
      </div>

      <button
        type="button"
        disabled={busy}
        onClick={handleUnfollow}
        className="relative z-10 flex h-10 w-36 shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl bg-(--color-primary-200) px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-grays-100) transition-colors hover:bg-[#3300F5] disabled:opacity-60"
      >
        {busy ? "…" : "Отследи"}
      </button>
    </article>
  );
}

export default function ProfileFollowedUsers() {
  const [users, setUsers] = useState(null);

  useEffect(() => {
    let active = true;

    getMyFollowingUsers()
      .then((data) => {
        if (active) setUsers(data);
      })
      .catch(() => {
        if (active) setUsers([]);
      });

    return () => {
      active = false;
    };
  }, []);

  if (users === null) {
    return <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  }

  if (users.length === 0) {
    return (
      <p className="text-[16px] text-[#595959]">Сè уште не следиш други корисници.</p>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      {users.map((user) => (
        <UserCard
          key={user.id}
          user={user}
          onUnfollowed={(id) => {
            setUsers((prev) => prev.filter((item) => item.id !== id));
          }}
        />
      ))}
    </div>
  );
}
