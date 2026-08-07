import Image from "next/image";
import { isRemoteAssetUrl } from "@/lib/banners";

const SCHOOL_ICON = "/icons/uchilishte.svg";
const DEFAULT_AVATAR = "/Generic avatar.svg";

export function buildThreadMetaTags(forum, thread) {
  const tags = [
    { label: forum?.name ?? "Форум", icon: forum?.imageUrl, zoom: true },
    {
      label: thread.is_anonymous
        ? "Анонимен"
        : (thread.author?.username ?? "Корисник"),
      icon: thread.is_anonymous
        ? DEFAULT_AVATAR
        : (thread.author?.imageUrl ?? DEFAULT_AVATAR),
      avatar: true,
    },
  ];

  if (!thread.is_anonymous && thread.author?.school?.name) {
    tags.push({ label: thread.author.school.name, icon: SCHOOL_ICON });
  }

  return tags;
}

function MetaTag({ tag }) {
  const remoteIcon = isRemoteAssetUrl(tag.icon);
  const iconBoxClass = tag.avatar
    ? "relative size-6 shrink-0 overflow-hidden rounded-full"
    : "relative size-5 shrink-0 overflow-hidden";
  const iconClass = tag.avatar
    ? "size-6 object-cover"
    : tag.zoom
      ? "size-11"
      : "size-5";
  const iconSize = tag.avatar ? 24 : tag.zoom ? 44 : 20;

  return (
    <span className="flex h-7 shrink-0 items-center gap-1.5 rounded-md bg-[#F5F5F5] px-2 text-[12px] font-bold leading-none text-black">
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
    </span>
  );
}

export default function ThreadMetaTags({ tags, postedAgo }) {
  return (
    <div className="flex flex-wrap items-center gap-2">
      {tags.map((tag) => (
        <MetaTag key={tag.label} tag={tag} />
      ))}
      {postedAgo ? (
        <span className="text-[12px] leading-none text-[#595959]">
          {postedAgo}
        </span>
      ) : null}
    </div>
  );
}
