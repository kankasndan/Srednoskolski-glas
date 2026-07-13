import Image from "next/image";

export default function LoginMasthead() {
  return (
    <div>
      <div className="flex items-center gap-3">
        <Image
          src="/logo.svg"
          alt=""
          width={138}
          height={93}
          priority
          className="h-23 w-34.5 shrink-0 object-contain"
        />
        <h1 className="font-(family-name:--font-oswald) text-left text-[34.71px] font-normal leading-[39.05px]">
          <span className="text-[#0A0A0A]">РЕГИСТРИРАЈ СЕ НА</span>
          <br />
          <span className="bg-linear-to-r from-[#582FF5] to-[#9B6BFF] bg-clip-text text-transparent">
            СРЕДНОШКОЛСКИ ГЛАС
          </span>
        </h1>
      </div>

      <p className="mt-20 text-center font-(family-name:--font-manrope) text-[20px] font-normal leading-[22.59px] text-[#595959]">
        Приклучи се на заедницата на средношколци во Македонија — споделувај,
        прашувај, поврзувај се.
      </p>
    </div>
  );
}
