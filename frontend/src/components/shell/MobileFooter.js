import Link from "next/link";

export const LEGAL_LINKS = [
  { href: "/privacy", label: "Приватност" },
  { href: "/terms", label: "Услови за користење" },
  { href: "/rules", label: "Правила" },
];

// Sekoja stranica ima drug gap vo kontejnerot, pa marginata se dopolnuva do ~44px vkupno.
export default function MobileFooter({ className = "mt-8", hideAtClassName = "lg:hidden" }) {
  return (
    <footer className={`flex flex-col items-center gap-3 pb-6 ${hideAtClassName} ${className}`}>
      <div className="flex items-center gap-2">
        {LEGAL_LINKS.map((link, index) => (
          <div key={link.href} className="flex items-center gap-2">
            {index > 0 ? <span className="size-[2px] rounded-full bg-[#595959]" /> : null}
            <Link
              href={link.href}
              className="font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-none text-[#595959] transition-colors active:text-black"
            >
              {link.label}
            </Link>
          </div>
        ))}
      </div>

      <p className="font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-none text-[#582FF5]">
        © 2026 Средношколски Глас. Сите права задржани.
      </p>
    </footer>
  );
}
