import Image from "next/image";
import AuthHero from "@/components/auth/AuthHero";
import OnboardingGuard from "@/components/auth/OnboardingGuard";
import BackButton from "@/components/shell/BackButton";
import OnboardingMasthead from "@/components/auth/OnboardingMasthead";
import OnboardingForm from "@/components/auth/OnboardingForm";

export default function Onboarding() {
  return (
    <OnboardingGuard>
      <main className="relative flex min-h-dvh w-full overflow-x-hidden bg-white lg:min-h-screen">
        <Image
          src="/login-hero.png"
          alt=""
          fill
          priority
          sizes="(max-width: 1023px) 100vw, 0px"
          aria-hidden="true"
          className="pointer-events-none absolute inset-0 z-0 scale-125 object-cover object-center opacity-15 sm:scale-115 md:scale-105 lg:hidden"
        />
        <AuthHero />

        <div className="v-stack relative z-10 w-full px-6 py-8 lg:min-h-screen lg:w-1/2 lg:px-16">
          <div className="lg:hidden">
            <BackButton href="/register" label={null} tone="muted" />
          </div>

          <div className="v-stack mx-auto w-full max-w-[342px] grow justify-center py-8 sm:max-w-[380px] md:max-w-[420px] lg:max-w-122 lg:justify-start lg:py-0 2xl:max-w-[560px]">
            <OnboardingMasthead />
            <OnboardingForm />
          </div>
        </div>
      </main>
    </OnboardingGuard>
  );
}
