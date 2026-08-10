"use client";

import { useParams } from "next/navigation";
import ProfileThreadList from "@/components/profile/ProfileThreadList";

export default function PublicProfileThreadsPage() {
  const params = useParams();
  const username = decodeURIComponent(params.username ?? "");

  return <ProfileThreadList username={username} isOwnProfile={false} />;
}
