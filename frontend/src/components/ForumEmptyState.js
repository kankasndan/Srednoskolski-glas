import Image from "next/image";
import StartDiscussionButton from "@/components/StartDiscussionButton";

export default function ForumEmptyState() {
  return (
    <section className="flex h-[334px] w-[990px] max-w-full flex-col items-center justify-center gap-12 rounded-3xl bg-[#CFE9ED] py-8">
      <div className="flex h-[182px] w-[388px] max-w-full flex-col items-center justify-center gap-6 text-center">
        <Image
          src="/logo.svg"
          alt=""
          width={96}
          height={64}
          priority
          className="h-16 w-24 object-contain"
        />
        <div className="flex flex-col items-center gap-4">
          <h1 className="font-[family-name:var(--font-oswald)] text-[20px] font-bold uppercase leading-[27px] text-black">
            Се уште нема дискусии
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
