"use client";

import { useEffect } from "react";
import { notFound, useParams, useRouter } from "next/navigation";
import AppShell from "@/components/shell/AppShell";
import ProfileBanner from "@/components/profile/ProfileBanner";
import ProfileTabs from "@/components/profile/ProfileTabs";
import { usePublicProfile } from "@/hooks/usePublicProfile";

function StatusMessage({ children }) {
  return (
    <p className="font-(family-name:--font-manrope) text-[16px] text-[#595959]">
      {children}
    </p>
  );
}

export default function PublicProfileLayout({ children }) {
  const router = useRouter();
  const params = useParams();
  const username = decodeURIComponent(params.username ?? "");
  const {
    user,
    counts,
    isFollowing,
    isOwnProfile,
    loading,
    error,
    missing,
    patchFollow,
  } = usePublicProfile(username);

  useEffect(() => {
    if (isOwnProfile && username) {
      router.replace("/profile");
    }
  }, [isOwnProfile, username, router]);

  if (loading || isOwnProfile) {
    return (
      <AppShell>
        <StatusMessage>Се вчитува…</StatusMessage>
      </AppShell>
    );
  }

  if (missing) {
    notFound();
  }

  if (error || !user) {
    return (
      <AppShell>
        <StatusMessage>Не успеа вчитувањето на профилот.</StatusMessage>
      </AppShell>
    );
  }

  const basePath = `/u/${encodeURIComponent(username)}`;

  return (
    <AppShell>
      <div className="flex w-247.5 max-w-full flex-col">
        <ProfileBanner
          user={user}
          isOwnProfile={false}
          isFollowing={isFollowing}
          onFollowChange={patchFollow}
        />
        <div className="mt-18 flex flex-col gap-12">
          <ProfileTabs
            counts={counts}
            basePath={basePath}
            isOwnProfile={false}
          />
          {children}
        </div>
      </div>
    </AppShell>
  );
}
