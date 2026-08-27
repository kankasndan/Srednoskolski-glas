import Image from "next/image";

export default function TeamQuote({ className = "" }) {
  return (
    <figure
      className={`relative -mx-6 flex h-[480px] w-screen items-center justify-center overflow-hidden bg-[var(--color-primary-200)] py-6 pr-6 pl-12 lg:mx-0 lg:min-h-[480px] lg:w-auto lg:rounded-3xl ${className}`}
    >
      <Image
        src="/about/quote-megaphone.png"
        alt=""
        width={692}
        height={552}
        aria-hidden="true"
        className="pointer-events-none absolute -right-[150px] bottom-[-120px] h-auto w-[500px] opacity-15 lg:-right-[285px] lg:bottom-[-305px] lg:w-[860px]"
      />

      <div className="relative flex w-full max-w-[1046px] flex-col gap-[65px] lg:gap-16">
        <blockquote className="flex flex-col items-start font-(family-name:--font-oswald) font-bold text-white">
          <span aria-hidden="true" className="text-[96px] leading-[64px]">
            “
          </span>
          <p className="h-[216px] w-[min(270px,100%)] text-[24px] leading-[1.5] uppercase min-[450px]:w-full md:w-[min(809px,100%)] md:text-[clamp(24px,3.39vw,34.71px)] lg:h-auto lg:max-w-[809px] lg:leading-[normal]">
            Не сакавме само да направиме платформа. Сакавме да создадеме место
            каде што гласот на средношколците навистина се слуша.
          </p>
        </blockquote>

        <figcaption className="flex h-[30px] w-[220px] items-center gap-3 text-center text-[14px] leading-none text-white lg:h-auto lg:w-auto lg:gap-4 lg:text-[16px] lg:leading-[normal]">
          <span className="h-[30px] w-px shrink-0 bg-white" />
          <span className="h-[19px] w-[204px] whitespace-nowrap lg:h-auto lg:w-auto">
            Тимот на Средношколски Глас
          </span>
        </figcaption>
      </div>
    </figure>
  );
}
