"use client";

import Image from "next/image";
import Link from "next/link";
import { isRemoteAssetUrl } from "@/lib/banners";
import { authorProfileHref, forumHref, schoolCityLabel, schoolForumHref } from "@/lib/profileLinks";

const DEFAULT_AVATAR = "/Generic avatar.svg";

function isPostedOnAuthorSchool(forum, thread) {
  if (forum?.type !== "school") {
    return false;
  }

  const schoolForum = thread?.author?.school?.forum;
  if (schoolForum?.id != null && forum.id != null) {
    return Number(schoolForum.id) === Number(forum.id);
  }
  if (schoolForum?.slug && forum.slug) {
    return schoolForum.slug === forum.slug;
  }

  return true;
}

export function buildThreadMetaTags(forum, thread) {
  const forumData = thread?.forum ?? forum;

  const tags = [
    {
      key: "forum",
      label: forumData?.name ?? "Форум",
      icon: forumData?.imageUrl,
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

  const showAuthorSchool =
    !thread.is_anonymous &&
    Boolean(thread.author?.school?.name) &&
    !isPostedOnAuthorSchool(forumData, thread);

  if (showAuthorSchool) {
    tags.push({
      key: "school",
      label: schoolCityLabel(thread.author.school),
      href: schoolForumHref(thread.author.school),
    });
  }

  return tags;
}

function MetaTag({ tag, hiddenOnMobile = false, hiddenOnPhone = false }) {
  const remoteIcon = isRemoteAssetUrl(tag.icon);
  const iconBoxClass = tag.avatar
    ? "size-5 shrink-0 overflow-hidden rounded-full md:size-6"
    : "size-4 shrink-0 md:size-5";
  const iconClass = tag.avatar
    ? "size-full object-cover"
    : "size-full object-contain";
  const iconSize = tag.avatar ? 24 : 20;
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
            <img src={tag.icon} alt="" className={iconClass} />
          ) : (
            <Image
              src={tag.icon}
              alt=""
              width={iconSize}
              height={iconSize}
              className={iconClass}
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
