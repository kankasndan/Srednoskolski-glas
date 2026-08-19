import Image from "next/image";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";

export default function ForumEmptyState() {
  return (
    <section className="flex w-[1100px] max-w-full flex-col items-center justify-center gap-6 rounded-3xl px-6 py-10">
      <div className="flex w-full flex-col items-center justify-center gap-6 text-center lg:w-[388px]">
        <Image
          src="/gray-logo.svg"
          alt=""
          width={115}
          height={77}
          priority
          className="object-contain"
        />
        <div className="flex flex-col items-center gap-4">
          <h1 className="font-[family-name:var(--font-oswald)] text-[18px] font-bold uppercase leading-[24px] text-black lg:text-[20px] lg:leading-[27px]">
            Сè уште нема дискусии
          </h1>
          <p className="w-full font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-[20px] text-[#595959] lg:w-[388px] lg:text-[16px] lg:leading-[22px]">
            Креирај ја првата дискусија - сподели тема за која вреди да се зборува.
          </p>
        </div>
      </div>
      <StartDiscussionButton full className="max-w-[268px]" />
    </section>
  );
}
