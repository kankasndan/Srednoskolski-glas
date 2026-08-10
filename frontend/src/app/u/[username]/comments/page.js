"use client";

import { useParams } from "next/navigation";
import ProfileCommentList from "@/components/profile/ProfileCommentList";

export default function PublicProfileCommentsPage() {
  const params = useParams();
  const username = decodeURIComponent(params.username ?? "");

  return <ProfileCommentList username={username} isOwnProfile={false} />;
}
