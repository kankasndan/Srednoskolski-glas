import AuthHero from "@/components/AuthHero";
import AuthBackButton from "@/components/AuthBackButton";
import AuthMasthead from "@/components/AuthMasthead";
import SocialAuthButtons from "@/components/SocialAuthButtons";

export default function Register() {
  return (
    <main className="flex min-h-screen w-full bg-white">
      <AuthHero />

      <div className="relative flex w-full flex-col px-6 py-8 lg:w-1/2 lg:px-16">
        <AuthBackButton />

        <div className="mx-auto flex w-full max-w-122 flex-1 flex-col justify-center py-8 2xl:max-w-[560px]">
          <AuthMasthead
            titleLine="РЕГИСТРИРАЈ СЕ НА"
            subtitle="Приклучи се на заедницата на средношколци во Македонија — споделувај, прашувај, поврзувај се."
          />
          <SocialAuthButtons successRedirect="/register/onboarding" />
        </div>
      </div>
    </main>
  );
}
