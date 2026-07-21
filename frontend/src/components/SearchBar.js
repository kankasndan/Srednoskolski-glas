import Image from "next/image";

export default function SearchBar() {
  return (
    <div className="flex h-10 w-[632px] items-center gap-4 rounded-xl border border-[#CCCCCC] px-4 py-2">
      <Image
        src="/search.svg"
        alt=""
        width={16}
        height={16}
        className="shrink-0"
      />
      <input
        type="text"
        placeholder="Пребарај дискусии..."
        className="w-full bg-transparent font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-[#595959] placeholder:text-[#595959] outline-none"
      />
    </div>
  );
}
