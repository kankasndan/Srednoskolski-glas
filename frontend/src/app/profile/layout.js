"use client";

import { useEffect } from "react";
import { usePathname, useRouter } from "next/navigation";
import AppShell from "@/components/shell/AppShell";
import ProfileBanner from "@/components/profile/ProfileBanner";
import ProfileTabs from "@/components/profile/ProfileTabs";
import { useProfile } from "@/hooks/useProfile";
import { useProfileCounts } from "@/hooks/useProfileCounts";

export default function ProfileLayout({ children }) {
  const router = useRouter();
  const pathname = usePathname();
  const { user, loading, error } = useProfile();
  const counts = useProfileCounts();
  const isEditPage = pathname === "/profile/edit";

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

  if (isEditPage) {
    return <AppShell>{children}</AppShell>;
  }

  return (
    <AppShell>
      <div className="flex w-[1100px] max-w-full flex-col">
        <ProfileBanner user={user} isOwnProfile />
        <div className="mt-10 flex flex-col gap-10 md:mt-8 md:gap-8">
          <ProfileTabs counts={counts} basePath="/profile" isOwnProfile />
          {children}
        </div>
      </div>
    </AppShell>
  );
}
