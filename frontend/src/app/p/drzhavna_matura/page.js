import AppShell from "@/components/AppShell";
import ForumBanner from "@/components/ForumBanner";
import ForumFilters from "@/components/ForumFilters";
import ForumThreadList from "@/components/ForumThreadList";

export default function DrzhavnaMaturaPage() {
  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-6">
        <ForumBanner
          title="Државна матура"
          description="Сè за државна матура - подготовка, литература, искуства."
          icon="/icons/drzavna_matura.svg"
        />
        <ForumFilters />
        <ForumThreadList />
      </div>
    </AppShell>
  );
}
