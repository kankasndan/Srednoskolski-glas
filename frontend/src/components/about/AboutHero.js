import Label from "@/components/ui/Label";

export default function AboutHero() {
  return (
    <div className="flex flex-col items-start gap-2 rounded-3xl border-b border-[var(--color-secondary-200)] pb-7 lg:pb-[50px]">
      <Label>За Нас</Label>
      <h1 className="max-w-[991px] font-(family-name:--font-oswald) text-[clamp(40px,6.25vw,64px)] font-bold leading-[clamp(46px,7.03vw,72px)] tracking-normal text-[var(--color-grays-900)]">
        ЗАПОЗНАЈ ГО ТИМОТ КОЈ ГО СОЗДАДЕ{" "}
        <span className="hidden text-[var(--color-primary-200)] min-[580px]:inline">
          СРЕДНОШКОЛСКИ ГЛАС
        </span>
        <span className="text-[var(--color-primary-200)] min-[580px]:hidden">
          СРЕДНО -<br />
          ШКОЛСКИ ГЛАС
        </span>
      </h1>
    </div>
  );
}
