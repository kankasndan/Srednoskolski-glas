import Image from "next/image";
import Link from "next/link";

export default function CommunityBanner() {
  return (
    <section className="flex h-32 w-[990px] max-w-full items-center gap-[122px] rounded-3xl bg-[#CFE9ED] py-6 pl-6 pr-[22px]">
      <div className="flex h-20 w-[554px] shrink-0 items-center gap-[15px]">
        <Image
          src="/logo.svg"
          alt=""
          width={119}
          height={80}
          className="h-20 w-[119px] shrink-0 object-contain"
          priority
        />

        <div className="flex h-[60px] w-[420px] shrink-0 flex-col gap-2">
          <h2 className="h-[30px] w-[420px] font-[family-name:var(--font-oswald)] text-[20px] font-bold leading-none text-black">
            ПОВРЗИ СЕ СО ЗАЕДНИЦАТА НА СРЕДНОШКОЛЦИ
          </h2>
          <p className="h-[22px] w-[420px] font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-none text-[#595959]">
            Започни дискусија, придружи се на разговорите.
          </p>
        </div>
      </div>

      <Link
        href="/new"
        className="flex h-12 w-[268px] shrink-0 items-center justify-center gap-3 rounded-2xl bg-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0]"
      >
        <Image src="/plus.svg" alt="" width={24} height={24} className="size-6" />
        <span className="flex h-[19px] w-[168px] items-center leading-none">
          Започни нова дискусија
        </span>
      </Link>
    </section>
  );
}
