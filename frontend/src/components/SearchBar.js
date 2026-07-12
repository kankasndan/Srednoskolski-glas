import Image from "next/image";

export default function SearchBar() {
  return (
    <div className="flex h-12 w-[632px] items-center gap-4 rounded-2xl border border-[#CCCCCC] px-4 py-2">
      <Image src="/search.svg" alt="" width={14} height={14} className="shrink-0" />
      <input
        type="text"
        placeholder="Пребарај дискусии..."
        className="w-full bg-transparent font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-[#595959] placeholder:text-[#595959] outline-none"
      />
    </div>
  );
}
