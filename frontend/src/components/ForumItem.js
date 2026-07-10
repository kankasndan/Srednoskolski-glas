import Link from "next/link";

export default function ForumItem({ name, slug }) {
  return (
    <Link
      href={`/p/${slug}`}
      className="group flex h-12 w-[268px] items-center gap-3 rounded-2xl border border-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-medium text-[#595959] transition-colors hover:bg-[#582FF5] hover:text-white"
    >
      <img
        src={`/icons/${slug}.svg`}
        alt=""
        width={55}
        height={55}
        className="h-[55px] w-[55px] shrink-0 transition group-hover:brightness-0 group-hover:invert"
      />
      <span>{name}</span>
    </Link>
  );
}
