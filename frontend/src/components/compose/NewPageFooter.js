export default function NewPageFooter() {
  return (
    <footer className="border-t border-[#E5E5E5] px-14 pb-10 pt-8 font-[family-name:var(--font-manrope)] text-[12px]">
      <nav aria-label="Правни информации" className="flex flex-wrap items-center gap-x-4 gap-y-2 text-[#595959]">
        <a className="cursor-pointer transition-colors hover:text-black">
          Услови за користење
        </a>
        <a className="cursor-pointer transition-colors hover:text-black">
          Приватност
        </a>
        <a className="cursor-pointer transition-colors hover:text-black">
          Правила
        </a>
      </nav>
      <p className="mt-3 leading-5 text-[#582FF5]">
        &copy; {new Date().getFullYear()} Средношколски Глас. Сите права задржани.
      </p>
    </footer>
  );
}
