import Image from "next/image";
import StartDiscussionButton from "@/components/StartDiscussionButton";

export default function ForumBanner({ title, description, icon }) {
  return (
    <section className="flex h-32 w-[990px] max-w-full items-center justify-between gap-[122px] rounded-3xl bg-[#CFE9ED] py-6 pl-6 pr-[25px]">
      <div className="flex min-w-0 items-center gap-6">
        <span className="relative size-20 shrink-0 overflow-hidden">
          <Image
            src={icon}
            alt=""
            width={205}
            height={205}
            className="absolute left-[calc(50%+4px)] top-1/2 size-[205px] max-w-none -translate-x-1/2 -translate-y-1/2"
            priority
          />
        </span>

        <div className="flex min-w-0 flex-col gap-2">
          <h1 className="font-[family-name:var(--font-oswald)] text-[20px] font-bold uppercase leading-[27px] text-black">
            {title}
          </h1>
          <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
            {description}
          </p>
        </div>
      </div>

      <StartDiscussionButton />
    </section>
  );
}
