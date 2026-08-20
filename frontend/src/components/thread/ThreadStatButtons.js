import ThreadActionButton from "@/components/thread/ThreadActionButton";
import ThreadViewCount from "@/components/thread/ThreadViewCount";

// Na telefon statistikite stojat vo red pod tekstot, na pogolem ekran vo kolona strana.
export default function ThreadStatButtons({ thread, href }) {
  return (
    <div className="relative z-10 flex shrink-0 items-center gap-2 md:flex-col md:self-center">
      <ThreadActionButton
        compact
        icon="/Chevrons up.svg"
        label="Гласови"
        count={thread.upvotes ?? 0}
        href={href}
      />
      <ThreadActionButton
        compact
        icon="/chat-1-line.svg"
        label="Коментари"
        count={thread.comments_count ?? 0}
        href={href}
      />
      {/* Fiksna shirina za tri cifri, za redot da ne skoka. */}
      <ThreadViewCount views={thread.views} className="w-11 md:hidden" />
    </div>
  );
}
