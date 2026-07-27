import Image from "next/image";
import Link from "next/link";
import { formatPostedAgo } from "@/lib/time";

const SCHOOL_ICON = "/icons/uchilishte.svg";
const DEFAULT_AVATAR = "/Generic avatar.svg";

function getAttachmentImage(attachments = []) {
  const imageAttachment = attachments.find((attachment) => {
    const type = attachment.type ?? attachment.mime_type ?? attachment.mimeType ?? "";
    const url = attachment.imageUrl ?? attachment.url ?? attachment.path ?? "";

    return type.startsWith("image/") || /\.(avif|gif|jpe?g|png|webp|svg)$/i.test(url);
  });

  return imageAttachment?.imageUrl ?? imageAttachment?.url ?? imageAttachment?.path ?? null;
}

function normalizeThread(thread) {
  const authorLabel = thread.is_anonymous
    ? "Анонимен"
    : thread.author?.username ?? "Корисник";
  const school = thread.author?.school;
  const tags = [
    {
      label: authorLabel,
      icon: thread.is_anonymous ? DEFAULT_AVATAR : thread.author?.imageUrl ?? DEFAULT_AVATAR,
    },
  ];

  if (school?.name) {
    tags.push({
      label: school.city ? `${school.name}` : school.name,
      icon: SCHOOL_ICON,
    });
  }

  return {
    id: thread.id,
    tags,
    title: thread.title,
    excerpt: thread.description,
    postedAgo: formatPostedAgo(thread.created_at),
    comments: thread.comments_count,
    votes: thread.upvotes,
    image: getAttachmentImage(thread.attachments),
  };
}

function ForumTag({ tag }) {
  return (
    <span
      className={`flex h-6 shrink-0 items-center gap-1 rounded-md px-2 font-[family-name:var(--font-manrope)] text-[12px] font-bold leading-none text-black ${
        tag.tone === "highlight" ? "bg-[#F0E92F]" : "bg-[#F5F5F5]"
      }`}
    >
      {tag.icon ? (
        <span className="relative size-4 shrink-0 overflow-hidden">
          <Image
            src={tag.icon}
            alt=""
            width={tag.iconZoom ? 40 : 16}
            height={tag.iconZoom ? 40 : 16}
            className={`absolute left-1/2 top-1/2 max-w-none -translate-x-1/2 -translate-y-1/2 ${
              tag.iconZoom ? "size-10" : "size-4"
            }`}
          />
        </span>
      ) : null}
      {tag.label}
    </span>
  );
}

function TimestampTag({ label }) {
  return (
    <span className="flex h-6 shrink-0 items-center justify-center gap-2 rounded-md px-2 py-1 font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-4 tracking-normal text-[#595959]">
      <span className="flex h-4 items-center gap-1 whitespace-nowrap">
        {label}
      </span>
    </span>
  );
}

function ForumActionButton({ icon, label, count }) {
  return (
    <button
      type="button"
      aria-label={label}
      className="flex h-12 w-24 items-center justify-center gap-4 rounded-2xl border border-[#CCCCCC] px-4 py-2 text-black opacity-80"
    >
      <Image src={icon} alt="" width={24} height={24} className="size-6" />
      <span className="flex h-[19px] w-[17px] items-center font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none tracking-normal">
        {count}
      </span>
    </button>
  );
}

function ForumThread({ thread, forumSlug, priority }) {
  const openThreadLink = (
    <Link
      href={`/p/${forumSlug}/${thread.id}`}
      aria-label={thread.title}
      className="absolute inset-0 rounded-3xl"
    />
  );

  const content = (
    <div className="flex w-full items-start justify-between gap-8">
      <div className="flex min-h-[97px] w-[681px] max-w-[calc(100%-128px)] shrink-0 flex-col gap-4">
        <div className="flex h-6 max-w-full items-center gap-2 overflow-hidden">
          {thread.tags.map((tag) => (
            <ForumTag key={`${thread.id}-${tag.label}`} tag={tag} />
          ))}
          <TimestampTag label={thread.postedAgo} />
        </div>

        <div className="flex min-h-[57px] w-[681px] max-w-full flex-col gap-2">
          <h3 className="w-fit max-w-full overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-[27px] text-black">
            {thread.title}
          </h3>
          <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
            {thread.excerpt}
          </p>
        </div>
      </div>

      <div className="relative z-10 flex h-[104px] w-24 shrink-0 flex-col gap-2">
        <ForumActionButton
          icon="/Chevrons up.svg"
          label="Гласај нагоре"
          count={thread.votes}
        />
        <ForumActionButton
          icon="/chat-1-line.svg"
          label="Коментари"
          count={thread.comments}
        />
      </div>
    </div>
  );

  if (thread.image) {
    return (
      <article className="relative flex flex-col gap-4 items-start justify-center bg-transparent border-b border-b-[#CFE9ED] hover:bg-gray-50 p-4 rounded-3xl">
        {openThreadLink}
        <div className="w-full">{content}</div>
        <Image
          src={thread.image}
          alt=""
          width={990}
          height={421}
          className="h-[421px] w-full rounded-t-3xl rounded-b-2xl object-cover"
          priority={priority}
        />
      </article>
    );
  }

  return (
    <article className="relative flex items-start justify-center bg-transparent border-b border-b-[#CFE9ED] hover:bg-gray-50 p-4 rounded-3xl">
      {openThreadLink}
      {content}
    </article>
  );
}

export default function ForumThreadList({ forumName, forumSlug, threads }) {
  const normalizedThreads = threads.map(normalizeThread);
  const firstImageId = normalizedThreads.find((thread) => thread.image)?.id;

  return (
    <div className="flex w-[990px] max-w-full flex-col gap-6" aria-label={`Дискусии за ${forumName}`}>
      {normalizedThreads.map((thread) => (
        <ForumThread
          key={thread.id}
          thread={thread}
          forumSlug={forumSlug}
          priority={thread.id === firstImageId}
        />
      ))}
    </div>
  );
}
