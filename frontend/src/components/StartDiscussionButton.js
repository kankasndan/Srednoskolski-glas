import Image from "next/image";

export default function StartDiscussionButton({ className = "" }) {
  return (
    <button
      type="button"
      className={`flex h-10 w-[268px] shrink-0 items-center justify-center gap-3 rounded-xl bg-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0] ${className}`}
    >
      <Image src="/plus.svg" alt="" width={24} height={24} className="size-6" />
      <span className="flex h-[19px] items-center leading-none">
        Започни дискусија
      </span>
    </button>
  );
}
