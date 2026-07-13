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
          className="h-20 w-30 shrink-0 object-contain 2xl:h-24 2xl:w-36"
        />
        <h1 className="font-(family-name:--font-oswald) text-left text-[30px] font-normal leading-[34px] text-[#000000] 2xl:text-[38px] 2xl:leading-[42px]">
          ДОЗВОЛИ НИ ДА ТЕ
          <br />
          ЗАПОЗНАЕМЕ
        </h1>
      </div>

      <p className="mx-auto mt-3 max-w-[360px] text-center font-(family-name:--font-manrope) text-[14px] font-normal leading-[17px] text-[#000000] 2xl:max-w-[440px] 2xl:text-[16px] 2xl:leading-[20px]">
        Овие информации помагаат да ја направиме платформата подобра за тебе.
      </p>
    </div>
  );
}
