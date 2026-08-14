import Image from "next/image";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";

export default function CommunityBanner() {
  return (
    <section className="flex w-full flex-col items-center gap-4 rounded-xl bg-[#CFE9ED] px-3 py-6 lg:h-32 lg:w-[990px] lg:max-w-full lg:flex-row lg:gap-[92px] lg:rounded-3xl lg:p-6">
      <div className="flex flex-col items-center gap-2 md:flex-row md:gap-4 lg:h-20 lg:w-[582px] lg:shrink-0">
        <Image
          src="/logo.svg"
          alt=""
          width={119}
          height={80}
          className="hidden h-20 w-[119px] shrink-0 object-contain md:block"
          priority
        />

        <div className="flex flex-col items-center gap-2 lg:w-[447px] lg:shrink-0 lg:items-start">
          <h2 className="max-w-72 text-center md:max-w-lg font-[family-name:var(--font-oswald)] text-[20px] font-bold leading-6 text-black lg:w-full lg:max-w-none lg:text-left lg:leading-normal">
            МЕСТО КАДЕ СЕКОЈ СРЕДНОШКОЛЕЦ ИМА ГЛАС
          </h2>
          <p className="max-w-72 text-center md:max-w-lg font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-tight text-[#595959] lg:w-full lg:max-w-none lg:whitespace-nowrap lg:text-left lg:text-[16px] lg:leading-snug">
            Прашувај, споделувај и откриј што мислат твоите врсници.
          </p>
        </div>
      </div>

      <StartDiscussionButton />
    </section>
  );
}
