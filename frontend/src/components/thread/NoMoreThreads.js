import Image from "next/image";

export default function NoMoreThreads() {
  return (
    <div role="status" className="flex w-full flex-col items-center gap-4 pt-2">
      <div className="h-px w-full bg-[#CFE9ED]" aria-hidden="true" />
      <div className="flex w-full max-w-[420px] flex-col items-center gap-3 rounded-3xl bg-[#DCEBED] px-8 py-6 text-center">
        <Image
          src="/logo.svg"
          alt=""
          width={72}
          height={48}
          className="h-12 w-[72px] object-contain"
        />
        <div className="flex flex-col items-center gap-2">
          <p className="font-[family-name:var(--font-oswald)] text-[18px] font-bold uppercase leading-none text-black">
            Нема веќе
          </p>
          <p className="font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-5 text-[#595959]">
            Ги виде сите дискусии засега.
          </p>
        </div>
      </div>
    </div>
  );
}
