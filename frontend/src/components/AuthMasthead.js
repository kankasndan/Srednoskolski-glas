import Image from "next/image";

// Shared masthead for the login and register pages: logo + two-line title
// (top line varies, second line is always the gradient "СРЕДНОШКОЛСКИ ГЛАС")
// and a subtitle. Only titleLine and subtitle differ between the two pages.
export default function AuthMasthead({ titleLine, subtitle }) {
  return (
    <div>
      <div className="flex items-center justify-center gap-4">
        <Image
          src="/logo.svg"
          alt=""
          width={138}
          height={93}
          priority
          className="h-16 w-24 shrink-0 object-contain 2xl:h-20 2xl:w-28"
        />
        <h1 className="font-(family-name:--font-oswald) text-left text-[26px] font-normal leading-[29px] text-[#0A0A0A] 2xl:text-[32px] 2xl:leading-[36px]">
          {titleLine}
          <br />
          <span className="bg-linear-to-r from-[#582FF5] to-[#9B6BFF] bg-clip-text text-transparent">
            СРЕДНОШКОЛСКИ ГЛАС
          </span>
        </h1>
      </div>

      <p className="mx-auto mt-12 max-w-[360px] text-center font-(family-name:--font-manrope) text-[15px] font-normal leading-[18px] text-[#000000] 2xl:max-w-[440px] 2xl:text-[17px] 2xl:leading-[21px]">
        {subtitle}
      </p>
    </div>
  );
}
