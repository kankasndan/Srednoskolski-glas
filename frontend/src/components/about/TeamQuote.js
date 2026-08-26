import Image from "next/image";

export default function TeamQuote({ className = "" }) {
  return (
    <figure
      className={`relative flex min-h-[480px] items-center justify-center overflow-hidden rounded-3xl bg-[var(--color-primary-200)] p-6 ${className}`}
    >
      <Image
        src="/about/background.png"
        alt=""
        fill
        aria-hidden="true"
        className="object-cover"
      />

      <div className="relative flex w-full max-w-[1046px] flex-col gap-16">
        <blockquote className="flex flex-col items-start font-(family-name:--font-oswald) font-bold text-white">
          <span aria-hidden="true" className="text-[96px] leading-[64px]">
            “
          </span>
          <p className="max-w-[809px] text-[24px] uppercase lg:text-[34.71px]">
            Не сакавме само да направиме платформа. Сакавме да создадеме место
            каде што гласот на средношколците навистина се слуша.
          </p>
        </blockquote>

        <figcaption className="flex items-center gap-4 text-[16px] text-white">
          <span className="h-[30px] w-px shrink-0 bg-white" />
          Тимот на Средношколски Глас
        </figcaption>
      </div>
    </figure>
  );
}
