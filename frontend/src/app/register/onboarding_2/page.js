import Image from "next/image";
import OnboardingGuard from "@/components/auth/OnboardingGuard";
import AvatarUploadCard from "@/components/auth/AvatarUploadCard";

export default function OnboardingAvatar() {
  return (
    <OnboardingGuard>
      <main className="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-white px-6 py-10">
        <div aria-hidden="true" className="pointer-events-none absolute inset-0">
          <Image
            src="/onboarding2.png"
            alt=""
            fill
            priority
            sizes="100vw"
            className="object-cover object-bottom opacity-30"
          />
        </div>

        <div className="relative z-10 flex w-full justify-center">
          <AvatarUploadCard />
        </div>
      </main>
    </OnboardingGuard>
  );
}
