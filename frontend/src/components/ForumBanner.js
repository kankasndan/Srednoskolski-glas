import Image from "next/image";

export default function ForumBanner({ title, description, icon }) {
  return (
    <section className="flex h-32 w-[990px] max-w-full items-center justify-between gap-[122px] rounded-3xl bg-[#CFE9ED] py-6 pl-6 pr-[25px]">
      <div className="flex min-w-0 items-center gap-6">
        <span className="relative size-20 shrink-0 overflow-hidden">
          <Image
            src={icon}
            alt=""
            width={220}
            height={220}
            className="absolute left-1/2 top-1/2 size-[220px] max-w-none -translate-x-1/2 -translate-y-1/2"
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

      <button
        type="button"
        className="flex h-12 w-[268px] shrink-0 items-center justify-center gap-3 rounded-2xl bg-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0]"
      >
        <Image src="/plus.svg" alt="" width={24} height={24} className="size-6" />
        <span className="flex h-[19px] w-[168px] items-center leading-none">
          Започни нова дискусија
        </span>
      </button>
    </section>
  );
}
