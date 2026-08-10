import Image from "next/image";
import StartDiscussionButton from "@/components/forum/StartDiscussionButton";

export default function ForumEmptyState() {
  return (
    <section className="flex h-[334px] w-[990px] max-w-full flex-col items-center justify-center gap-12 rounded-3xl py-8">
      <div className="flex h-[175px] w-[388px] max-w-full flex-col items-center justify-center gap-6 text-center">
        <Image
          src="/gray-logo.svg"
          alt=""
          width={115}
          height={77}
          priority
          className="object-contain"
        />
        <div className="flex flex-col items-center gap-4">
          <h1 className="font-[family-name:var(--font-oswald)] text-[20px] font-bold uppercase leading-[27px] text-black">
            Сè уште нема дискусии
          </h1>
          <p className="w-[388px] max-w-full font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
            Креирај ја првата дискусија - сподели тема за која вреди да се зборува.
          </p>
        </div>
      </div>
      <StartDiscussionButton />
    </section>
  );
}
