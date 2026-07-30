"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import AppShell from "@/components/AppShell";
import ProfileBanner from "@/components/ProfileBanner";
import ProfileTabs from "@/components/ProfileTabs";
import { useProfile } from "@/hooks/useProfile";
import { useProfileCounts } from "@/hooks/useProfileCounts";

export default function ProfileLayout({ children }) {
  const router = useRouter();
  const { user, loading, error } = useProfile();
  const counts = useProfileCounts();

  useEffect(() => {
    if (!loading && (error || !user)) {
      router.replace("/login");
    }
  }, [loading, error, user, router]);

  if (loading || error || !user) {
    return (
      <AppShell>
        <p className="font-(family-name:--font-manrope) text-[16px] text-[#595959]">
          Се вчитува…
        </p>
      </AppShell>
    );
  }

  return (
    <AppShell>
      <div className="flex w-247.5 max-w-full flex-col">
        <ProfileBanner user={user} />
        <div className="mt-18 flex flex-col gap-12">
          <ProfileTabs counts={counts} />
          {children}
        </div>
      </div>
    </AppShell>
  );
}
