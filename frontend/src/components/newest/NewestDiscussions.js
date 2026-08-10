import AppShell from "@/components/shell/AppShell";
import Threads from "@/components/thread/Threads";
import newestDiscussions from "../../../public/newest-discussions-mock.json";

export default function NewestDiscussions() {
  const threads = Array.isArray(newestDiscussions.data) ? newestDiscussions.data : [];

  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-8">
        <div className="flex flex-col ml-4">
          <h1 className="font-[family-name:var(--font-manrope)] text-[24px] font-bold text-[#582FF5] tracking-normal">
            Најнови дискусии
          </h1>
          <p className="font-[family-name:var(--font-manrope)] max-w-[720px] text-[16px] text-[#595959] tracking-normal">
            Погледни што е ново и приклучи се на актуелните дискусии.
          </p>
        </div>

        <Threads
          defaultSort="newest"
          staticThreads={threads}
        />
      </div>
    </AppShell>
  );
}
