import Image from "next/image";

// Shared masthead for the login and register pages: logo + two-line title
// (top line varies, second line is always the gradient "СРЕДНОШКОЛСКИ ГЛАС")
// and a subtitle. Only titleLine and subtitle differ between the two pages.
export default function AuthMasthead({ titleLine, subtitle, variant = "default" }) {
  if (variant === "loginMobile") {
    return (
      <>
        <MobileAuthMasthead subtitle={subtitle}>
          {titleLine}
          <br />
          <span className="text-[#582FF5]">СРЕДНОШКОЛСКИ ГЛАС</span>
        </MobileAuthMasthead>

        <div className="hidden lg:block">
          <DefaultAuthMasthead
            titleLine={titleLine}
            subtitle={subtitle}
            subtitleClassName="h-[55px] w-[487px] max-w-none text-[20px] leading-[22.59px] text-[#000000] 2xl:max-w-none"
          />
        </div>
      </>
    );
  }

  if (variant === "register") {
    return (
      <>
        <MobileAuthMasthead subtitle={subtitle}>
          {titleLine}
          <br />
          <span className="text-[#582FF5]">СРЕДНОШКОЛСКИ ГЛАС</span>
        </MobileAuthMasthead>

        <div className="hidden lg:block">
          <DefaultAuthMasthead
            titleLine={titleLine}
            subtitle={subtitle}
            subtitleClassName="h-[55px] w-[487px] max-w-none text-[20px] leading-[22.59px] text-[#000000] 2xl:max-w-none"
          />
        </div>
      </>
    );
  }

  return <DefaultAuthMasthead titleLine={titleLine} subtitle={subtitle} />;
}

export function MobileAuthMasthead({ children, subtitle }) {
  return (
    <div className="v-stack w-full items-center gap-9 lg:hidden">
      <div className="v-stack h-[120px] w-[219px] items-center justify-center gap-6">
        <Image
          src="/logo.svg"
          alt=""
          width={71}
          height={48}
          priority
          className="h-12 w-[71px] object-contain"
        />
        <h1 className="font-(family-name:--font-oswald) text-center text-xl leading-6 font-bold text-[#0A0A0A]">
          {children}
        </h1>
      </div>

      {subtitle && (
        <p className="w-full text-center font-(family-name:--font-manrope) text-sm leading-none font-normal text-[#595959]">
          {subtitle}
        </p>
      )}
    </div>
  );
}

function DefaultAuthMasthead({ titleLine, subtitle, subtitleClassName = "" }) {
  const subtitleClasses =
    "mx-auto mt-12 max-w-[360px] text-center font-(family-name:--font-manrope) text-[16px] font-normal leading-[18.07px] tracking-[0%] text-[#000000] 2xl:max-w-[440px]";

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
        <h1 className="font-(family-name:--font-oswald) text-left text-[30px] font-normal leading-[34px] tracking-[0%] text-[#0A0A0A]">
          {titleLine}
          <br />
          <span className="bg-linear-to-r from-[#582FF5] to-[#9B6BFF] bg-clip-text text-transparent">
            СРЕДНОШКОЛСКИ ГЛАС
          </span>
        </h1>
      </div>

      <p className={`${subtitleClasses} ${subtitleClassName}`}>
        {subtitle}
      </p>
    </div>
  );
}
