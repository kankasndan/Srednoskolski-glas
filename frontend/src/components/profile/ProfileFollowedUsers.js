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
    <article className="relative flex flex-col gap-4 rounded-3xl border border-[#CFE9ED] px-3 py-6 transition-colors active:bg-gray-50 hover:bg-gray-50 md:flex-row md:items-center md:justify-between md:gap-6 md:rounded-2xl md:p-6">
      {href ? (
        <Link
          href={href}
          aria-label={user.username}
          className="absolute inset-0 rounded-3xl md:rounded-2xl"
        />
      ) : null}

      {/* Na telefon avatarot stoi vo red so imeto, a uchilishteto pagja pod niv. */}
      <div className="grid min-w-0 grid-cols-[auto_1fr] items-center gap-x-3 gap-y-2 md:flex md:items-center md:gap-5">
        <span className="relative size-10 shrink-0 overflow-hidden rounded-full md:size-16">
          {/^https?:\/\//i.test(user.imageUrl || "") ? (
            <img
              src={user.imageUrl}
              alt=""
              width={64}
              height={64}
              className="size-10 object-cover md:size-16"
            />
          ) : (
            <Image
              src={user.imageUrl || "/Generic-avatar-profile.svg"}
              alt=""
              width={64}
              height={64}
              className="size-10 object-cover md:size-16"
            />
          )}
        </span>

        <div className="contents md:flex md:min-w-0 md:flex-col md:gap-2">
          <h3 className="truncate font-(family-name:--font-oswald) text-[14px] font-bold uppercase leading-none text-(--color-grays-900)">
            {user.username}
          </h3>
          {schoolLabel ? (
            <p className="col-span-2 truncate font-(family-name:--font-manrope) text-[14px] leading-none text-(--color-grays-700) md:col-span-1 md:text-[16px]">
              {schoolLabel}
            </p>
          ) : null}
        </div>
      </div>

      <button
        type="button"
        disabled={busy}
        onClick={handleUnfollow}
        className="relative z-10 flex h-10 w-36 shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl bg-(--color-primary-200) px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-grays-100) transition-colors active:bg-[#3300F5] hover:bg-[#3300F5] disabled:opacity-60"
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
