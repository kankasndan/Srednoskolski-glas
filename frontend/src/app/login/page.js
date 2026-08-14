import Image from "next/image";
import AuthHero from "@/components/auth/AuthHero";
import BackButton from "@/components/shell/BackButton";
import AuthMasthead from "@/components/auth/AuthMasthead";
import SocialAuthButtons from "@/components/auth/SocialAuthButtons";

export default function LogIn() {
  return (
    <main className="h-stack relative min-h-dvh w-full overflow-hidden bg-white">
      <Image
        src="/login-hero.png"
        alt=""
        fill
        priority
        sizes="(max-width: 1023px) 100vw, 0px"
        aria-hidden="true"
        className="pointer-events-none absolute inset-0 z-0 object-cover object-center opacity-15 scale-125 sm:scale-115 md:scale-105 lg:hidden"
      />
      <AuthHero />

      <div className="v-stack relative z-10 w-full px-6 py-8 lg:min-h-dvh lg:w-1/2 lg:px-16">
        <BackButton href="/feed" label={null} />

        <div className="v-stack mx-auto w-full max-w-[342px] grow justify-center py-8 sm:max-w-[380px] md:max-w-[420px] lg:absolute lg:left-1/2 lg:top-[192px] lg:h-[628px] lg:w-[487px] lg:max-w-none lg:-translate-x-1/2 lg:justify-start lg:gap-[103px] lg:py-0">
          <AuthMasthead
            variant="loginMobile"
            titleLine="НАЈАВИ СЕ НА"
            subtitle="Најави се за повторно да се поврзеш со заедницата на средношколци во Македонија."
          />
          <SocialAuthButtons successRedirect="/feed" variant="loginMobile" className="lg:mt-0" />
        </div>
      </div>
    </main>
  );
}
