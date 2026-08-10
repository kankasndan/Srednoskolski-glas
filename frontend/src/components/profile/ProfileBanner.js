"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { format } from "date-fns";
import { mk } from "date-fns/locale";
import Image from "next/image";
import Link from "next/link";
import { followUser, unfollowUser } from "@/api/profile";
import { apiFetch } from "@/lib/api";

const GRADE_LABELS = {
  1: "1ва",
  2: "2ра",
  3: "3та",
  4: "4та",
};

function Chip({ children, href }) {
  const className =
    "flex items-center gap-2 rounded-md border-[0.5px] border-(--color-grays-300) bg-(--color-grays-200) px-2 py-1 font-(family-name:--font-roboto) text-[12px] leading-4 text-[#404040]";

  if (href) {
    return (
      <Link
        href={href}
        className={`${className} transition-colors hover:border-(--color-primary-200) hover:bg-[#F1EEFE] hover:text-(--color-primary-200)`}
      >
        {children}
      </Link>
    );
  }

  return <span className={className}>{children}</span>;
}

function joinedLabel(createdAt) {
  const date = new Date(createdAt);

  if (Number.isNaN(date.getTime())) return null;

  return `Се придружи во ${format(date, "LLLL yyyy", { locale: mk })}`;
}

function readStudentData(user) {
  return user?.student_data ?? user?.studentData ?? null;
}

export default function ProfileBanner({
  user,
  isOwnProfile = true,
  isFollowing = false,
  onFollowChange,
}) {
  const router = useRouter();
  const [loggingOut, setLoggingOut] = useState(false);
  const [following, setFollowing] = useState(Boolean(isFollowing));
  const [followBusy, setFollowBusy] = useState(false);

  const studentData = readStudentData(user);
  const school = studentData?.school ?? null;
  const city = school?.city?.name ?? null;
  const vocation = studentData?.vocation?.name ?? null;
  const grade = studentData?.grade ?? null;
  const gradeLabel = grade != null ? `${GRADE_LABELS[grade] ?? grade} година` : null;
  const schoolForumSlug = school?.forum?.slug ?? null;
  const schoolHref = schoolForumSlug ? `/p/${schoolForumSlug}` : null;
  const schoolLabel = [school?.name, city].filter(Boolean).join(", ");
  const joined = user.created_at ? joinedLabel(user.created_at) : null;

  useEffect(() => {
    setFollowing(Boolean(isFollowing));
  }, [isFollowing]);

  async function handleLogout() {
    setLoggingOut(true);

    try {
      await apiFetch("/api/logout", { method: "POST" });
    } finally {
      localStorage.removeItem("onboarding_pending");
      router.replace("/feed");
    }
  }

  async function handleFollowToggle() {
    if (!user?.username || followBusy) return;

    const nextFollowing = !following;
    setFollowing(nextFollowing);
    setFollowBusy(true);

    try {
      const data = nextFollowing
        ? await followUser(user.username)
        : await unfollowUser(user.username);

      const resolvedFollowing =
        typeof data?.is_following === "boolean" ? data.is_following : nextFollowing;

      setFollowing(resolvedFollowing);
      onFollowChange?.({
        is_following: resolvedFollowing,
        followers: data?.followers,
      });
    } catch (err) {
      setFollowing(!nextFollowing);
      if (err?.status === 401) {
        router.push("/login");
      }
    } finally {
      setFollowBusy(false);
    }
  }

  return (
    <section className="flex items-center justify-between gap-6 rounded-3xl border border-[#CFE9ED] bg-white p-6">
      <div className="flex min-w-0 items-center gap-6">
        {/^https?:\/\//i.test(user.imageUrl || "") ? (
          <img
            src={user.imageUrl}
            alt={user.username}
            width={88}
            height={88}
            className="size-22 shrink-0 rounded-full object-cover"
          />
        ) : (
          <Image
            src={user.imageUrl || "/Generic-avatar-profile.svg"}
            alt={user.username}
            width={88}
            height={88}
            className="size-22 shrink-0 rounded-full object-cover"
          />
        )}

        <div className="flex min-w-0 flex-col gap-4">
          <h1 className="font-(family-name:--font-oswald) text-[20px] font-bold leading-none text-black">
            {user.username}
          </h1>

          <div className="flex flex-wrap items-center gap-2">
            {schoolLabel ? <Chip href={schoolHref}>{schoolLabel}</Chip> : null}
            {gradeLabel ? <Chip>{gradeLabel}</Chip> : null}
            {vocation ? <Chip>{vocation}</Chip> : null}
            {joined ? <Chip>{joined}</Chip> : null}
          </div>
        </div>
      </div>

      <div className="flex shrink-0 flex-col gap-2">
        {isOwnProfile ? (
          <>
            <button
              type="button"
              className="flex h-10 w-36 cursor-pointer items-center justify-center rounded-xl border border-(--color-primary-200) px-4 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-primary-200) transition-colors hover:bg-[#F1EEFE]"
            >
              Уреди профил
            </button>
            <button
              type="button"
              onClick={handleLogout}
              disabled={loggingOut}
              className="flex h-10 w-36 cursor-pointer items-center justify-center rounded-xl border border-(--color-primary-200) px-4 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-(--color-primary-200) transition-colors hover:bg-[#F1EEFE] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {loggingOut ? "Се одјавува…" : "Одјави се"}
            </button>
          </>
        ) : (
          <button
            type="button"
            onClick={handleFollowToggle}
            disabled={followBusy}
            className={`flex h-10 w-36 cursor-pointer items-center justify-center rounded-xl px-4 font-(family-name:--font-manrope) text-[14px] font-bold leading-none transition-colors disabled:opacity-60 ${
              following
                ? "bg-(--color-primary-200) text-white hover:bg-[#4B25E0]"
                : "border border-(--color-primary-200) text-(--color-primary-200) hover:bg-[#F1EEFE]"
            }`}
          >
            {followBusy ? "…" : following ? "Отследи" : "Следи"}
          </button>
        )}
      </div>
    </section>
  );
}
