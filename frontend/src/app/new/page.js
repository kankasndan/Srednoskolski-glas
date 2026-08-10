import Image from "next/image";
import BackButton from "@/components/shell/BackButton";
import Header from "@/components/shell/Header";
import NewDiscussionForm from "@/components/compose/NewDiscussionForm";
import NewPageFooter from "@/components/compose/NewPageFooter";

export default function NewDiscussionPage() {
  return (
    <div className="flex min-h-screen w-full flex-col overflow-x-clip bg-white">
      <Header />

      <div className="flex flex-1 flex-col items-center px-14 pb-8">
        <div className="flex w-fit max-w-full flex-col items-start gap-6">
          <BackButton />

          <div className="flex items-start gap-[152px]">
            <main
              aria-label="Започни дискусија"
              className="flex w-[632px] max-w-full min-w-0 flex-col gap-6"
            >
              <NewDiscussionForm />
            </main>

            <div
              aria-hidden="true"
              className="pointer-events-none mt-[192px] hidden shrink-0 select-none xl:block"
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
      </div>

      <NewPageFooter />
    </div>
  );
}
