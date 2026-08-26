import Label from "@/components/ui/Label";

export default function AboutHero() {
  return (
    <div className="flex flex-col items-start gap-2 rounded-3xl border-b border-[var(--color-secondary-200)] pb-[50px]">
      <Label>За Нас</Label>
      <h1 className="max-w-[991px] font-(family-name:--font-oswald) text-[40px] font-bold leading-[48px] text-[var(--color-grays-900)] lg:text-[64px] lg:leading-[72px]">
        ЗАПОЗНАЈ ГО ТИМОТ КОЈ ГО СОЗДАДЕ{" "}
        <span className="text-[var(--color-primary-200)]">
          СРЕДНОШКОЛСКИ ГЛАС
        </span>
      </h1>
    </div>
  );
}
