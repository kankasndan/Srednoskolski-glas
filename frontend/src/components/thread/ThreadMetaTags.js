"use client";

import Image from "next/image";
import Link from "next/link";
import { isRemoteAssetUrl } from "@/lib/banners";
import { authorProfileHref, forumHref, schoolForumHref } from "@/lib/profileLinks";

const SCHOOL_ICON = "/icons/uchilishte.svg";
const DEFAULT_AVATAR = "/Generic avatar.svg";

function timestampsDiffer(first, second) {
  if (!first || !second) return false;

  const firstTime = new Date(first).getTime();
  const secondTime = new Date(second).getTime();

  if (Number.isNaN(firstTime) || Number.isNaN(secondTime)) {
    return first !== second;
  }

  return Math.abs(firstTime - secondTime) > 1000;
}

function wasThreadEdited(thread) {
  if (!thread) return false;
  if (Boolean(thread.is_edited)) return true;
  if (thread.edited_at) return true;

  return timestampsDiffer(thread.updated_at, thread.created_at);
}

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

  if (wasThreadEdited(thread)) {
    tags.push({
      key: "edited",
      label: "изменето",
    });
  }

  return tags;
}

function MetaTag({ tag, hiddenOnMobile = false, hiddenOnPhone = false }) {
  const remoteIcon = isRemoteAssetUrl(tag.icon);
  const iconBoxClass = tag.avatar
    ? "relative size-5 shrink-0 overflow-hidden rounded-full md:size-6"
    : "relative size-4 shrink-0 overflow-hidden md:size-5";
  const iconClass = tag.avatar
    ? "size-5 object-cover md:size-6"
    : tag.zoom
      ? "size-9 md:size-11"
      : "size-4 md:size-5";
  const iconSize = tag.avatar ? 24 : tag.zoom ? 44 : 20;
  const displayClass = hiddenOnMobile
    ? "hidden lg:flex"
    : hiddenOnPhone
      ? "hidden md:flex"
      : "flex";

  const className = `relative z-10 ${displayClass} h-6 max-w-[150px] shrink-0 cursor-pointer items-center gap-1 rounded-md bg-[#F5F5F5] px-1.5 text-[11px] font-bold leading-none text-black transition-colors hover:bg-[#EBEBEB] md:h-7 md:max-w-none md:gap-1.5 md:px-2 md:text-[12px]`;

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
      <span className="truncate">{tag.label}</span>
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
export default function ThreadMetaTags({
  tags,
  postedAgo,
  forumOnlyOnMobile = false,
  hideForumOnPhone = false,
}) {
  return (
    <div className="flex min-w-0 flex-wrap items-center gap-1.5 md:gap-2">
      {tags.map((tag) => (
        <MetaTag
          key={tag.key ?? tag.label}
          tag={tag}
          hiddenOnMobile={forumOnlyOnMobile && tag.key !== "forum"}
          hiddenOnPhone={hideForumOnPhone && tag.key === "forum"}
        />
      ))}
      {postedAgo ? (
        <span className="shrink-0 text-[11px] leading-none text-[#595959] md:text-[12px]">
          {postedAgo}
        </span>
      ) : null}
    </div>
  );
}
