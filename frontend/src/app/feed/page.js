import AppShell from "@/components/shell/AppShell";
import CommunityBanner from "@/components/shell/CommunityBanner";
import Threads from "@/components/thread/Threads";

export default function FeedPage() {
  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-8">
        <CommunityBanner />
        <Threads />
      </div>
    </AppShell>
  );
}
