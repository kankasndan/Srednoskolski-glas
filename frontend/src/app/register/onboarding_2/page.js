import OnboardingGuard from "@/components/auth/OnboardingGuard";
import AvatarUploadCard from "@/components/auth/AvatarUploadCard";
import AuthHero from "@/components/auth/AuthHero";
import OnboardingMasthead from "@/components/auth/OnboardingMasthead";

export default function OnboardingAvatar() {
  return (
    <OnboardingGuard>
      <main className="flex min-h-screen w-full bg-white">
        <AuthHero />

        <div className="relative flex w-full flex-col px-6 py-8 lg:w-1/2 lg:px-16">
          <div className="mx-auto flex w-full max-w-122 flex-1 flex-col justify-start 2xl:max-w-[560px]">
            <OnboardingMasthead />
            {/* <OnboardingForm /> */}
            <AvatarUploadCard />
          </div>
        </div>
      </main>
    </OnboardingGuard>
  );
}

/*
<OnboardingGuard>
  <main className="flex min-h-screen w-full items-center justify-center bg-white px-6">
    <AvatarUploadCard />
  </main>
</OnboardingGuard>
*/
