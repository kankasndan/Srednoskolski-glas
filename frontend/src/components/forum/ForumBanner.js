"use client";

import Image from "next/image";
import { useState } from "react";
import FollowForumButton from "@/components/forum/FollowForumButton";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";
import { isRemoteAssetUrl, resolveBannerUrl } from "@/lib/banners";

const FALLBACK_ICON = "/icons/opshti_diskusii.svg";

export default function ForumBanner({
  title,
  description,
  icon,
  banner,
  slug,
  type,
  membersCount = 0,
  isFollowing = false,
}) {
  const bannerUrl = resolveBannerUrl({ bannerUrl: banner, slug, type });
  const iconSrc = icon || FALLBACK_ICON;
  const remoteIcon = isRemoteAssetUrl(iconSrc);
  const [memberTotal, setMemberTotal] = useState(membersCount);
  const isSchoolForum = type === "school";
  // Guests get no is_following from the API — hide the button until logged in.
  const canFollow = !isSchoolForum && typeof isFollowing === "boolean";

  return (
    <section className="w-[990px] max-w-full overflow-hidden rounded-3xl border border-[#CFE9ED] bg-[#DEDFD9]">
      <div
        className="h-[164px] w-full bg-[#DEDFD9] bg-cover bg-center"
        style={{ backgroundImage: `url("${bannerUrl}")` }}
      />

      <div className="flex min-h-[137px] items-center justify-between gap-6 border-t border-[#CFE9ED] bg-white p-6">
        <div className="flex min-w-0 items-center gap-6">
          <span className="relative size-20 shrink-0 overflow-hidden">
            {remoteIcon ? (
              <img
                src={iconSrc}
                alt=""
                className="absolute left-1/2 top-1/2 size-[150px] max-w-none -translate-x-1/2 -translate-y-1/2 object-cover"
              />
            ) : (
              <Image
                src={iconSrc}
                alt=""
                width={150}
                height={150}
                className="absolute left-1/2 top-1/2 size-[150px] max-w-none -translate-x-1/2 -translate-y-1/2"
                priority
              />
            )}
          </span>

          <div className="flex min-w-0 flex-col gap-2">
            <h1 className="font-[family-name:var(--font-oswald)] text-[20px] font-bold uppercase leading-[27px] text-black">
              {title}
            </h1>

            <p className="flex items-center gap-2 font-[family-name:var(--font-oswald)] text-[14px] font-bold leading-none text-black">
              <Image
                src="/user-heart-line.svg"
                alt=""
                width={16}
                height={16}
                className="size-4"
              />
              {memberTotal} членови
            </p>

            <p className="min-h-[22px] font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
              {description}
            </p>
          </div>
        </div>

        <div className="flex shrink-0 flex-col gap-2">
          <StartDiscussionButton />
          {canFollow ? (
            <FollowForumButton
              slug={slug}
              initialFollowing={isFollowing}
              onMembersCountChange={setMemberTotal}
            />
          ) : null}
        </div>
      </div>
    </section>
  );
}
