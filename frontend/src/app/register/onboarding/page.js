import AuthHero from "@/components/AuthHero";
import AuthBackButton from "@/components/AuthBackButton";
import OnboardingMasthead from "@/components/OnboardingMasthead";
import OnboardingForm from "@/components/OnboardingForm";
import OnboardingGuard from "@/components/OnboardingGuard";

export default function Onboarding() {
  return (
    <OnboardingGuard>
      <main className="flex min-h-screen w-full bg-white">
        <AuthHero />

        <div className="relative flex w-full flex-col px-6 py-3 lg:w-1/2 lg:px-16">
          <AuthBackButton href="/feed" />

          <div className="mx-auto flex w-full max-w-122 flex-1 flex-col justify-center py-2 2xl:max-w-[560px]">
            <OnboardingMasthead />
            <OnboardingForm />
          </div>
        </div>
      </main>
    </OnboardingGuard>
  );
}
