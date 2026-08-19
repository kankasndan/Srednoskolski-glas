"use client";

import { useEffect, useState } from "react";
import { format } from "date-fns";
import { mk } from "date-fns/locale";
import Image from "next/image";
import Link from "next/link";
import { followUser, unfollowUser } from "@/api/profile";
import CheckIcon from "@/components/ui/CheckIcon";
import LogoutDialogs from "@/components/shell/LogoutDialogs";
import { useLogout } from "@/hooks/useLogout";
import { useProfile } from "@/hooks/useProfile";
import { needsOnboarding, ONBOARDING_REQUIRED_MESSAGE } from "@/lib/capabilities";

const GUEST_FOLLOW_ERROR = "Мора да си најавен за да следиш корисник.";

const GRADE_LABELS = {
  1: "1ва",
  2: "2ра",
  3: "3та",
  4: "4та",
};

function Chip({ children, href, className = "" }) {
  const base = `flex items-center gap-2 rounded-md border-[0.5px] border-(--color-grays-300) bg-(--color-grays-200) px-2 py-1 font-(family-name:--font-roboto) text-[12px] leading-4 text-[#404040] ${className}`;

  if (href) {
    return (
      <Link
        href={href}
        className={`${base} cursor-pointer transition-colors hover:border-(--color-primary-200) hover:bg-[#F1EEFE] hover:text-(--color-primary-200) active:border-(--color-primary-200) active:bg-[#F1EEFE] active:text-(--color-primary-200)`}
      >
        {children}
      </Link>
    );
  }

  return <span className={base}>{children}</span>;
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
  const { user: viewer, loading: viewerLoading } = useProfile();
  const logout = useLogout();
  const [following, setFollowing] = useState(Boolean(isFollowing));
  const [followBusy, setFollowBusy] = useState(false);
  const [followError, setFollowError] = useState("");

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

  async function handleFollowToggle() {
    if (!user?.username || followBusy) return;

    setFollowError("");

    if (!viewerLoading && viewer == null) {
      setFollowError(GUEST_FOLLOW_ERROR);
      return;
    }

    if (!viewerLoading && needsOnboarding(viewer)) {
      setFollowError(ONBOARDING_REQUIRED_MESSAGE);
      return;
    }

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
        setFollowError(GUEST_FOLLOW_ERROR);
      } else if (err?.status === 403) {
        setFollowError(err.message || ONBOARDING_REQUIRED_MESSAGE);
      }
    } finally {
      setFollowBusy(false);
    }
  }

  return (
    <section className="flex flex-col gap-6 rounded-3xl border border-[#CFE9ED] bg-white p-6 md:flex-row md:items-center md:justify-between">
      {/* Na telefon avatarot i imeto se vo prv red, a tagovite pod niv. */}
      <div className="grid min-w-0 grid-cols-[auto_1fr] items-center gap-x-6 gap-y-4">
        {/^https?:\/\//i.test(user.imageUrl || "") ? (
          <img
            src={user.imageUrl}
            alt={user.username}
            width={88}
            height={88}
            className="size-22 shrink-0 rounded-full object-cover md:row-span-2"
          />
        ) : (
          <Image
            src={user.imageUrl || "/Generic-avatar-profile.svg"}
            alt={user.username}
            width={88}
            height={88}
            className="size-22 shrink-0 rounded-full object-cover md:row-span-2"
          />
        )}

        <div className="flex min-w-0 flex-col gap-1">
          <h1 className="font-(family-name:--font-oswald) text-[20px] font-bold leading-none text-black">
            {user.username}
          </h1>
          {joined ? (
            <p className="font-(family-name:--font-manrope) text-[12px] leading-none text-(--color-grays-700) md:hidden">
              {joined}
            </p>
          ) : null}
        </div>

        <div className="col-span-2 flex min-w-0 flex-wrap items-center gap-2 md:col-span-1 md:col-start-2">
          {schoolLabel ? <Chip href={schoolHref}>{schoolLabel}</Chip> : null}
          {gradeLabel ? <Chip>{gradeLabel}</Chip> : null}
          {vocation ? <Chip className="hidden md:flex">{vocation}</Chip> : null}
          {joined ? <Chip className="hidden md:flex">{joined}</Chip> : null}
        </div>
      </div>

      {isOwnProfile ? (
        <>
          <div className="flex shrink-0 gap-2 md:flex-col-reverse">
            <button
              type="button"
              onClick={logout.ask}
              disabled={logout.loggingOut}
              className="flex h-10 flex-1 cursor-pointer items-center justify-center rounded-xl border border-(--color-primary-200) px-4 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#F1EEFE] active:bg-(--color-primary-200) active:text-white disabled:cursor-not-allowed md:active:bg-[#F1EEFE] md:active:text-(--color-primary-200) disabled:opacity-60 md:w-36 md:flex-none md:text-(--color-primary-200)"
            >
              {logout.loggingOut ? "Се одјавува…" : "Одјави се"}
            </button>
            <Link
              href="/profile/edit"
              className="flex h-10 flex-1 cursor-pointer items-center justify-center rounded-xl bg-(--color-primary-200) px-4 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-white transition-colors hover:bg-(--color-primary-300) active:bg-(--color-primary-300) md:w-36 md:flex-none md:border md:border-(--color-primary-200) md:bg-transparent md:text-(--color-primary-200) md:hover:bg-[#F1EEFE] md:active:bg-[#F1EEFE]"
            >
              Уреди профил
            </Link>
          </div>

          <LogoutDialogs logout={logout} />
        </>
      ) : (
        <div className="flex w-full shrink-0 flex-col gap-1 md:w-36">
          <button
            type="button"
            onClick={handleFollowToggle}
            disabled={followBusy}
            className={`flex h-10 w-full cursor-pointer items-center justify-center gap-3 rounded-xl px-4 font-(family-name:--font-manrope) text-[14px] font-bold leading-none transition-colors disabled:opacity-60 ${
              following
                ? "bg-(--color-primary-300) text-white"
                : "bg-(--color-primary-200) text-white hover:bg-(--color-primary-300) active:bg-(--color-primary-300)"
            }`}
          >
            {following && !followBusy && <CheckIcon />}
            <span className="flex h-[19px] items-center leading-none">
              {followBusy ? "…" : following ? "Следиш" : "Следи"}
            </span>
          </button>
          {followError ? (
            <p className="font-(family-name:--font-manrope) text-[12px] leading-4 text-[#DC2626]">
              {followError}
            </p>
          ) : null}
        </div>
      )}
    </section>
  );
}
