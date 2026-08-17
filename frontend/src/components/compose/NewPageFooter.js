import Link from "next/link";
import { LEGAL_LINKS } from "@/lib/legalLinks";

const links = [LEGAL_LINKS.privacy, LEGAL_LINKS.terms, LEGAL_LINKS.rules];

export default function NewPageFooter() {
  return (
    <footer className="hidden px-14 py-6 font-[family-name:var(--font-manrope)] text-[12px] xl:block">
      <nav
        aria-label="Правни информации"
        className="flex flex-wrap items-center gap-x-4 gap-y-2 text-[#595959]"
      >
        {links.map((link) => (
          <Link key={link.href} href={link.href} className="cursor-pointer transition-colors hover:text-black">
            {link.label}
          </Link>
        ))}
      </nav>
      <p className="mb-8 mt-4 leading-4 text-[#582FF5]">
        &copy; {new Date().getFullYear()} Средношколски Глас.
        <br />
        Сите права задржани.
      </p>
    </footer>
  );
}
