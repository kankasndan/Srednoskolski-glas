"use client";

import Image from "next/image";
import Link from "next/link";
import { isRemoteAssetUrl } from "@/lib/banners";
import { authorProfileHref, forumHref, schoolForumHref } from "@/lib/profileLinks";

const SCHOOL_ICON = "/icons/uchilishte.svg";
const DEFAULT_AVATAR = "/Generic avatar.svg";

export function buildThreadMetaTags(forum, thread) {
  const forumData = thread?.forum ?? forum;

  const tags = [
    {
      key: "forum",
      label: forumData?.name ?? "Форум",
      icon: forumData?.imageUrl,
      zoom: true,
      href: forumHref(forumData),
    },
    {
      key: "author",
      label: thread.is_anonymous
        ? "Анонимен"
        : (thread.author?.username ?? "Корисник"),
      icon: thread.is_anonymous
        ? DEFAULT_AVATAR
        : (thread.author?.imageUrl ?? DEFAULT_AVATAR),
      avatar: true,
      href: thread.is_anonymous ? null : authorProfileHref(thread.author),
    },
  ];

  if (!thread.is_anonymous && thread.author?.school?.name) {
    tags.push({
      key: "school",
      label: thread.author.school.name,
      icon: SCHOOL_ICON,
      href: schoolForumHref(thread.author.school),
    });
  }

  return tags;
}

function MetaTag({ tag, hiddenOnMobile = false }) {
  const remoteIcon = isRemoteAssetUrl(tag.icon);
  const iconBoxClass = tag.avatar
    ? "relative size-4 shrink-0 overflow-hidden rounded-full lg:size-6"
    : "relative size-5 shrink-0 overflow-hidden";
  const iconClass = tag.avatar
    ? "size-4 object-cover lg:size-6"
    : tag.zoom
      ? "size-11"
      : "size-5";
  const iconSize = tag.avatar ? 24 : tag.zoom ? 44 : 20;

  const className = `relative z-10 ${
<<<<<<< HEAD
    hiddenOnMobile ? "hidden md:flex" : "flex"
  } h-7 shrink-0 cursor-pointer items-center gap-1.5 rounded-md bg-[#F5F5F5] px-2 text-[12px] font-bold leading-none text-black transition-colors hover:bg-[#EBEBEB]`;
=======
    hiddenOnMobile ? "hidden lg:flex" : "flex"
  } shrink-0 cursor-pointer items-center gap-2 rounded-md border-[0.5px] border-[#CCCCCC] bg-[#E5E5E5] px-2 py-1 font-[family-name:var(--font-roboto)] text-[12px] font-normal leading-[16px] text-black transition-colors hover:bg-[#EBEBEB] lg:h-7 lg:gap-1.5 lg:border-0 lg:bg-[#F5F5F5] lg:py-0 lg:font-sans lg:font-bold lg:leading-none`;
>>>>>>> 5397c7e03cc16a8b2de1ec7ef44a077c4821c45d

  const content = (
    <>
      {tag.icon ? (
        <span className={iconBoxClass}>
          {remoteIcon ? (
            <img
              src={tag.icon}
              alt=""
              className={`absolute left-1/2 top-1/2 max-w-none -translate-x-1/2 -translate-y-1/2 ${iconClass}`}
            />
          ) : (
            <Image
              src={tag.icon}
              alt=""
              width={iconSize}
              height={iconSize}
              className={`absolute left-1/2 top-1/2 max-w-none -translate-x-1/2 -translate-y-1/2 ${iconClass}`}
            />
          )}
        </span>
      ) : null}
      {tag.label}
    </>
  );

  if (tag.href) {
    return (
      <Link
        href={tag.href}
        className={className}
        onClick={(event) => event.stopPropagation()}
      >
        {content}
      </Link>
    );
  }

  return <span className={className.replace("cursor-pointer ", "")}>{content}</span>;
}

// Na mobilen karticata gi prikazuva samo forumot i vremeto.
export default function ThreadMetaTags({ tags, postedAgo, forumOnlyOnMobile = false }) {
  return (
    <div className="flex flex-wrap items-center gap-2">
      {tags.map((tag) => (
        <MetaTag
          key={tag.key ?? tag.label}
          tag={tag}
          hiddenOnMobile={forumOnlyOnMobile && tag.key !== "forum"}
        />
      ))}
      {postedAgo ? (
        <span className="font-[family-name:var(--font-roboto)] text-[12px] font-normal leading-[16px] text-[#595959] lg:font-sans lg:leading-none">
          {postedAgo}
        </span>
      ) : null}
    </div>
  );
}
