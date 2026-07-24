import Image from "next/image";
import Link from "next/link";

// Pill so glas/koment na kartichka za diskusija. Ako ima `href` e link (vodi kon
// diskusijata), inaku e obichno kopche.
export default function ThreadActionButton({ icon, label, count, href, onClick }) {
  const className =
    "flex h-12 w-24 cursor-pointer items-center justify-center gap-4 rounded-2xl border border-[#CCCCCC] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black opacity-80 transition-colors hover:bg-[#E5E5E5]";
  const inner = (
    <>
      <Image src={icon} alt="" width={24} height={24} className="size-6" />
      {count}
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
