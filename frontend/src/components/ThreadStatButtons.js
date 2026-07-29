import ThreadActionButton from "@/components/ThreadActionButton";

export default function ThreadStatButtons({ thread, href }) {
  return (
    <div className="relative z-10 flex shrink-0 flex-col gap-2">
      <ThreadActionButton
        icon="/eye-line.svg"
        label="Прегледи"
        count={thread.views ?? 0}
        href={href}
      />
      <ThreadActionButton
        icon="/chat-1-line.svg"
        label="Коментари"
        count={thread.comments_count ?? 0}
        href={href}
      />
      <ThreadActionButton
        icon="/Chevrons up.svg"
        label="Гласови"
        count={thread.upvotes ?? 0}
        href={href}
      />
    </div>
  );
}
