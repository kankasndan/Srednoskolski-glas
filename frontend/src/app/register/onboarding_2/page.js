import OnboardingGuard from "@/components/OnboardingGuard";
import AvatarUploadCard from "@/components/AvatarUploadCard";

export default function OnboardingAvatar() {
  return (
    <OnboardingGuard>
      <main className="flex min-h-screen w-full items-center justify-center bg-white px-6">
        <AvatarUploadCard />
      </main>
    </OnboardingGuard>
  );
}
