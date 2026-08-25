import OnboardingGuard from "@/components/auth/OnboardingGuard";
import BackButton from "@/components/shell/BackButton";
import AvatarUploadCard from "@/components/auth/AvatarUploadCard";

export default function OnboardingAvatar() {
  return (
    <OnboardingGuard>
      <main className="relative flex min-h-screen w-full items-start justify-center overflow-hidden bg-white px-6 pt-[290px] lg:items-center lg:py-10 lg:pt-10">
        <div
          aria-hidden="true"
          className="pointer-events-none absolute inset-0 bg-[url('/onboarding2.png')] bg-[length:1120px_auto] bg-[position:center_-112px] bg-no-repeat opacity-15 sm:bg-[length:1680px_auto] sm:bg-[position:center_-180px] lg:bg-[length:3600px_auto] lg:bg-[position:center_-4px] lg:opacity-30"
        />

        <div className="absolute top-8 left-6 z-20">
          <BackButton href="/register/onboarding" label={null} />
        </div>

        <div className="relative z-10 flex w-full justify-center">
          <AvatarUploadCard />
        </div>
      </main>
    </OnboardingGuard>
  );
}
