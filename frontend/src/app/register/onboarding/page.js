import AuthHero from "@/components/AuthHero";
import BackButton from "@/components/BackButton";
import OnboardingGuard from "@/components/OnboardingGuard";
import OnboardingMasthead from "@/components/OnboardingMasthead";
import OnboardingForm from "@/components/OnboardingForm";

export default function Onboarding() {
  return (
    <OnboardingGuard>
      <main className="flex min-h-screen w-full bg-white">
        <AuthHero />

        <div className="relative flex w-full flex-col px-6 pt-8 pb-0 lg:w-1/2 lg:px-16">
          <div className="relative z-10 self-start">
            <BackButton href="/feed" />
          </div>

          <div className="mx-auto -mt-10 flex w-full max-w-122 flex-1 flex-col justify-start pt-0 pb-0 2xl:max-w-[560px]">
            <OnboardingMasthead />
            <OnboardingForm />
          </div>
        </div>
      </main>
    </OnboardingGuard>
  );
}
