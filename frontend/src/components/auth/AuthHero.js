import Image from "next/image";

export default function AuthHero() {
  return (
    <div
      aria-hidden="true"
      className="relative hidden w-1/2 shrink-0 overflow-hidden bg-white lg:block"
    >
      <Image
        src="/login-hero.png"
        alt=""
        width={712}
        height={1024}
        priority
        className="absolute left-0 top-[-72px] h-[1024px] w-[712px] max-w-none"
      />
    </div>
  );
}
