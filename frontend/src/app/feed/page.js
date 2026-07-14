import AppShell from "@/components/AppShell";
import CommunityBanner from "@/components/CommunityBanner";
import FeedThreads from "@/components/FeedThreads";

export default function FeedPage() {
  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-8">
        <CommunityBanner />
        <FeedThreads />
      </div>
    </AppShell>
  );
}
