import Header from "@/components/Header";
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
        <section className="flex-1">
          {/* Main feed content */}
        </section>
      </div>
    </div>
  );
}
