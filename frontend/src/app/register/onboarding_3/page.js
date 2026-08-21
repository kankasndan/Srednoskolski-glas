import OnboardingGuard from "@/components/auth/OnboardingGuard";
import BackButton from "@/components/shell/BackButton";
import AvatarReadyCard from "@/components/auth/AvatarReadyCard";

export default function OnboardingAvatarReady() {
  return (
    <OnboardingGuard>
      <main className="relative flex min-h-screen w-full overflow-x-hidden bg-white lg:h-dvh lg:min-h-0">
        <div
          aria-hidden="true"
          className="pointer-events-none absolute inset-y-0 right-0 hidden w-[38vw] bg-[url('/onboarding2.png')] bg-[length:2400px_auto] bg-[position:left_-460px_top_-430px] bg-no-repeat opacity-30 lg:block"
        />

        <div className="absolute left-6 top-16 z-20 lg:left-10 lg:top-10">
          <BackButton href="/register/onboarding_2" label={null} />
        </div>

        <section className="relative z-10 flex w-full flex-col items-center px-6 pb-10 pt-[196px] sm:pt-[160px] lg:h-dvh lg:w-1/2 lg:items-center lg:justify-end lg:overflow-y-auto lg:py-10 lg:pl-10 lg:pr-2 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
          <AvatarReadyCard />
          <div
            aria-hidden="true"
            className="mt-6 h-48 w-full max-w-[342px] bg-[url('/onboarding2.png')] bg-[length:4400px_auto] bg-[position:left_-384px_top_0px] bg-no-repeat opacity-30 sm:h-60 sm:max-w-[533px] sm:bg-[length:5000px_auto] sm:bg-[position:left_-380px_top_0px] lg:hidden"
          />
        </section>
      </main>
    </OnboardingGuard>
  );
}
