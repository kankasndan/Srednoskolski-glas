import OnboardingGuard from "@/components/OnboardingGuard";
import AvatarUploadCard from "@/components/AvatarUploadCard";
import AuthHero from "@/components/AuthHero";
import OnboardingMasthead from "@/components/OnboardingMasthead";
import BackButton from "@/components/BackButton";

export default function OnboardingAvatar() {
  return (
    <OnboardingGuard>
      <main className="flex min-h-screen w-full bg-white">
        <AuthHero />

        <div className="relative flex w-full flex-col px-6 pt-8 pb-0 lg:w-1/2 lg:px-16">
          {/* <div className="relative z-10 self-start">
            <BackButton href="/feed" />
          </div> */}

          <div className="mx-auto flex w-full max-w-122 flex-1 flex-col justify-start pt-0 pb-0 2xl:max-w-[560px]">
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
