import Header from "@/components/Header";
import SidebarNav from "@/components/SidebarNav";
import ThematicForums from "@/components/ThematicForums";
import SchoolForums from "@/components/SchoolForums";

export default function FeedPage() {
  return (
    <div className="min-h-screen w-full bg-white">
      <Header />
      <div className="flex px-14 pt-8">
        {/* Left navigation — pinned below the header, scrolls on its own */}
        <div className="sticky top-40 flex h-[calc(100vh-160px)] shrink-0">
          <aside className="overflow-y-auto overscroll-contain pr-14 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <SidebarNav />
            <ThematicForums />
            <SchoolForums />
          </aside>
          <div className="w-px shrink-0 rounded-2xl bg-[#CCCCCC]" />
        </div>
        <section className="flex-1 pl-14">
          {/* Main feed content */}
        </section>
      </div>
    </div>
  );
}
