import Image from "next/image";

const THREADS = [
  {
    id: 1,
    tags: [
      { label: "марко_2026", icon: "/Generic avatar.svg" },
      { label: "Гим. Орце Николов", icon: "/icons/uciliste.svg" },
    ],
    title: "Кои се најдобрите ресурси за подготовка на матура по математика?",
    excerpt: "Здраво. Барам совети за најдобри книги, видеа и онлајн материјали...",
    postedAgo: "пред 2ч.",
    comments: 42,
    votes: 87,
  },
  {
    id: 2,
    tags: [
      { label: "гоце_2027", icon: "/Generic avatar.svg" },
      { label: "Гим. Никола Карев", icon: "/icons/uciliste.svg" },
    ],
    title: "Дали матура по странски јазик е тешка ако имам B2?",
    excerpt: "Имам Cambridge B2 сертификат и размислувам да земам англиски на матура...",
    postedAgo: "пред 3д.",
    comments: 15,
    votes: 28,
    image: "/thread-placeholder.png",
  },
  {
    id: 3,
    tags: [
      { label: "Истакнато", tone: "highlight" },
      { label: "ана_матуранка", icon: "/Generic avatar.svg" },
      { label: "СУГС Михајло Пупин", icon: "/icons/uciliste.svg" },
    ],
    title: "[GUIDE] Како се справив со матурата по македонски - мојот метод",
    excerpt: "Завршив матура минатата година со 4.95. Сакам да го споделам начинот на кој...",
    postedAgo: "пред 5д.",
    comments: 89,
    votes: 312,
  },
];

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

export default function ForumThreadList() {
  return (
    <div className="flex w-[990px] max-w-full flex-col gap-6" aria-label="Дискусии за државна матура">
      {THREADS.map((thread) => (
        <ForumThread key={thread.id} thread={thread} />
      ))}
    </div>
  );
}
