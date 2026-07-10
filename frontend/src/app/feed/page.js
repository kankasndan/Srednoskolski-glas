import Header from "@/components/Header";
import CommunityBanner from "@/components/CommunityBanner";
import FeedThreads from "@/components/FeedThreads";
import SidebarNav from "@/components/SidebarNav";

export default function FeedPage() {
  return (
    <div className="min-h-screen w-full bg-white">
      <Header />
      <div className="flex px-14 pt-8">
        <aside className="pr-14">
          <SidebarNav />
          {/* Тематски форуми section goes here next */}
        </aside>
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
