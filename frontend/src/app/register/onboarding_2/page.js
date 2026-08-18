import Image from "next/image";
import OnboardingGuard from "@/components/auth/OnboardingGuard";
import BackButton from "@/components/shell/BackButton";
import AvatarUploadCard from "@/components/auth/AvatarUploadCard";

export default function OnboardingAvatar() {
  return (
    <OnboardingGuard>
      <main className="relative flex min-h-screen w-full items-start justify-center overflow-hidden bg-white px-6 pt-[290px] lg:items-center lg:py-10 lg:pt-10">
        <div aria-hidden="true" className="pointer-events-none absolute inset-0">
          {/* scale-125 e barano dopolnitelno; vo dizajnot slikata ne e zumirana. */}
          <Image
            src="/onboarding2.png"
            alt=""
            fill
            priority
            sizes="100vw"
            className="scale-125 object-cover object-bottom opacity-30"
          />
        </div>

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
