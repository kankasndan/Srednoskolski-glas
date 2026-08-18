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
  isOwnSchoolForum = false,
}) {
  const bannerUrl = resolveBannerUrl({ bannerUrl: banner, slug, type });
  const iconSrc = icon || FALLBACK_ICON;
  const remoteIcon = isRemoteAssetUrl(iconSrc);
  const [memberTotal, setMemberTotal] = useState(membersCount);
  const lockOwnSchool = Boolean(isOwnSchoolForum);

  return (
    <section className="w-[1100px] max-w-full overflow-hidden rounded-3xl border border-[#CFE9ED] bg-[#DEDFD9]">
      <div
        className="h-36 w-full bg-[#DEDFD9] bg-cover bg-center lg:h-[164px]"
        style={{ backgroundImage: `url("${bannerUrl}")` }}
      />

      <div className="flex min-h-[137px] flex-col gap-4 border-t border-[#CFE9ED] bg-white p-4 lg:flex-row lg:items-center lg:justify-between lg:gap-6 lg:p-6">
        <div className="flex min-w-0 flex-col gap-3 lg:flex-row lg:items-center lg:gap-6">
          <div className="flex min-w-0 items-center gap-4 lg:hidden">
            <div className="flex min-w-0 shrink items-center gap-3">
              <span className="size-10 shrink-0 translate-y-0.5">
                {remoteIcon ? (
                  <img src={iconSrc} alt="" className="size-full object-contain" />
                ) : (
                  <Image
                    src={iconSrc}
                    alt=""
                    width={40}
                    height={40}
                    className="size-full object-contain"
                  />
                )}
              </span>

              <h1 className="min-w-0 truncate font-[family-name:var(--font-oswald)] text-[20px] font-bold uppercase leading-none text-black">
                {title}
              </h1>
            </div>

            <p className="flex shrink-0 items-center gap-1 font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-none text-(--color-grays-900)">
              <Image src="/user-heart-line.svg" alt="" width={14} height={14} className="size-3.5" />
              {memberTotal}
            </p>
          </div>

          <p className="font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-[20px] text-[#595959] lg:hidden">
            {description}
          </p>

          <span className="hidden shrink-0 lg:block lg:size-20">
            {remoteIcon ? (
              <img src={iconSrc} alt="" className="size-full object-contain" />
            ) : (
              <Image
                src={iconSrc}
                alt=""
                width={80}
                height={80}
                className="size-full object-contain"
                priority
              />
            )}
          </span>

          <div className="hidden min-w-0 flex-col gap-2 lg:flex">
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

        <div className="flex shrink-0 flex-row items-center gap-2 lg:flex-col">
          <StartDiscussionButton />
          <FollowForumButton
            slug={slug}
            initialFollowing={Boolean(isFollowing) || lockOwnSchool}
            locked={lockOwnSchool}
            onMembersCountChange={setMemberTotal}
          />
        </div>
      </div>
    </section>
  );
}
