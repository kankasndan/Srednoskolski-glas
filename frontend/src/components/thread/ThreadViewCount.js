import Image from "next/image";
import { formatCount } from "@/lib/formatCount";

export default function ThreadViewCount({ views }) {
  return (
    <div className="flex h-4 w-24 items-center gap-1 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-4 text-[var(--color-primary-200)]">
      <Image
        src="/eye-line.svg"
        alt=""
        width={16}
        height={16}
        className="size-4 shrink-0"
      />
      <span>{formatCount(views ?? 0)}</span>
    </div>
  );
}
