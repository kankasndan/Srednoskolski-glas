import Image from "next/image";
import { MobileAuthMasthead } from "@/components/auth/AuthMasthead";

export default function OnboardingMasthead() {
  return (
    <div>
      <MobileAuthMasthead>
        ДОЗВОЛИ НИ
        <br />
        ДА ТЕ ЗАПОЗНАЕМЕ
      </MobileAuthMasthead>

      <div className="hidden items-center justify-center gap-4 lg:flex">
        <Image
          src="/logo.svg"
          alt=""
          width={138}
          height={93}
          priority
          className="shrink-0 object-contain"
        />
        <h1 className="font-(family-name:--font-oswald) text-left text-[30px] font-normal leading-[34px] tracking-[0%] text-[#000000]">
          ДОЗВОЛИ НИ ДА ТЕ ЗАПОЗНАЕМЕ
        </h1>
      </div>

      <p className="mx-auto mt-12 hidden max-w-[440px] text-center font-(family-name:--font-manrope) text-[20px] font-normal leading-[18.07px] tracking-[0%] text-[#000000] lg:block 2xl:max-w-[487px]">
        Овие информации помагаат да ја направиме платформата подобра за тебе.
      </p>
    </div>
  );
}
