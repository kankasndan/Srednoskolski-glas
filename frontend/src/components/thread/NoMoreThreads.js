import Image from "next/image";

export default function NoMoreThreads() {
  return (
    <div className="flex h-[175px] w-full max-w-[340px] flex-col items-center justify-center gap-6 text-center min-[440px]:max-w-[388px]">
      <Image
        src="/gray-logo.svg"
        alt=""
        width={115}
        height={77}
        priority
        className="object-contain"
      />
      <div className="flex flex-col items-center gap-4">
        <h1 className="font-[family-name:var(--font-oswald)] text-[20px] font-bold uppercase leading-[27px] text-black min-[440px]:max-w-[388px]">
          Нема веќе дискусии
        </h1>
        <p className="w-full max-w-[340px] font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
          Ги прегледа сите дискусии што ги имаме за тебе засега.
        </p>
      </div>
    </div>
  );
}
