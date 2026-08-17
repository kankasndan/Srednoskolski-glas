import Link from "next/link";
import { LEGAL_LINKS } from "@/lib/legalLinks";

const links = [LEGAL_LINKS.terms, LEGAL_LINKS.privacy, LEGAL_LINKS.rules];

// Sedi na dnoto na stranichnata lenta; koga listata e podolga odi po nea.
export default function SidebarFooter() {
  return (
    <footer className="mt-auto flex flex-col gap-4 pt-8">
      <div className="flex items-center gap-3">
        {links.map((link) => (
          <Link
            key={link.href}
            href={link.href}
            className="font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-none text-[var(--color-grays-700)] transition-colors hover:text-black"
          >
            {link.label}
          </Link>
        ))}
      </div>

      <p className="font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-4 text-[var(--color-primary-200)]">
        © 2026 Средношколски Глас.
        <br />
        Сите права задржани.
      </p>
    </footer>
  );
}
