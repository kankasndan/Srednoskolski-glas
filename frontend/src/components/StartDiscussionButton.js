import Image from "next/image";
import Link from "next/link";

export default function StartDiscussionButton({ className = "", href = "/feed/create" }) {
  return (
    <Link
      href={href}
      prefetch={false}
      className={`relative z-10 flex h-10 w-[268px] shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl bg-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-white no-underline transition-colors hover:bg-[#4B25E0] ${className}`}
    >
      <Image src="/plus.svg" alt="" width={24} height={24} className="pointer-events-none size-6" />
      <span className="pointer-events-none flex h-[19px] items-center leading-none">
        Започни дискусија
      </span>
    </Link>
  );
}
