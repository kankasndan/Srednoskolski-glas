import AppShell from "@/components/shell/AppShell";
import CommunityBanner from "@/components/shell/CommunityBanner";
import MobileFooter from "@/components/shell/MobileFooter";
import Threads from "@/components/thread/Threads";

export default function FeedPage() {
  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-3 md:max-w-[680px] lg:max-w-full lg:gap-8">
        <CommunityBanner />
        <Threads />
        <MobileFooter />
      </div>
    </AppShell>
  );
}
