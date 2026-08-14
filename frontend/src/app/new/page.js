import Image from "next/image";
import BackButton from "@/components/shell/BackButton";
import Header from "@/components/shell/Header";
import NewDiscussionForm from "@/components/compose/NewDiscussionForm";
import NewPageFooter from "@/components/compose/NewPageFooter";

export default function NewDiscussionPage() {
  return (
    <div className="flex min-h-screen w-full flex-col overflow-x-clip bg-white">
      <Header />

      <div className="flex flex-1 flex-col items-stretch px-14 pb-8">
        <div className="self-start">
          <BackButton label="Врати се назад" />
        </div>
        <div className="mt-6 grid w-full grid-cols-1 items-start xl:grid-cols-2">
          <main
            aria-label="Започни дискусија"
            className="flex w-full min-w-0 flex-col gap-6 xl:pr-8"
          >
            <NewDiscussionForm />
          </main>

          <div
            aria-hidden="true"
            className="pointer-events-none hidden min-h-[520px] select-none xl:flex xl:items-center xl:justify-center"
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

      <NewPageFooter />
    </div>
  );
}
