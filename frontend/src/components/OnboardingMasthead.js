import Image from "next/image";

export default function OnboardingMasthead() {
  return (
    <div>
      <div className="flex items-center justify-center gap-4">
        <Image
          src="/logo.svg"
          alt=""
          width={138}
          height={93}
          priority
          className="h-[89px] w-[132px] shrink-0 object-contain"
        />
        <h1 className="font-(family-name:--font-oswald) text-left text-[30px] font-normal leading-[34px] tracking-[0%] text-[#000000]">
          ДОЗВОЛИ НИ ДА ТЕ ЗАПОЗНАЕМЕ
        </h1>
      </div>

      <p className="mx-auto mt-12 max-w-[360px] text-center font-(family-name:--font-manrope) text-[16px] font-normal leading-[18.07px] tracking-[0%] text-[#000000] 2xl:max-w-[440px]">
        Овие информации помагаат да ја направиме платформата подобра за тебе.
      </p>
    </div>
  );
}
