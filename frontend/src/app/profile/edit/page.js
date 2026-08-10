"use client";

import BackButton from "@/components/shell/BackButton";
import EditProfileForm from "@/components/profile/EditProfileForm";
import { useProfile } from "@/hooks/useProfile";

export default function EditProfilePage() {
  const { user, loading } = useProfile();

  if (loading || !user) {
    return (
      <p className="font-(family-name:--font-manrope) text-[16px] text-[#595959]">
        Се вчитува…
      </p>
    );
  }

  return (
    <div className="flex w-[990px] max-w-full flex-col gap-6">
      <BackButton href="/profile" label="Назад кон профил" tone="muted" />
      <EditProfileForm user={user} />
    </div>
  );
}
