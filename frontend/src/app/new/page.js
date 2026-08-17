import Image from "next/image";
import BackButton from "@/components/shell/BackButton";
import AppShell from "@/components/shell/AppShell";
import NewDiscussionForm from "@/components/compose/NewDiscussionForm";
import NewPageFooter from "@/components/compose/NewPageFooter";
import MobileFooter from "@/components/shell/MobileFooter";

export default function NewDiscussionPage() {
  return (
    <AppShell contentClassName="!flex-col !items-stretch !justify-start !px-0 !pb-0 !pt-0">
      <div className="-mx-4 flex min-h-full flex-1 flex-col items-stretch px-6 pb-8 lg:-mx-6 lg:px-14">
        <div className="self-start">
          <BackButton
            label="Врати се назад"
            className="font-normal text-[#582FF5] lg:font-medium"
            iconClassName="size-6 lg:h-4 lg:w-auto"
          />
        </div>
        <div className="mt-6 grid w-full grid-cols-1 items-start lg:grid-cols-2">
          <main
            aria-label="Започни дискусија"
            className="flex w-full min-w-0 flex-col gap-6 lg:pr-6 xl:pr-8"
          >
            <NewDiscussionForm />
          </main>

          <div
            aria-hidden="true"
            className="pointer-events-none hidden min-h-[420px] select-none lg:flex lg:items-center lg:justify-center xl:min-h-[520px]"
          >
            <Image
              src="/avatar.svg"
              alt=""
              width={395}
              height={366}
              priority
              className="h-[280px] w-[302px] xl:h-[366px] xl:w-[395px]"
            />
          </div>
        </div>
        <MobileFooter className="mt-auto pt-8" hideAtClassName="xl:hidden" />
      </div>
      <NewPageFooter />
    </AppShell>
  );
}
