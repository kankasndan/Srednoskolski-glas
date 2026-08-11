import ThreadActionButton from "@/components/thread/ThreadActionButton";

export default function ThreadStatButtons({ thread, href }) {
  return (
    <div className="relative z-10 flex shrink-0 self-center flex-col gap-2">
      <ThreadActionButton
        icon="/Chevrons up.svg"
        label="Гласови"
        count={thread.upvotes ?? 0}
        href={href}
      />
      <ThreadActionButton
        icon="/chat-1-line.svg"
        label="Коментари"
        count={thread.comments_count ?? 0}
        href={href}
      />
    </div>
  );
}
