import AppShell from "@/components/shell/AppShell";
import BackButton from "@/components/shell/BackButton";

export default function LegalPage({ title, lastUpdated, children }) {
  return (
    <AppShell>
      <div className="flex flex-col gap-[clamp(2.5rem,6.4vw,3rem)] font-(family-name:--font-manrope) leading-[normal]">
        <div className="self-start">
          <BackButton label="Назад" tone="muted" />
        </div>

        <div className="flex flex-col gap-[clamp(2.5rem,10vw,5rem)]">
          <header className="flex flex-col gap-2">
            <h1 className="text-[clamp(20px,5.1vw,24px)] font-bold text-[var(--color-primary-200)]">
              {title}
            </h1>
            <p className="text-[12px] text-[var(--color-grays-700)]">
              Последно ажурирање: {lastUpdated}
            </p>
          </header>

          <div className="flex flex-col gap-[clamp(2.5rem,10vw,5rem)] pb-10">{children}</div>
        </div>
      </div>
    </AppShell>
  );
}
