import AppShell from "@/components/AppShell";
import CommunityBanner from "@/components/CommunityBanner";
import Threads from "@/components/Threads";

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
