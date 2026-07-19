"use client";

export default function NewPageFooter() {
  return (
    <footer className="h-16 w-[268px] font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-none tracking-normal">
      <nav
        aria-label="Правни информации"
        className="flex h-4 w-[263px] items-center gap-3 text-[#595959]"
      >
        <span className="h-4 w-[121px]">Услови за користење</span>
        <span className="h-4 w-[69px]">Приватност</span>
        <span className="h-4 w-[49px]">Правила</span>
      </nav>
      <p className="mt-4 h-8 w-[268px] text-[#582FF5]">
        &copy; {new Date().getFullYear()} Средношколски Глас.
        <br />
        Сите права задржани.
      </p>
    </footer>
  );
}
