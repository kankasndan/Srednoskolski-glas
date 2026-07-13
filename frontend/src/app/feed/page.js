import Header from "@/components/Header";
import CommunityBanner from "@/components/CommunityBanner";
import FeedThreads from "@/components/FeedThreads";
import SidebarNav from "@/components/SidebarNav";
import ThematicForums from "@/components/ThematicForums";
import SchoolForums from "@/components/SchoolForums";

export default function FeedPage() {
  return (
    <div className="min-h-screen w-full bg-white">
      <Header />
      <div className="flex px-14 pt-8">
        <div className="sticky top-40 flex h-[calc(100vh-160px)] shrink-0">
          <aside className="overflow-y-auto overscroll-contain pr-14 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <SidebarNav />
            <ThematicForums />
            <SchoolForums />
          </aside>
          <div className="w-px shrink-0 rounded-2xl bg-[#CCCCCC]" />
        </div>
        <div className="w-px self-stretch rounded-2xl bg-[#CCCCCC]" />
        <section className="flex flex-1 justify-center pl-8">
          <div className="flex w-[990px] max-w-full flex-col gap-8">
            <CommunityBanner />
            <FeedThreads />
          </div>
        </section>
      </div>
    </div>
  );
}
