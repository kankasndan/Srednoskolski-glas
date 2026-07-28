"use client";

export default function NewPageFooter() {
  return (
    <footer className="font-[family-name:var(--font-manrope)] text-[12px]">
      <nav aria-label="Правни информации" className="flex items-center gap-3 text-[#595959]">
        <a href="#" className="transition-colors hover:text-black">
          Услови за користење
        </a>
        <a href="#" className="transition-colors hover:text-black">
          Приватност
        </a>
        <a href="#" className="transition-colors hover:text-black">
          Правила
        </a>
      </nav>
      <p className="mt-4 leading-5 text-[#582FF5]">
        &copy; {new Date().getFullYear()} Средношколски Глас.
        <br />
        Сите права задржани.
      </p>
    </footer>
  );
}
