import Image from "next/image";
import Link from "next/link";
import { formatCount } from "@/lib/formatCount";

// Pill so glas/koment na kartichka za diskusija. Ako ima `href` e link (vodi kon
// diskusijata), inaku e obichno kopche.
export default function ThreadActionButton({ icon, label, count, href, onClick }) {
  const className =
    "group flex h-10 w-24 cursor-pointer items-center justify-center gap-4 rounded-2xl border border-[#CCCCCC] font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black opacity-80 transition-colors hover:border-[var(--color-primary-100)] hover:bg-[var(--color-primary-100)] hover:text-white hover:opacity-100";
  const inner = (
    <>
      {/* Ikonite se ednobojni purpurni SVG-a, pa na purpurna podloga se gubat.
          brightness-0 gi pravi crni, invert potoa beli. */}
      <Image
        src={icon}
        alt=""
        width={24}
        height={24}
        className="size-6 transition group-hover:brightness-0 group-hover:invert"
      />
      {formatCount(count)}
    </>
  );

  if (href) {
    return (
      <Link href={href} aria-label={label} className={className}>
        {inner}
      </Link>
    );
  }

  return (
    <button type="button" aria-label={label} onClick={onClick} className={className}>
      {inner}
    </button>
  );
}
