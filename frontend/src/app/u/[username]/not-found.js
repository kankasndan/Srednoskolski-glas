import AppShell from "@/components/shell/AppShell";
import BackButton from "@/components/shell/BackButton";

export default function PublicProfileNotFound() {
  return (
    <AppShell>
      <div className="flex w-[990px] max-w-full flex-col gap-4 font-(family-name:--font-manrope)">
        <BackButton href="/feed" tone="muted" />
        <h1 className="text-[20px] font-bold text-black">Профилот не е пронајден</h1>
        <p className="text-[16px] text-[#595959]">
          Овој корисник не постои или профилот не е достапен.
        </p>
      </div>
    </AppShell>
  );
}
