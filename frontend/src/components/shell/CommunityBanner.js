import Image from "next/image";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";

export default function CommunityBanner() {
  return (
    <section className="flex w-full flex-col items-center gap-4 rounded-xl bg-[#CFE9ED] px-3 py-6 lg:h-32 lg:w-[990px] lg:max-w-full lg:flex-row lg:gap-[92px] lg:rounded-3xl lg:p-6">
      <div className="flex flex-col items-center gap-2 lg:h-20 lg:w-[582px] lg:shrink-0 lg:flex-row lg:gap-4">
        <Image
          src="/logo.svg"
          alt=""
          width={119}
          height={80}
          className="hidden h-20 w-[119px] shrink-0 object-contain lg:block"
          priority
        />

        {/* Na desktop line-height-ot e od Figma (30px / 22px) za da dishaat naslovot i opisot. */}
        <div className="flex flex-col items-center gap-2 lg:w-[447px] lg:shrink-0 lg:items-start">
          <h2 className="max-w-[289px] text-center md:max-w-[520px] font-[family-name:var(--font-oswald)] text-[20px] font-bold leading-6 text-black lg:w-[447px] lg:max-w-none lg:text-left lg:leading-[30px]">
            МЕСТО КАДЕ СЕКОЈ СРЕДНОШКОЛЕЦ ИМА ГЛАС
          </h2>
          <p className="max-w-[290px] text-center md:max-w-[520px] font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-[17px] text-[#595959] lg:w-[447px] lg:max-w-none lg:whitespace-nowrap lg:text-left lg:text-[16px] lg:leading-[22px]">
            Прашувај, споделувај и откриј што мислат твоите врсници.
          </p>
        </div>
      </div>

      <StartDiscussionButton />
    </section>
  );
}
