import Image from "next/image";

const SCHOOL_ICON = "/icons/uchilishte.svg";
const DEFAULT_AVATAR = "/Generic avatar.svg";

function formatPostedAgo(value) {
  const createdAt = new Date(value);

  if (Number.isNaN(createdAt.getTime())) {
    return "";
  }

  const diffMs = Date.now() - createdAt.getTime();
  const diffMinutes = Math.max(0, Math.floor(diffMs / 60000));

  if (diffMinutes < 60) {
    return `пред ${Math.max(1, diffMinutes)}м.`;
  }

  const diffHours = Math.floor(diffMinutes / 60);

  if (diffHours < 24) {
    return `пред ${diffHours}ч.`;
  }

  return `пред ${Math.floor(diffHours / 24)}д.`;
}

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
            width={16}
            height={16}
            className="absolute left-1/2 top-1/2 size-4 -translate-x-1/2 -translate-y-1/2"
          />
        </span>
      ) : null}
      {tag.label}
    </span>
  );
}

function ForumActionButton({ icon, label, count }) {
  return (
    <button
      type="button"
      aria-label={label}
      className="flex h-10 w-24 items-center justify-center gap-4 rounded-[10px] border border-[#CCCCCC] px-4 py-2 text-[#582FF5] transition-colors hover:border-[#582FF5] hover:bg-[#F5F2FF]"
    >
      <Image src={icon} alt="" width={24} height={24} className="size-6" />
      <span className="flex h-[19px] min-w-[17px] items-center justify-center font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none tracking-normal text-[#595959]">
        {count}
      </span>
    </button>
  );
}

function ForumThread({ thread }) {
  const articleClassName = thread.image
    ? "relative flex w-[990px] max-w-full flex-col gap-6 bg-transparent pb-8 after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:rounded-full after:bg-[#CFE9ED]"
    : "relative flex w-[990px] max-w-full flex-col gap-6 bg-transparent pt-6 pb-7 after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:rounded-full after:bg-[#CFE9ED]";

  return (
    <article className={articleClassName}>
      {thread.image ? (
        <Image
          src={thread.image}
          alt=""
          width={990}
          height={421}
          className="h-[421px] w-full rounded-t-3xl object-cover"
          priority
        />
      ) : null}

      <div className="flex w-full items-start justify-between gap-8">
        <div className="flex h-[97px] w-[681px] max-w-[calc(100%-128px)] shrink-0 flex-col gap-4">
          <div className="flex h-6 max-w-full items-center gap-2 overflow-hidden">
            {thread.tags.map((tag) => (
              <ForumTag key={`${thread.id}-${tag.label}`} tag={tag} />
            ))}
            <span className="shrink-0 font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-none tracking-normal text-[#595959]">
              {thread.postedAgo}
            </span>
          </div>

          <div className="flex max-w-full flex-col gap-2">
            <h3 className="overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-none tracking-normal text-black">
              {thread.title}
            </h3>
            <p className="overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
              {thread.excerpt}
            </p>
          </div>
        </div>

        <div className="flex w-24 shrink-0 flex-col gap-2">
          <ForumActionButton icon="/chat-1-line.svg" label="Коментари" count={thread.comments} />
          <ForumActionButton icon="/Chevrons up.svg" label="Гласај нагоре" count={thread.votes} />
        </div>
      </div>
    </article>
  );
}

export default function ForumThreadList({ forumName, threads }) {
  const normalizedThreads = threads.map(normalizeThread);

  return (
    <div className="flex w-[990px] max-w-full flex-col gap-6" aria-label={`Дискусии за ${forumName}`}>
      {normalizedThreads.map((thread) => (
        <ForumThread key={thread.id} thread={thread} />
      ))}
    </div>
  );
}
