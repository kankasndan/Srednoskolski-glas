import Image from "next/image";
import Link from "next/link";
import { formatCount } from "@/lib/formatCount";

// Pill so glas/koment na kartichka za diskusija. Ako ima `href` e link (vodi kon
// diskusijata), inaku e obichno kopche. `compact` go smaluva pillot na telefon.
export default function ThreadActionButton({
  icon,
  label,
  count,
  href,
  onClick,
  compact = false,
}) {
  const sizeClassName = compact
    ? "h-8 w-18 gap-2 rounded-xl text-[12px] md:h-10 md:w-24 md:gap-4 md:rounded-2xl md:text-[14px]"
    : "h-10 w-24 gap-4 rounded-2xl text-[14px]";
  const iconClassName = compact ? "size-4 md:size-6" : "size-6";
  const className = `group flex cursor-pointer items-center justify-center border border-[#CCCCCC] font-[family-name:var(--font-manrope)] font-normal leading-none text-black opacity-80 transition-colors hover:border-[var(--color-primary-100)] hover:bg-[var(--color-primary-100)] hover:text-white hover:opacity-100 active:border-[var(--color-primary-100)] active:bg-[var(--color-primary-100)] active:text-white active:opacity-100 ${sizeClassName}`;
  const inner = (
    <>
      {/* Ikonite se ednobojni purpurni SVG-a, pa na purpurna podloga se gubat.
          brightness-0 gi pravi crni, invert potoa beli. */}
      <Image
        src={icon}
        alt=""
        width={24}
        height={24}
        className={`${iconClassName} transition group-hover:brightness-0 group-hover:invert group-active:brightness-0 group-active:invert`}
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
