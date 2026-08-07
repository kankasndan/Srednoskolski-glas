import AppShell from "@/components/shell/AppShell";
import Link from "next/link";

export default function PublicProfileNotFound() {
  return (
    <AppShell>
      <div className="flex flex-col gap-4 font-(family-name:--font-manrope)">
        <h1 className="text-[20px] font-bold text-black">Профилот не е пронајден</h1>
        <p className="text-[16px] text-[#595959]">
          Овој корисник не постои или профилот не е достапен.
        </p>
        <Link href="/feed" className="text-[14px] font-bold text-[#582FF5] hover:underline">
          Назад кон почетна
        </Link>
      </div>
    </AppShell>
  );
}
