import BackButton from "@/components/BackButton";
import Header from "@/components/Header";
import Image from "next/image";
import NewDiscussionForm from "@/components/NewDiscussionForm";
import NewPageFooter from "@/components/NewPageFooter";

export default function NewDiscussionPage() {
  return (
    <div className="relative min-h-screen w-full overflow-x-hidden bg-white">
      <Header />
      <Image
        src="/avatar.svg"
        alt=""
        width={395}
        height={366}
        aria-hidden="true"
        className="pointer-events-none absolute left-[840px] top-[280px] hidden h-[366px] w-[395px] select-none xl:block"
        priority
      />
      <div className="relative z-10 flex flex-col items-start gap-6 px-14">
        <BackButton label="Врати се назад" />
        <NewDiscussionForm />
        <NewPageFooter />
      </div>
    </div>
  );
}
