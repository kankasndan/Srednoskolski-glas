import Image from "next/image";
import { formatCount } from "@/lib/formatCount";

// className ja menuva sirinata koga brojacot stoi vo red so kopcinjata.
export default function ThreadViewCount({ views, className = "w-24" }) {
  return (
    <div
      className={`flex h-4 items-center gap-1 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-4 text-[var(--color-primary-200)] ${className}`}
    >
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
