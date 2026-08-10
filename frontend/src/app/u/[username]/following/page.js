"use client";

import { useParams } from "next/navigation";
import ProfileFollowedForums from "@/components/profile/ProfileFollowedForums";

export default function PublicProfileFollowingPage() {
  const params = useParams();
  const username = decodeURIComponent(params.username ?? "");

  return (
    <div className="flex flex-col gap-8">
      <section className="flex flex-col gap-4">
        <h2 className="font-(family-name:--font-manrope) text-[18px] font-bold text-black">
          Форуми што ги следи
        </h2>
        <ProfileFollowedForums username={username} isOwnProfile={false} />
      </section>
    </div>
  );
}
