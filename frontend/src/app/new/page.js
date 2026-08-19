import Image from "next/image";
import BackButton from "@/components/shell/BackButton";
import AppShell from "@/components/shell/AppShell";
import NewDiscussionForm from "@/components/compose/NewDiscussionForm";

export default function NewDiscussionPage() {
  return (
    <AppShell contentClassName="!flex-col !items-stretch !justify-start !px-0 !pb-0 !pt-0">
      <div className="flex w-full flex-col items-stretch px-6 lg:-mx-6 lg:px-14">
        <div className="self-start">
          <BackButton
            label="Врати се назад"
            className="font-normal text-[#582FF5] lg:font-medium"
            iconClassName="size-6 lg:h-4 lg:w-auto"
          />
        </div>
        <div className="mt-6 grid w-full grid-cols-1 items-start pb-6 lg:pb-8 xl:grid-cols-2">
          <main
            aria-label="Започни дискусија"
            className="flex w-full min-w-0 flex-col gap-6 xl:pr-8"
          >
            <NewDiscussionForm />
          </main>

          <div
            aria-hidden="true"
            className="pointer-events-none hidden select-none xl:flex xl:items-start xl:justify-center"
          >
            <Image
              src="/avatar.svg"
              alt=""
              width={395}
              height={366}
              priority
              className="h-[366px] w-[395px]"
            />
          </div>
        </div>
      </div>
    </AppShell>
  );
}
