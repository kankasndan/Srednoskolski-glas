import AppShell from "@/components/shell/AppShell";
import CommunityBanner from "@/components/shell/CommunityBanner";
import MobileFooter from "@/components/shell/MobileFooter";
import Threads from "@/components/thread/Threads";

export default function FeedPage() {
  return (
    <AppShell>
      <div className="flex w-[1100px] max-w-full flex-col gap-3 lg:gap-8">
        <CommunityBanner />
        <Threads />
        <MobileFooter />
      </div>
    </AppShell>
  );
}
