import Link from "next/link";

export default function AuthButtons() {
  return (
    <div className="ml-auto flex shrink-0 items-center gap-4">
      <Link
        href="/login"
        className="flex h-12 w-36 items-center justify-center gap-4 rounded-2xl border border-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-medium text-[#0A0A0A] transition-colors hover:border-[#F88DD5] hover:bg-[#F88DD5] hover:text-white"
      >
        Најави се
      </Link>
      <Link
        href="/register"
        className="flex h-12 w-36 items-center justify-center gap-4 rounded-2xl border border-[#582FF5] bg-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-medium text-white transition-colors hover:border-[#F88DD5] hover:bg-[#F88DD5] hover:text-white"
      >
        Регистрација
      </Link>
    </div>
  );
}
